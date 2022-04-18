<?php
/**
 * EVoucherWP WooCommerce Voucher PDF Creator
 *
 * Class for generator vouchers from WooCommerce completed orders
 *
 * @author      Jose A. Salim
 * @package     EVoucherWP_WooCommerce/Classes
 * @version     1.0.2
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVWP_Voucher_PDF_Creator Class
 */
class EVWP_Voucher_PDF_Creator {

	/**
	 * Template name
	 *
	 * @var string
	 */
	private static $template_html = 'evoucher-pdf.php';

	/**
	 * Constructor
	 *
	 * @param  WC_Order $order    order
	 * @param  array    $vouchers vouchers
	 * @param  boolean  $download download
	 * @return boolean|string   download url if downlod is true, or file path, or false on failure
	 */
	public static function create_pdf( $order, $vouchers, $download = false ) {
		if ( empty( $vouchers ) ) {
			return false;
		}

		$dompdf_path = get_option( 'evoucherwp_wc_dompdf_path', EVWP_WC_ABSPATH . 'external/dompdf' );
		if ( ! file_exists( $dompdf_path ) ) {
			add_action( 'admin_notices', 'EVWP_Voucher_PDF_Creator::admin_notice__error' );
			return false;
		}

		require_once trailingslashit( $dompdf_path ) . 'autoload.inc.php';

		$html = evwp_wc_get_template_html(
			self::get_template(),
			[
				'order'    => $order,
				'vouchers' => $vouchers,
			]
		);

		$options = new Dompdf\Options();
		$options->set( 'isRemoteEnabled', true );
		$dompdf = new Dompdf\Dompdf( $options );

		$context = stream_context_create(
			[
				'ssl' => [
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				],
			]
		);
		$dompdf->setHttpContext( $context );

		$dompdf->loadHtml( $html );
		$dompdf->setPaper( 'A4', 'portrait' );

		$dompdf->render();

		$upload_dir = wp_upload_dir();
		$filename   = sprintf( 'voucher_%d.pdf', $order->get_id() );

		$vouchers_dir = trailingslashit( $upload_dir['basedir'] ) . 'evwp_vouchers';
		if ( ! file_exists( $vouchers_dir ) ) {
			wp_mkdir_p( $vouchers_dir );
		}

		$path    = trailingslashit( $vouchers_dir ) . $filename;
		$pdf_gen = $dompdf->output();

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		if ( ! $wp_filesystem->put_contents( $path, $pdf_gen, 0644 ) ) {
			return __( 'Failed to create PDF voucher', 'evoucherwp-woocommerce' );
		}

		return ! $download ? $path : trailingslashit( $upload_dir['baseurl'] . '/evwp_vouchers' ) . $filename;
	}

	/**
	 * Get Voucher PDF template
	 *
	 * @return string template name
	 */
	public static function get_template() {
		return apply_filters( 'evwp_wc_voucher_pdf_template', self::$template_html );
	}

	/**
	 * Display admin notice errors
	 */
	public static function admin_notice__error() {
		$class   = 'notice notice-error';
		$message = __( 'DomPDF path does not exists! PDF vouchers creation will not work unless you provide a valid path.', 'evoucherwp-woocommerce' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * Delete PDF files
	 *
	 * @param array pdf file name's
	 */
	public static function delete_pdfs( $pdfs ) {
		$upload_dir   = wp_upload_dir();
		$vouchers_dir = trailingslashit( $upload_dir['basedir'] ) . 'evwp_vouchers';
		foreach ( $pdfs as $pdf ) {
			if ( file_exists( $pdf ) && is_file( $pdf ) && dirname( $pdf ) === $vouchers_dir ) {
				unlink( $pdf );
			}
		}
	}
}
