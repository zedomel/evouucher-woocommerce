<?php

/**
 * Plugin Name: EVoucherWP-WooCommerce Add-On
 * Plugin URI: TODO
 * Description: EVoucherWP-WooCommerce allows integration of EVoucherWP with WooCommerce plugin. WooCommerce products can be set to generate vouchers to download, send vouchers as WooCommerce email, setup voucher to a specific Order and Product.
 * Author: José A. Salim
 * Version: 1.0.0
 * Author URI: TODO: https://github.com/zedomel/
 *
 * @package EVoucherWP_WooCommerce
 * @author Jose A. Salim
 * @version 1.0.0
 **/

if ( ! class_exists( 'EVoucherWP_WooCommerce' ) ) {

	include_once 'includes/class-evwp-wc-install.php';

	final class EVoucherWP_WooCommerce {


		/**
		 * Classes
		 *
		 * @var array
		 */
		public $classes;

		/**
		 * EVoucherWP-WooCommerce version
		 *
		 * @var string
		 */
		public $version = '1.0.3';

		/**
		 * The single instance of the class.
		 *
		 * @var EVoucherWP_WooCommerce
		 * @since 1.0
		 */
		protected static $_instance = null;

		/**
		 * Main EVoucherWP_WooCommerce Instance.
		 *
		 * Ensures only one instance of EVoucherWP_WooCommerce is loaded or can be loaded.
		 *
		 * @since 1.0
		 * @static
		 * @return EVoucherWP_WooCommerce - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'evoucherwp-woocommerce' ), '1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'evoucherwp-woocommerce' ), '1.0' );
		}

		/*
		 * Constructor
		 */
		private function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init_evwp_wc' ) );
			register_activation_hook( __FILE__, array( 'EVWP_WC_Install', 'install' ) );
			add_filter( 'evoucherwp_ajax_events', array( $this, 'add_ajax_actions' ) );
		}

		/**
		 * ADD ajax actions
		 *
		 * @param array $ajax_events ajax events
		 */
		function add_ajax_actions( $ajax_events ) {
			$ajax_events['select_order_id']    = array(
				'class'  => 'EVWP_WC_AJAX',
				'nopriv' => false,
			);
			$ajax_events['resend_voucher']     = array(
				'class'  => 'EVWP_WC_AJAX',
				'nopriv' => false,
			);
			$ajax_events['create_voucher_pdf'] = array(
				'class'  => 'EVWP_WC_AJAX',
				'nopriv' => false,
			);
			$ajax_events['change_voucher']     = array(
				'class'  => 'EVWP_WC_Change_Voucher',
				'nopriv' => false,
			);
			$ajax_events['set_live']           = array(
				'class'  => 'EVWP_WC_AJAX',
				'nopriv' => false,
			);
			return $ajax_events;
		}

		/**
		 * Initialize plugin
		 */
		function init_evwp_wc() {
			$this->classes = new stdClass();

			$this->define_constants();

			if ( ! class_exists( 'Woocommerce' ) || ! class_exists( 'EVoucherWP' ) ) {
				return;
			}

			$this->includes();
			$this->init_hooks();

			/*
			* Initialize classes.
			*/
			$this->initClasses();

			do_action( 'evoucherwp_wc_loaded' );
		}

		/**
		 * Define EVoucherWP Constants.
		 */
		private function define_constants() {
			$this->define( 'EVWP_WC_PLUGIN_FILE', __FILE__ );
			$this->define( 'EVWP_WC_ABSPATH', dirname( EVWP_WC_PLUGIN_FILE ) . '/' );
			$this->define( 'EVWP_WC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'EVWP_WC_VERSION', $this->version );
			$this->define( 'EVOUCHERWP_WC_VERSION', $this->version );
		}

		private function includes() {
			// Voucher Object
			include_once 'includes/class-evwp-wc-voucher.php';

			if ( $this->is_request( 'admin' ) ) {
				include_once 'includes/admin/class-evwp-wc-admin.php';
			}

			if ( $this->is_request( 'frontend' ) ) {
				$this->frontend_includes();
			}

			// JWT
			include_once 'includes/class-evwp-wc-jwt.php';

			// Voucher Factory
			include_once 'includes/class-evwp-wc-voucher-factory.php';

			// Assets
			include_once 'includes/class-evwp-wc-assets.php';

			// Send as gift
			include_once 'includes/class-evwp-wc-voucher-gift.php';

			// Ajax handler
			include_once 'includes/class-evwp-wc-ajax.php';

			// Functions
			include_once 'includes/evwp-wc-functions.php';

			// MyAccount EndPoint
			include_once 'includes/admin/class-evwp-wc-myaccount-endpoint.php';

			// Validate Voucher EndPoint
			include_once 'includes/admin/class-evwp-wc-validate-voucher-endpoint.php';

		}

		private function frontend_includes() {
			// Change voucher form handler
			include_once 'includes/class-evwp-wc-change-voucher.php';
		}

		/**
		 * Function used to Init EVoucherWP Template Functions - This makes them pluggable by plugins and themes.
		 */
		public function include_template_functions() {
			// Voucher Template Hooks
			include_once 'includes/evwp-wc-template-hooks.php';
			include_once 'includes/evwp-wc-template-functions.php';
			include_once 'includes/class-evwp-wc-voucher-pdf-creator.php';
		}

		private function init_hooks() {
			add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
			add_action( 'init', array( $this, 'init' ), 0 );

			add_filter( 'woocommerce_email_classes', array( $this, 'add_voucher_woocommerce_email' ) );
			// Display settings at Settings menu
			add_action( 'evoucherwp_after_settings_menu', 'EVWP_WC_Admin_Settings_Menu::display_settings' );
			// Save EVoucherWP-Woocommerce settings
			add_action( 'evoucherwp_save_settings', 'EVWP_WC_Admin_Settings_Menu::save' );

			add_filter( 'evoucherwp_get_settings_pages', array( $this, 'evoucherwp_wc_settings_tab' ) );
		}

		function evoucherwp_wc_settings_tab( $settings ) {
			$settings[] = include 'includes/settings/class-evwp-wc-settings.php';
		}

		function add_voucher_woocommerce_email( $email_classes ) {
			// include our custom email class
			require 'includes/class-evwp-wc-voucher-mail.php';
			// add the email class to the list of email classes that WooCommerce loads
			$email_classes['EVWP_WC_Voucher_Mail'] = new EVWP_WC_Voucher_Mail();

			return $email_classes;
		}

		/*
		 * Initialize PHP classes.
		 */
		function initClasses() {
			/*
			 * Initialize WooCommerce voucher render class.
			 */
			$this->classes->render = class_exists( 'EVWP_WC_Voucher_Render' ) ? new EVWP_WC_Voucher_Render() : 'Class does not exist!';

			/*
			 * Initialize WooCommerce tab class.
			 */
			$this->classes->tab = class_exists( 'EVWP_WC_Admin_Voucher_Tab' ) ? new EVWP_WC_Admin_Voucher_Tab() : 'Class does not exist!';

			/*
			 * Initialize Woocommerce send as gift form
			 */
			$this->classes->gift = class_exists( 'EVWP_WC_Voucher_Gift' ) ? new EVWP_WC_Voucher_Gift() : 'Class does not exist';

		}

		/**
		 * Init EVoucherWP when WordPress Initialises.
		 */
		public function init() {
			// Before init action.
			do_action( 'before_evoucherwp_wc_init' );

			// Set up localisation.
			$this->load_plugin_textdomain();

			// Init action.
			do_action( 'evoucherwp_wc_init' );
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/evoucherwp/evoucherwp-LOCALE.mo
		 *      - WP_LANG_DIR/plugins/evoucherwp-LOCALE.mo
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'evoucherwp-wc' );

			load_textdomain( 'evoucherwp_wc', WP_LANG_DIR . '/evoucherwp-wc/evoucherwp-wc-' . $locale . '.mo' );
			load_plugin_textdomain( 'evoucherwp_wc', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages' );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param  string      $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		public function template_path() {
			return apply_filters( 'evoucherwp_wc_template_path', 'evoucherwp-woocommerce/' );
		}

		/**
		 * Get Ajax URL.
		 *
		 * @return string
		 */
		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}

		/**
		 * What type of request is this?
		 *
		 * @param  string $type admin, ajax, cron or frontend.
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}
	}
}

/**
 * Main instance of EVoucherWP.
 *
 * Returns the main instance of EVoucherWP to prevent the need to use globals.
 *
 * @since  1.0
 * @return EVoucherWP
 */
function EvoucherWP_WC() {
	return EvoucherWP_WooCommerce::instance();
}

// Global for backwards compatibility.
$GLOBALS['evoucherwp_wc'] = EvoucherWP_WC();



