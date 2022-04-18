<?php
/**
 * EVoucherWP WooCommerce My Account End-Point
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-meta-boxes.php
 *
 * Sets up the write panels used by vouchers.
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
 * EVWP_WC_MyAccount_EndPoint class
 */
class EVWP_WC_MyAccount_EndPoint {

	/**
	 * Custom query_vars name.
	 *
	 * @var string
	 */
	public $query_vars = array();

	/**
	 * Vouchers per page
	 *
	 * @var integer
	 */
	public $posts_per_page = 10;

	/**
	 * Plugin actions.
	 */
	public function __construct() {
		// Actions used to insert a new endpoint in the WordPress.
		add_action( 'init', array( $this, 'add_endpoints' ) );
		if ( ! is_admin() ) {
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
		}

		$this->init_query_vars();

		// Inserting your new tab/page into the My Account page.
		add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );

		foreach ( $this->query_vars as $key => $value ) {
			add_action( 'woocommerce_account_' . $key . '_endpoint', array( $this, 'endpoint_content' ) );
			add_action( 'evoucherwp_account_' . $key . '_endpoint_content', array( $this, 'account_' . str_replace( '-', '_', $key ) ) );
		}

		// Flush rewrite rules on plugin activation.
		register_activation_hook( __FILE__, array( 'EVWP_WC_MyAccount_EndPoint', 'install' ) );
	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {

		// Query vars to add to WP.
		$this->query_vars = array(
			'vouchers' => __( 'Vouchers', 'evoucherwp-woocommerce' ),
		);

		$change_voucher_enabled = get_option( 'evoucherwp_change_enabled', 'yes' );
		if ( 'yes' === $change_voucher_enabled ) {
			$this->query_vars['change-voucher'] = __( 'Change Voucher', 'evoucherwp-woocommerce' );
		}
	}

	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public function add_endpoints() {
		foreach ( $this->query_vars as $key => $var ) {
			add_rewrite_endpoint( $key, EP_ROOT | EP_PAGES );
		}
	}

	/**
	 * Returns endpoint url
	 *
	 * @param  string $endpoint  endpoint name
	 * @param  string $permalink base permalink url
	 * @return string            endpoint url
	 */
	public static function get_endpoint_url( $endpoint, $permalink = '' ) {
		if ( ! $permalink ) {
			$permalink = get_permalink();
		}
		return trailingslashit( $permalink ) . $endpoint;
	}

	/**
	 * Add new query var.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->query_vars as $key => $var ) {
			$vars[] = $key;
		}
		return $vars;
	}

	/**
	 * Get query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return $this->query_vars;
	}

	/**
	 * Get query current active query var.
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp;
		foreach ( $this->get_query_vars() as $key => $value ) {
			if ( isset( $wp->query_vars[ $key ] ) ) {
				return $key;
			}
		}
		return '';
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;

		// Map query vars to their keys, or get them if endpoints are not supported
		foreach ( $this->query_vars as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = $_GET[ $var ];
			}
		}
	}

	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @param array $items
	 * @return array
	 */
	public function new_menu_items( $items ) {
		// Temporary remove the logout menu item.
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );

		// Insert your custom endpoint.
		foreach ( $this->query_vars as $key => $value ) {
			$items[ $key ] = $value;
		}
		// Insert back the logout item.
		$items['customer-logout'] = $logout;

		return $items;
	}

	/**
	 * Endpoint HTML content.
	 */
	public function endpoint_content( $current_page ) {
		global $wp;

		foreach ( $this->query_vars as $key => $value ) {
			if ( isset( $wp->query_vars[ $key ] ) ) {
				do_action( 'evoucherwp_account_' . $key . '_endpoint_content', $current_page );
			}
		}
	}

	/**
	 * Handle request to account vouchers
	 *
	 * @param  int $current_page current page
	 */
	public function account_vouchers( $current_page ) {

		// Check if it is a customer or a voucher manager
		$current_user = wp_get_current_user();

		if ( in_array( 'evoucherwp_manager', $current_user->roles, true ) || user_can( $current_user, 'validate_vouchers' ) ) {
			$this->voucher_manager_vouchers_account( $current_user, $current_page );
		} else {
			$this->customer_vouchers_account( $current_user, $current_page );
		}
	}

	/**
	 * Admin vouchers
	 */
	private function voucher_manager_vouchers_account( $user, $current_page ) {

		$current_page = empty( $current_page ) ? 1 : absint( $current_page );

		$args  = array(
			'post_type'      => 'evoucher',
			'post_author'    => $user->ID,
			'post_status'    => 'publish',
			'posts_per_page' => $this->posts_per_page,
			'paged'          => $current_page,
		);
		$query = new WP_Query( $args );

		evwp_wc_get_template(
			'myaccount/manager-vouchers.php',
			array(
				'vouchers'      => $query->posts,
				'current_page'  => absint( $current_page ),
				'has_vouchers'  => $query->have_posts(),
				'total'         => $query->found_posts,
				'max_num_pages' => $query->max_num_pages,
			)
		);

	}

	/**
	 * Customer voucher
	 */
	function customer_vouchers_account( $current_user, $current_page ) {
		global $wpdb;

		$current_page = empty( $current_page ) ? 1 : absint( $current_page );

		$limit  = ( $current_page - 1 ) * $this->posts_per_page;
		$offset = $limit + $this->posts_per_page;

		$sql = $wpdb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS voucher_id FROM ( SELECT ID as order_id, meta_value as customer_id FROM $wpdb->posts p INNER JOIN
		 	$wpdb->postmeta m ON p.ID = m.post_id WHERE post_type = 'shop_order' AND meta_key = '_customer_user' AND  meta_value = %s ) orders
		 	INNER JOIN ( SELECT ID as voucher_id, meta_value as order_id FROM $wpdb->posts p INNER JOIN $wpdb->postmeta m ON p.ID = m.post_id WHERE m.meta_key = '_evoucherwp_order_id' AND post_status = 'publish' ) vouchers ON orders.order_id = vouchers.order_id ORDER BY voucher_id
		 	DESC LIMIT %d, %d",
			$current_user->ID,
			$limit,
			$offset
		);

		$vouchers_ids                     = $wpdb->get_col( $sql ); // phpcs.ignore
		$customer_vouchers                = new stdClass();
		$customer_vouchers->vouchers      = array();
		$customer_vouchers->max_num_pages = 1;
		$customer_vouchers->total         = 0;

		if ( $vouchers_ids ) {
			$found_posts                      = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			$customer_vouchers->total         = $found_posts;
			$customer_vouchers->max_num_pages = ceil( $found_posts / $this->posts_per_page );
			foreach ( $vouchers_ids as $voucher_id ) {
				$customer_vouchers->vouchers[] = new EVWP_WC_Voucher( $voucher_id );
			}
		}

		evwp_wc_get_template(
			'myaccount/vouchers.php',
			array(
				'current_page'      => absint( $current_page ),
				'customer_vouchers' => $customer_vouchers,
				'has_vouchers'      => 0 < $customer_vouchers->total,
			)
		);
	}

	/**
	 * Change voucher account template
	 *
	 * @param  int $voucher_id voucher id
	 */
	public function account_change_voucher( $voucher_id ) {

		$voucher_id = empty( $voucher_id ) ? 0 : absint( $voucher_id );
		evwp_wc_get_template(
			'myaccount/change-voucher.php',
			array(
				'voucher_id' => $voucher_id,
			)
		);
	}

	/**
	 * Plugin install action.
	 * Flush rewrite rules to make our custom endpoint available.
	 */
	public function install() {
		flush_rewrite_rules();
	}
}

new EVWP_WC_MyAccount_EndPoint();
