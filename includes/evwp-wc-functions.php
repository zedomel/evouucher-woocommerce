<?php
/**
 * EVoucherWP Woocommerce Voucher Functions
 *
 * Functions for voucher specific things.
 *
 * @author   Jose A. Salim
 * @package  EVoucherWP_Woocommerce/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get voucher by GUID
 *
 * @param  string $code guid
 * @return EVWP_WC_Voucher       voucher
 */
function evwp_get_voucher_by_guid( $code ) {

	$args = array(
		'post_type'      => 'evoucher',
		'posts_per_page' => 1,
		'meta_query'     => array(
			array(
				'key'   => '_evoucherwp_guid',
				'value' => sanitize_text_field( $code ),
			),
		),
	);

	$posts = get_posts( $args );

	if ( empty( $posts ) ) {
		// If empty, check if user provided it without removing prefix and suffix
		// Try it again
		$args = array(
			'post_type'      => 'evoucher',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'     => '_evoucherwp_guid',
					'value'   => '%' . sanitize_text_field( $code ) . '%',
					'compare' => 'LIKE',
				),
			),
		);

		$posts = get_posts( $args );
	}

	return isset( $posts[0] ) ? new EVWP_WC_Voucher( $posts[0] ) : false;
}

/**
 * Get shop orders
 *
 * @return array orders
 */
function evwp_get_orders() {
	$args   = array(
		'post_type'      => 'shop_order',
		'post_status'    => preg_replace( '/^/', 'wc-', wc_get_is_paid_statuses() ),
		'posts_per_page' => -1,
	);
	$orders = get_posts( $args );

	return $orders;
}

/**
 * Get products of an order
 *
 * @param  integer $order_id order id
 * @return array            products
 */
function evwp_get_products( $order_id = 0 ) {
	$products = array();
	if ( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$order_items = $order->get_items();
			$products    = array();
			foreach ( $order_items as $item ) {
				$products[] = $item->get_product();
			}
		}
	}
	return $products;
}

/**
 * Returs order options
 *
 * @return array options
 */
function evwp_get_orders_options() {
	$orders  = evwp_get_orders();
	$options = array( 0 => __( 'Select a value', 'evoucherwp-woocommerce' ) );
	foreach ( $orders as $order ) {
		$options[ $order->ID ] = '#' . $order->ID;
	}
	return $options;
}

/**
 * Get order items options
 *
 * @param  integer $order_id order id
 * @return array            options
 */
function evwp_get_items_options( $order_id = 0 ) {
	$options = array( 0 => __( 'Select a value', 'evoucherwp-woocommerce' ) );

	if ( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			foreach ( $order->get_items() as $item_id => $item ) {
				$options[ $item_id ] = $item->get_name();
			}
		}
	}
	return $options;
}

/**
 * Get all vouchers of an order
 *
 * @param  int $order_id order id
 * @return array vouchers
 */
function evwp_get_vouchers_by_order( $order_id ) {
	global $wpdb;

	if ( ! $order_id ) {
		return false;
	}

	$result = $wpdb->get_col( $wpdb->prepare( "SELECT p.ID from {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON p.ID = m.post_id WHERE p.post_type = 'evoucher' AND p.post_status = 'publish' AND m.meta_key='_evoucherwp_order_id' AND m.meta_value=%d", $order_id ) );
	if ( ! $result ) {
		return false;
	}

	$vouchers = array();
	foreach ( $result as $voucher_id ) {
		$vouchers[] = new EVWP_WC_Voucher( $voucher_id );
	}

	return $vouchers;
}

