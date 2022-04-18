<?php
/**
 * EVoucherWP WooCommerce Voucher Tab
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-meta-boxes.php
 *
 * Sets up WooCommerce Voucher Tab
 *
 * @author      Jose A. Salim
 * @category    Admin
 * @package     EVoucherWP_WooCommerce/Admin
 * @version     1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVWP_WC_Admin_Voucher_Tab.
 */
class EVWP_WC_Admin_Voucher_Tab {

	/**
	 * Add tab
	 */
	public static function add( $tabs ) {
		$tabs['evoucherwp'] = array(
			'label'  => __( 'E-Voucher WP', 'evoucherwp-woocommerce' ),
			'target' => 'evoucherwp_product_data',
			'class'  => array( 'show_if_voucher' ),
		);
		return $tabs;
	}

	/**
	 * Display tab
	 */
	public static function display() {
		include 'views/html-product-data-evoucherwp.php';
	}

	/**
	 * Set tab options
	 *
	 * @param int $post_id post id
	 */
	public static function set( $post_id ) {

		// E-Voucher are only allowed to simple and variable products.
		$product_type = isset( $_POST['product-type'] ) ? sanitize_text_field( $_POST['product-type'] ) : '';

		if ( 'grouped' === $product_type || 'external' === $product_type ) {
			update_post_meta( $post_id, '_evoucherwp_voucher', 'no' );
			return;
		}

		$is_voucher = isset( $_POST['_evoucherwp_voucher'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_evoucherwp_voucher', $is_voucher );

		if ( 'yes' === $is_voucher ) {
			foreach ( $_POST as $key => $value ) {
				if ( in_array( $key, array( '_evoucherwp_codelength', '_evoucherwp_expiry' ), true ) ) {
					$val = intval( $value );
				} elseif ( in_array( $key, array( '_evoucherwp_codestype', '_evoucherwp_codeprefix', '_evoucherwp_codesuffix', '_evoucherwp_singlecode' ), true ) ) {
					$val = sanitize_text_field( $value );
				}

				if ( ! empty( $val ) ) {
					update_post_meta( $post_id, $key, $val );
				}
			}

			$require_email = isset( $_POST['_evoucherwp_requireemail'] ) ? 'yes' : 'no';
			update_post_meta( $post_id, '_evoucherwp_requireemail', $require_email );
		}
	}

	/**
	 * Get code types options
	 *
	 * @return array code types
	 */
	public static function get_codestype() {
		return array(
			''           => __( 'Select a code type', 'evoucherwp-woocommerce' ),
			'random'     => __( 'Random codes', 'evoucherwp-woocommerce' ),
			'sequential' => __( 'Sequential codes', 'evoucherwp-woocommerce' ),
			'single'     => __( 'Single code', 'evoucherwp-woocommerce' ),
		);
	}
}
