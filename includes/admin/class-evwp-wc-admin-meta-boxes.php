<?php

/**
 * EVoucherWP WooCommerce Meta Boxes
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-meta-boxes.php
 *
 * Sets up the write panels used by products and orders (custom post types).
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
 * EVWP_WC_Admin_Meta_Boxes.
 */
class EVWP_WC_Admin_Meta_Boxes {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_filter( 'product_type_options', array( $this, 'product_type_voucher' ), 10, 3 );

		add_action( 'evoucherwp_process_evoucher_meta', 'EVWP_WC_Meta_Box_Order::save', 20, 2 );

		/*
		 * Add tab.
		 */
		add_filter( 'woocommerce_product_data_tabs', 'EVWP_WC_Admin_Voucher_Tab::add', 20 );

		/*
		 * Add content to tab.
		 */
		add_action( 'woocommerce_product_data_panels', 'EVWP_WC_Admin_Voucher_Tab::display', 20 );

		/*
		 * Save tab data.
		 */
		add_action( 'woocommerce_process_product_meta', 'EVWP_WC_Admin_Voucher_Tab::set', 20 );

		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
	}

	/**
	 * Add meta boxes
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'voucher_woocommerce',
			__( 'WooCommerce Order', 'evoucherwp-woocommerce' ),
			'EVWP_WC_Meta_Box_Order::output',
			'evoucher',
			'normal'
		);
	}

	/**
	 * Remove meta boxes
	 *
	 * @return [type] [description]
	 */
	public function remove_meta_boxes() {
		remove_meta_box( 'voucher_woocommerce', 'evoucherwp', 'normal' );
	}

	/**
	 * Add voucher product type
	 */
	public function product_type_voucher( $product_type_options ) {

		$product_type_options['evoucherwp_voucher'] = array(
			'id'            => '_evoucherwp_voucher',
			'wrapper_class' => 'hide_if_grouped hide_if_external ',
			'label'         => __( 'E-Voucher', 'evoucherwp-woocommerce' ),
			'description'   => __( 'E-Voucher products automatic generates e-voucher and send it to customers.', 'evoucherwp-woocommerce' ),
			'default'       => 'no',
		);
		return $product_type_options;
	}
}

new EVWP_WC_Admin_Meta_Boxes();


