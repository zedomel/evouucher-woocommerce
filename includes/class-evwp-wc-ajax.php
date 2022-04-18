<?php
/**
 * EVoucherWP EVWP_WC_AJAX.
 *
 * AJAX Event Handler.
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-ajax.php
 *
 * @class    EVWP_WC_AJAX
 * @version  1.0.0
 * @package     EvoucherWP_WooCommerce/Classes
 * @category Class
 * @author   Jose A. Salim
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'EVWP_WC_AJAX' ) ) {

	/**
	 * EVWP_WC_AJAX Class
	 */
	class EVWP_WC_AJAX {

		/**
		 * Select order id in voucher options
		 *  TODO: nonce
		 */
		public static function select_order_id() {

			if ( isset( $_POST['order_id'] ) && ! empty( $_POST['order_id'] ) ) {
				$order_id = intval( $_POST['order_id'] );
				$order    = wc_get_order( $order_id );
				if ( ! empty( $order ) ) {
					$products = array();
					foreach ( $order->get_items() as $item_id => $item ) {
						$products[] = array(
							'id'   => $item_id,
							'name' => $item->get_name(),
						);
					}
					$data = array(
						'valid'    => true,
						'products' => $products,
					);
				} else {
					$data = array(
						'valid'   => false,
						'message' => "Can't find Order with ID: " . $order_id,
					);
				}

				wp_send_json( $data );
			}
			die();
		}

		/**
		 * Resend voucher by email
		 */
		public static function resend_voucher() {
			check_ajax_referer( '_evoucherwp_wc_admin_wpnonce', 'security' );

			if ( isset( $_GET['voucher_id'] ) ) {
				$voucher_id = absint( $_GET['voucher_id'] );
				$voucher    = new EVWP_WC_Voucher( $voucher_id );
				if ( ! empty( $voucher ) ) {
					$mailer = WC()->mailer();
					if ( isset( $mailer->emails['EVWP_WC_Voucher_Mail'] ) ) {
						$mail = $mailer->emails['EVWP_WC_Voucher_Mail'];
						$mail->trigger( $voucher->get_order_id(), $voucher->get_order(), array( $voucher ) );
						wp_send_json_success(
							array(
								'message' => __( 'Voucher was sent!', 'evoucherwp-woocommerce' ),
							)
						);
					}
				}
			}
			wp_send_json_error();
		}

		/**
		 * Create voucher PDF
		 */
		public static function create_voucher_pdf() {
			check_ajax_referer( '_evoucherwp_wc_admin_wpnonce', 'security' );

			if ( isset( $_GET['voucher_id'] ) ) {
				$voucher_id = absint( $_GET['voucher_id'] );
				$voucher    = new EVWP_WC_Voucher( $voucher_id );
				if ( ! empty( $voucher ) ) {
					$file_url = EVWP_Voucher_PDF_Creator::create_pdf( $voucher->get_order(), array( $voucher ), true );
					wp_send_json_success(
						array(
							'file_url' => esc_url( $file_url ),
						)
					);
				}
			}

			wp_send_json_error(
				array(
					'message' => __( 'Error creating PDF for this voucher', 'evoucherwp-woocommerce' ),
				)
			);
		}

		/**
		 * Active/deactive voucher
		 */
		public function set_live() {
			check_ajax_referer( '_evoucherwp_voucher_wpnonce', 'security' );
			if ( isset( $_POST['voucher_id'] ) ) {
				$voucher_id = absint( $_POST['voucher_id'] );
				$live       = isset( $_POST['voucher_live'] ) ? sanitize_text_field( $_POST['voucher_live'] ) : 'no';
				update_post_meta( $voucher_id, '_evoucherwp_live', 'yes' === $live ? 'yes' : 'no' );
				wp_send_json_success();
			}
			wp_send_json_error();
		}
	}
}
