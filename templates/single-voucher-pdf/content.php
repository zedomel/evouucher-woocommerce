<?php
/**
 * Single Voucher Content PDF
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
	$products = array();
	foreach ( $vouchers as $voucher ) :
		if ( ! in_array( $voucher->get_product_id(), $products, true ) ) : ?>
			<div class="voucher-content">
				<?php
					do_action( 'evoucherwp_before_voucher_content' );

					echo wp_kses_post( wpautop( wptexturize( $voucher->get_the_content() ) ) );

					do_action( 'evoucherwp_after_voucher_content' );
				?>
			</div>
		<?php endif;

		$products[] = $voucher->get_product_id();

	endforeach;
endif;

?>
