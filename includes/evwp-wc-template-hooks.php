<?php
/**
 * EVoucherWP Woocommerce Template Hooks
 *
 * Action/filter hooks used for EVoucherWP functions/templates.
 *
 * @author      Jose A. Salim
 * @category    Core
 * @package     EVoucherWP_WooCommerce/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'evoucherwp_voucher_image', 'evwp_voucher_product_image', 20, 2 );

add_filter( 'evoucherwp_template_title', 'evoucherwp_template_product_title', 10 );

add_action( 'evoucherwp_after_voucher_content', 'evoucherwp_product_content_after_voucher_content' );

add_filter( 'evoucherwp_voucher_meta', 'evoucherwp_wc_product_variation', 15, 2 );

add_filter( 'evoucherwp_voucher_meta', 'evoucherwp_wc_customer_data', 10, 2 );

add_action( 'evwp_wc_voucher_meta', 'evwp_wc_voucher_meta', 10, 1 );

add_action( 'evoucherwp_after_main_content', 'evwp_wc_voucher_qrcode', 10, 1 );

add_action( 'evwp_wc_voucher_qrcode', 'evwp_wc_voucher_qrcode_image', 10, 1 );

add_action( 'evwp_wc_voucher_content', 'evwp_wc_voucher_content', 5, 2 );
