<?php
/**
 * Load EVoucherWP WooCommerce assets
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-assets.php
 *
 * @author      Jose A. Salim
 * @package     EvoucherWP_WooCommerce/Classes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EVWP_WC_Assets' ) ) :

	/**
	 * EVWP_WC_Assets Class.
	 */
	class EVWP_WC_Assets {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		}

		/**
		 * Enqueue styles.
		 */
		public function admin_styles( $hook ) {

			global $post;

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Register admin styles
			wp_register_style( 'evoucherwp-wc-style', EVoucherWP_WC()->plugin_url() . '/assets/css/woocommerce-styles' . $suffix . '.css', array(), EVWP_WC_VERSION );

			if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && 'product' === $post->post_type ) {
				wp_enqueue_style( 'evoucherwp-wc-style' );
			}
		}

		/**
		 * Register frontend script and styles
		 */
		public function frontend_scripts( $hook ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_script( 'evoucherwp-wc-script', EVoucherWP_WC()->plugin_url() . '/assets/js/evoucherwp_wc' . $suffix . '.js', array( 'jquery' ), EVWP_WC_VERSION, true );
			wp_register_script( 'evoucherwp-wc-account-script', EVoucherWP_WC()->plugin_url() . '/assets/js/evoucherwp_wc_change_voucher' . $suffix . '.js', array( 'jquery' ), EVWP_WC_VERSION, true );
			wp_register_script( 'evoucherwp-wc-validate-voucher-script', EVoucherWP_WC()->plugin_url() . '/assets/js/evoucherwp_wc_validate_voucher' . $suffix . '.js', array( 'jquery' ), EVWP_WC_VERSION, true );

			wp_register_style( 'evoucherwp-wc-style', EVoucherWP_WC()->plugin_url() . '/assets/css/woocommerce-styles' . $suffix . '.css', array(), EVWP_WC_VERSION );

			// Font Awesome
			wp_register_style( 'evoucherwp-wc-font-awesome', EVoucherWP_WC()->plugin_url() . '/assets/css/font-awesome.min.css', array(), EVWP_VERSION );

			if ( is_account_page() ) {
				$ajax_url = EVoucherWP_WC()->ajax_url();
				wp_localize_script(
					'evoucherwp-wc-account-script',
					'_evoucherwp_wc_params',
					array(
						'ajax_url' => $ajax_url,
						'nonce'    => wp_create_nonce( '_evoucherwp_voucher_wpnonce' ),
					)
				);
				wp_enqueue_script( 'evoucherwp-wc-account-script' );
			}

			if ( is_checkout() ) {
				wp_enqueue_style( 'evoucherwp-wc-style' );
				wp_enqueue_style( 'evoucherwp-wc-font-awesome' );
			} elseif ( is_voucher() || get_query_var( 'validate-voucher', false ) !== false ) {
				wp_enqueue_style( 'evoucherwp-wc-style' );
			}
		}


		/**
		 * Enqueue admin scripts.
		 */
		public function admin_scripts( $hook ) {
			global $post;

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Register scripts
			wp_register_script( 'evoucherwp-wc-settings-script', EvoucherWP_WC()->plugin_url() . '/assets/js/admin/evoucherwp_wc_settings' . $suffix . '.js', array( 'jquery' ), EVWP_WC_VERSION, true );

			wp_register_script( 'evoucherwp-wc-admin-script', EVoucherWP_WC()->plugin_url() . '/assets/js/admin/evoucherwp_admin_wc' . $suffix . '.js', array( 'jquery' ), EVWP_WC_VERSION, true );

			// Edit evoucher
			if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
				if ( 'product' === $post->post_type || 'evoucher' === $post->post_type ) {
					$ajax_url = EVoucherWP_WC()->ajax_url();
					wp_localize_script( 'evoucherwp-wc-admin-script', 'url', $ajax_url );
					wp_localize_script( 'evoucherwp-wc-admin-script', 'admin_nonce', wp_create_nonce( '_evoucherwp_wc_admin_wpnonce' ) );
					wp_enqueue_script( 'evoucherwp-wc-admin-script' );
				}
			}
		}
	}

endif;

return new EVWP_WC_Assets();
