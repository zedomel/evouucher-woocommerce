<?php
/**
 * Voucher Availability email
 *
 * @author José A. Salim
 * @package EVoucherWP_WooCommerce/Templates/Emails/
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly ?>

<link href="https://fonts.googleapis.com/css?family=Parisienne|Ubuntu" rel="stylesheet"/>

<?php
	$heading = '<h1 style="' . apply_filters( 'evwp_wc_email_header_styles', 'color: #2b2b2b; font-size: 35px; text-align:center; font-family: \'Parisienne\', cursive;' ) . '">' . $email_heading . '</h1>';

	do_action( 'woocommerce_email_header', $heading );
	$count = count( $vouchers );
?>

<div style="text-align: center; font-family !important: \'Ubuntu\', sans-serif">
<?php
if ( ! empty( $gift_message ) ) :
	echo wp_kses_post( $gift_message );
endif;
?>

<h3 style="color: #600724; text-align: center;">
	<?php echo wp_kses_post( $email_body ); ?>
</h3>

<?php do_action( 'woocommerce_email_footer' ); ?>
