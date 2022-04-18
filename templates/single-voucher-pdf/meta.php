<?php
/**
 * Single Voucher Meta PDF
 *
 * This template can be overridden by copying it to yourtheme/evoucherwp/single-voucher/content.php.
 *
 * @author     Jose A. Salim
 * @package    EVoucherWP_WooCommerce/Templates/Single_Voucher_PDF
 * @version    1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! empty( $vouchers ) ) :
	foreach ( $vouchers as $voucher ) :
		$product = $voucher->get_product();
		?>
		<div class="voucher">
			<h2 itemprop="name" class="voucher-title entry-title"><?php echo esc_html( $product->get_title() ); ?></h2>
			<div class="summary entry-summary">
				<table>
					<tr>
						<td class="voucher-meta">
							<?php do_action( 'evwp_wc_voucher_meta', $voucher ); ?>
						</td>
						<td class="qrcode">
							<?php do_action( 'evwp_wc_voucher_qrcode', $voucher ); ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?php endforeach;
endif;

?>
