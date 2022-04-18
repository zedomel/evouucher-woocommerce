<?php
/**
 * Installation related functions and actions.
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-install.php
 *
 * @author   Jose A. Salim
 * @category Admin
 * @package  EVoucherWP_WooCommerce/Classes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EVWP_WC_Install Class.
 */
class EVWP_WC_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
	}

	/**
	 * Check EVoucherWP WooCommerce version and run the updater is required.
	 *
	 * This check is done on all requests and runs if he versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'evoucherwp_wc_version' ) !== EVoucherWP_WC()->version ) {
			self::install();
		}
	}

	/**
	 * Install EVoucherWP WooCommerce.
	 */
	public static function install() {
		global $wpdb;

		if ( ! defined( 'EVWP_WC_INSTALLING' ) ) {
			define( 'EVWP_WC_INSTALLING', true );
		}

		self::create_capabilities();

		// self::create_options();

		// Queue upgrades/setup wizard
		$current_evwp_wc_version = get_option( 'evoucherwp_wc_version', null );

		// No versions? This is a new install :)
		if ( is_null( $current_evwp_wc_version ) ) {
			set_transient( '_evoucherwp_wc_activation_redirect', 1, 30 );
		}

		self::update_evoucherwp_wc_version();

		// Flush rules after install
		flush_rewrite_rules();
	}

	private static function create_capabilities() {
		global $wp_roles;

		$wp_roles->add_cap( 'administrator', 'validate_vouchers' );
		$wp_roles->add_cap( 'evoucherwp_manager', 'validate_vouchers' );
	}

	/**
	 * Update EVWP_WC version to current.
	 */
	private static function update_evoucherwp_wc_version() {
		delete_option( 'evoucherwp_wc_version' );
		add_option( 'evoucherwp_wc_version', EVoucherWP_WC()->version );
	}

	/**
	 * Get slug from path
	 *
	 * @param  string $key
	 * @return string
	 */
	private static function format_plugin_slug( $key ) {
		$slug = explode( '/', $key );
		$slug = explode( '.', end( $slug ) );
		return $slug[0];
	}
}

EVWP_WC_Install::init();
