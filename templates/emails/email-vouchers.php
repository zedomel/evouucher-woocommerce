<?php
/**
 * Email Order Items
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-items.php.
 *
 * @package EVoucherWP_WooCommerce/Templates/Emails/
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$text_align = is_rtl() ? 'right' : 'left';

foreach ( $vouchers as $voucher ) :
	$product = $voucher->get_product();

	if ( ! apply_filters( 'evwp_wc_voucher_visible', true, $voucher ) ) {
		continue;
	}

	$item = $voucher->get_order_item();

	?>
	<tr>
		<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
			<a style="color: #600724;" href="<?php echo esc_url( $voucher->get_download_url() ); ?>">
				<div style="text-align: center;">
					<h2 style="text-align: center;"><?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) ); ?></h2>

					<?php echo wp_kses_post( evwp_wc_single_voucher( $voucher ) ); ?>
				</div>
			</a>
		</td>
	</tr>

	<?php
	do_action( 'evoucherwp_voucher_available_message', $voucher );
	endforeach;
?>
