<?php
/**
 * Customer voucher available email (plain text)
 *
 * @author      Jose A. Salim
 * @package     EVoucherWP_WooCommerce/Templates/Emails/
 * @version     1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

echo wp_kses_post( $email_heading ) . "\n\n";

if ( ! empty( $gift_message ) ) {
	echo wp_kses_post( $gift_message );
}

echo "****************************************************\n\n";

echo wp_kses_post( $email_body );

echo "\n****************************************************\n\n";

wc_get_template( 'emails/plain/email-addresses.php', array( 'order' => $order ) );

echo "\n****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
