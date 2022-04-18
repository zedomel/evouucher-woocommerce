<?php
/**
 * EVoucherWP WooCommerce Validate Voucher End-Point
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-meta-boxes.php
 *
 * Sets up the write panels used by allowed users to validate vouchers.
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
 * EVWP_WC_Validate_Voucher_EndPoint Class
 */
class EVWP_WC_Validate_Voucher_EndPoint {

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

		add_action( 'template_redirect', array( $this, 'endpoint_content' ) );
		add_action( 'evoucherwp_account_validate_voucher_endpoint_content', array( $this, 'validate_voucher_endpoint' ) );

		// Flush rewrite rules on plugin activation.
		register_activation_hook( __FILE__, array( 'EVWP_WC_Validate_Voucher_EndPoint', 'install' ) );
	}

	/**
	 * Register new endpoint to use inside Site root.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public function add_endpoints() {
		add_rewrite_endpoint( 'validate-voucher', EP_ROOT );
	}

	/**
	 * Get endpoint url
	 *
	 * @param  string $permalink base permalink
	 * @return string            endpoint url
	 */
	public static function get_endpoint_url( $permalink = '' ) {
		if ( ! $permalink ) {
			$permalink = get_permalink();
		}
		return trailingslashit( $permalink ) . 'validate-voucher';
	}

	/**
	 * Add new query var.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'validate-voucher';
		return $vars;
	}

	/**
	 * Get query current active query var.
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp;

		if ( isset( $wp->query_vars['validate-voucher'] ) ) {
			return 'validate-voucher';
		}
		return '';
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;

		// Map query vars to their keys, or get them if endpoints are not supported
		if ( isset( $_REQUEST['validate-voucher'] ) ) {
				$wp->query_vars['validate-voucher'] = sanitize_text_field( $_REQUEST['validate-voucher'] );
		}
	}

	/**
	 * Endpoint HTML content.
	 */
	public function endpoint_content() {
		global $wp;

		if ( ! isset( $wp->query_vars['validate-voucher'] ) || ! is_front_page() ) {
			return;
		}

		$voucher_code = $wp->query_vars['validate-voucher'];
		do_action( 'evoucherwp_account_validate_voucher_endpoint_content', $voucher_code );
	}

	/**
	 * Validade voucher endpoint
	 *
	 * @param  string $voucher_code voucher guid
	 */
	public function validate_voucher_endpoint( $jwt ) {

		if ( ! is_user_logged_in() ) {
			auth_redirect();
		}

		$current_user = wp_get_current_user();

		error_log( $jwt );

		if ( current_user_can( 'validate_vouchers' ) ) {

			$voucher_status = false;
			$payload        = EVWP_WC_JWT::decode( $jwt );

			if ( $payload ) {
				$voucher = evwp_get_voucher_by_guid( $payload->code );

				if ( ! empty( $voucher ) && apply_filters( 'evwp_wc_validate_jwt', true, $payload ) ) {
					$voucher_status = $voucher->is_valid();
					if ( 'valid' === $voucher_status ) {
						// Set voucher to used (live == no)
						update_post_meta( $voucher->id, '_evoucherwp_live', 'no' );
					}
				}
			}

			evwp_wc_get_template(
				'validation/validate-voucher.php',
				array(
					'voucher'        => isset( $voucher ) ? $voucher : '',
					'voucher_status' => $voucher_status,
				)
			);
		} else {
			wp_safe_redirect( home_url() );
		}
		die();
	}

	/**
	 * Plugin install action.
	 * Flush rewrite rules to make our custom endpoint available.
	 */
	public function install() {
		flush_rewrite_rules();
	}
}

new EVWP_WC_Validate_Voucher_EndPoint();
