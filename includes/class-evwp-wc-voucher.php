<?php
/**
 * EVoucherWP WooCommerce Voucher
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-meta-boxes.php
 *
 * Sets up the write panels used by products and orders (custom post types).
 *
 * @author      Jose A. Salim
 * @category    Admin
 * @package     EVoucherWP/Addons/WooCommerce/Includes/
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVWP_WC_Voucher.
 */
class EVWP_WC_Voucher extends EVWP_Voucher {


	/**
	 * Order id
	 *
	 * @var int
	 */
	public $order_id;

	/**
	 * Order item id
	 *
	 * @var int
	 */
	public $item_id;

	/**
	 * Product id
	 *
	 * @var int
	 */
	public $product_id;

	/**
	 * Constructor
	 *
	 * @param integer|WP_Post|EVocher_WP $voucher voucher id
	 */
	public function __construct( $voucher = 0 ) {
		parent::__construct( $voucher );
		$this->populate();
	}

	/**
	 * __get function.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key ) {

		if ( in_array( $key, array( 'order_id', 'product_id', 'item_id' ), true ) ) {
			$value = get_post_meta( $this->id, '_evoucherwp_' . $key, true );
			// Get values or default if not set
			$value = $value ? $value : '';

			if ( false !== $value ) {
				$this->$key = $value;
			}
		} else {
			$value = parent::__get( $key );
		}

		return $value;
	}

	/**
	 * Get order item id
	 *
	 * @return int item id
	 */
	public function get_item_id() {
		return $this->item_id;
	}

	/**
	 * Get order id
	 *
	 * @return int order id
	 */
	public function get_order_id() {
		return $this->order_id;
	}

	/**
	 * Get product id
	 *
	 * @return int product id
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * Get product
	 *
	 * @return WC_Product product
	 */
	public function get_product() {
		return wc_get_product( $this->product_id );
	}

	/**
	 * Get order
	 *
	 * @return WC_Order order
	 */
	public function get_order() {
		return wc_get_order( $this->order_id );
	}

	/**
	 * Get order item
	 *
	 * @return WC_Order_Item order item
	 */
	public function get_order_item() {
		return new WC_Order_Item_Product( $this->item_id );
	}

	/**
	 * Populate voucher properties
	 */
	function populate() {
		$this->order_id = get_post_meta( $this->id, '_evoucherwp_order_id', true );
		$this->item_id  = get_post_meta( $this->id, '_evoucherwp_item_id', true );
		$item           = $this->get_order_item();
		if ( $item ) {
			$this->product_id = $item->get_product_id();
		}
	}

	/**
	 * Returns voucher code
	 *
	 * @param  boolean    $prefix_suffix add prefix/suffix to code
	 * @param  WC_Product $product       product
	 * @return string voucher code
	 */
	function get_voucher_code( $prefix_suffix = true, $product = '' ) {

		if ( ! empty( $this->guid ) ) {
			return $prefix_suffix ? $this->codeprefix . $this->guid . $this->codesuffix : $this->guid;
		}

		if ( ! $product ) {
			$product = $this->get_product();
		}

		$codestype = get_post_meta( $product->get_id(), '_evoucherwp_codestype', true );
		$length    = get_post_meta( $product->get_id(), '_evoucherwp_codelength', true );

		if ( empty( $codestype ) ) {
			$codestype = get_option( 'evoucherwp_codestype', 'random' );
		}

		if ( empty( $length ) ) {
			$length = get_option( 'evoucherwp_codelength', 6 );
		}

		$code = generate_voucher_code( $codestype, absint( $length ) );

		return $prefix_suffix ? $this->codeprefix . $code . $this->codesuffix : $code;
	}

	/**
	 * Get a meta value from binded objects (order, order item, or product)
	 *
	 * @param  string        $key        meta key
	 * @param  WC_Order      $order      order
	 * @param  WC_Order_Item $order_item order item
	 * @param  WC_Product    $product    product
	 * @return mixed             meta value
	 */
	function get_value_from_bind_key( $key, $order, $order_item, $product ) {
		// Check if is post (product) keys
		if ( 0 === strncmp( $key, 'post_', 5 ) && ! empty( $product ) ) {
			$value = get_product_value( $key, $product );
		}
		// Or order keys
		elseif ( 0 === strncmp( $key, 'order_', 6 ) && ! empty( $order ) ) {
			$value = get_order_value( substr( $key, 6 ), $order );
		}

		// If still not found, try meta keys
		if ( empty( $value ) ) {
			// Try to get post meta following the order: Order, Order Item, Product
			$value = get_post_meta( $order->get_id(), $key );
			if ( empty( $value ) ) {
				if ( isset( $order_item[  $key ] ) ) {
					$value = $order_item[ $key ];
				}
				// Check for key without underscore ('_') at begining,
				elseif ( isset( $order_item[ ltrim( $key, '_' ) ] ) ) {
					$value = $order_item[ ltrim( $key, '_' ) ];
				}
			}
			// Last check it is a product meta_key
			if ( empty( $value ) && ! empty( $product ) ) {
				$value = get_post_meta( $product->get_id(), $key );
			}
		}

		return $value;
	}

	function get_order_value( $key, $order ) {
		return isset( $order[ $key ] ) ? $order[ $key ] : '';
	}

	function get_product_value( $key, $product ) {
		$product = wc_get_product( $id );
		if ( ! empty( $product ) ) {
			$post = $product->get_post_data();
			return isset( $post->$key ) ? $post->$key : '';
		}
	}
}


