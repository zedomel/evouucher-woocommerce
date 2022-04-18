<?php
/**
 * EVoucherWP WooCommerce Voucher Factory
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-meta-boxes.php
 *
 * Class for generator vouchers from WooCommerce completed orders
 *
 * @author      Jose A. Salim
 * @package     EVoucherWP_WooCommerce/Classes
 * @version     1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVWP_WC_Voucher_Factory
 */
class EVWP_WC_Voucher_Factory {

	/**
	 * Voucher options
	 *
	 * @var array
	 */
	public static $voucher_options = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		self::init_options();
		add_filter( 'woocommerce_order_actions', 'EVWP_WC_Voucher_Factory::create_vouchers_order_action' );
		add_action( 'woocommerce_order_action_wc_send_vouchers_action', 'EVWP_WC_Voucher_Factory::process_create_vouchers_order_action' );
	}

	/**
	 * Create vouchers for a order and send them to customer
	 *
	 * @param WC_Order $order WC_Order object
	 */
	public static function process_create_vouchers_order_action( $order ) {
		$vouchers = self::create_vouchers_by_order( $order );
		if ( ! empty( $vouchers ) ) {
			$mailer = WC()->mailer();
			if ( isset( $mailer->emails['EVWP_WC_Voucher_Mail'] ) ) {
				$mail = $mailer->emails['EVWP_WC_Voucher_Mail'];
				$mail->trigger( $order->get_id(), $order, $vouchers );
			}
		}
	}

	/**
	 * Add order meta box action to create vouchers
	 */
	public static function create_vouchers_order_action( $actions ) {
		global $theorder;

		// bail if the order has not been paid or this action has been run
		if ( ! $theorder->is_paid() ) {// || get_post_meta( $theorder->get_id(), '_evwp_wc_order_vouchers_sent', true ) ){
			return $actions;
		}

		// add action
		$actions['wc_send_vouchers_action'] = __( 'Send voucher(s)', 'evoucherwp-woocommerce' );
		return $actions;
	}

	/**
	 * Initialize voucher options
	 */
	public static function init_options() {
		self::$voucher_options = array(
			'evoucherwp_requireemail',
			'evoucherwp_codeprefix',
			'evoucherwp_codesuffix',
			'evoucherwp_codestype',
			'evoucherwp_singlecode',
			'evoucherwp_codelength',
			'evoucherwp_expiry',
		);
	}

	/**
	 * Create vouchers for all order items of type e-voucher
	 *
	 * @param WC_Order $order WC_Order object
	 */
	public static function create_vouchers_by_order( $order ) {

		if ( empty( $order ) || ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		$vouchers = array();
		$vouchers = evwp_get_vouchers_by_order( $order->get_id() );
		if ( ! empty( $vouchers ) ) {
			return $vouchers;
		}

		foreach ( $order->get_items() as $item_id => $item ) {
			$product_id = ! empty( $item->get_variation_id() ) ? $item->get_variation_id() : $item->get_product_id();
			$is_voucher = get_post_meta( $product_id, '_evoucherwp_voucher', true );
			if ( 'yes' === $is_voucher ) {
				$create_vouchers = apply_filters( 'evwp_wc_create_voucher_for_item', true, $item_id, $item );
				if ( $create_vouchers ) {
					$item_vouchers = self::create_vouchers( $item );
					if ( $item_vouchers ) {
						foreach ( $item_vouchers as $item_voucher ) {
							$vouchers[] = $item_voucher;
						}
					}
				}
			}
		}

		do_action( 'evoucherwp_wc_vouchers_created', $order->get_id(), $vouchers );

		return $vouchers;
	}

	/**
	 * Create voucher for order item
	 *
	 * @param WC_Order_Item $item order item
	 */
	protected static function create_vouchers( $item ) {

		if ( empty( $item ) || ! is_a( $item, 'WC_Order_Item_Product' ) ) {
			return false;
		}

		$product = $item->get_product();
		if ( empty( $product ) ) {
			return false;
		}

		$vouchers = array();
		if ( 0 < $item->get_quantity() ) {
			$quantity = $item->get_quantity();
			for ( $i = 0; $i < $quantity; $i++ ) {
				$vouchers[] = self::create_voucher( $item, ( $i + 1 ) );
			}
		}

		return $vouchers;
	}

	/**
	 * Create voucher
	 *
	 * @param  WC_Order_Item $item  order item
	 * @param  integer       $index index
	 * @return EVoucherWP_WC voucher
	 */
	private static function create_voucher( $item, $index = 1 ) {

		$product  = $item->get_product();
		$order_id = $item->get_order_id();
		$item_id  = $item->get_id();

		$start_date = time();

		$voucher_data = array(
			'post_type'   => 'evoucher',
			'post_status' => 'publish',
			'ping_status' => 'closed',
			'post_author' => 1,
			/* translators: %1$d: order id, %2$s: product title, %3$d: item id, %4$d: voucher index */
			'post_title'  => sprintf( __( 'Order: #%1$d -  Product: %2$s - Item: %3$d - Voucher: #%4$d', 'evoucherwp-woocommerce' ), $order_id, $product->get_title(), $item->get_id(), $index ),
			'meta_input'  => array(
				'_evoucherwp_order_id'   => $order_id,
				'_evoucherwp_item_id'    => $item_id,
				'_evoucherwp_product_id' => $product->get_id(),
				'_evoucherwp_live'       => 'yes',
				'_evoucherwp_startdate'  => $start_date,

			),
		);

		// Set voucher options from product options or use default options
		$product_id = 'variation' === $product->get_type() ? $product->get_parent_id() : $product->get_id();
		foreach ( self::$voucher_options as $option ) {
			$value = get_post_meta( $product_id, '_' . $option, true );
			if ( empty( $value ) ) {
				$value = get_option( $option );
			}
			$voucher_data['meta_input'][ '_' . $option ] = $value;
		}

		$days_to_expiry = absint( $voucher_data['meta_input']['_evoucherwp_expiry'] );
		if ( 0 < $days_to_expiry ) {
			$expiry = $start_date + ( $days_to_expiry * 24 * 60 * 60 );
			$voucher_data['meta_input']['_evoucherwp_expiry'] = $expiry;
		} else {
			$voucher_data['meta_input']['_evoucherwp_expiry'] = 0;
		}

		$voucher_data = apply_filters( 'evoucherwp_new_voucher_data', $voucher_data );
		$voucher_id   = wp_insert_post( $voucher_data, true );
		$voucher      = new EVWP_WC_Voucher( $voucher_id );

		// Generate voucher code
		$code = evwp_generate_voucher_code( $voucher_id );
		update_post_meta( $voucher_id, '_evoucherwp_guid', $code );

		return $voucher;
	}
}

new EVWP_WC_Voucher_Factory();


