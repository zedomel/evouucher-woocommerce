<?php
/**
 * Single Voucher footer
 *
 * This template can be overridden by copying it to yourtheme/evoucherwp/single-voucher/footer.php.
 *
 * @author     Jose A. Salim
 * @package    EVoucherWP_WooCommerce/Templates/Single_Voucher_PDF
 * @version    1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$footer_content = get_option( 'evoucherwp_footer_content', '' );

?>

<div class="voucher-footer">

	<?php do_action( 'evwp_wc_voucher_before_footer' ); ?>

	<?php if ( ! empty( $footer_content ) ) : ?>
		<div class="footer-content">
			<?php echo wp_kses_post( $footer_content ); ?>
		</div>

	<?php endif; ?>

	<?php do_action( 'evwp_wc_voucher_after_footer' ); ?>

</div>
