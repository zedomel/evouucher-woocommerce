<?php
/**
 * EvoucherWP WooCommerce Settings
 * Based on: https://github.com/woocommerce/woocommerce/blob/master/includes/admin/settings/class-wc-settings-general.php
 *
 * @author      Jose. A Salim
 * @category    Admin
 * @package     EVoucherWP_WooCommerce/Admin/Settings
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'EVWP_WC_Settings' ) ) :

	/**
	 * EVWP_WC_Settings.
	 */
	class EVWP_WC_Settings extends EVWP_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'evoucherwp-woocommerce';
			$this->label = __( 'WooCommerce', 'evoucherwp-woocommerce' );

			add_filter( 'evoucherwp_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'evoucherwp_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'evoucherwp_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = apply_filters(
				'evoucherwp_woocommerce_settings',
				array(

					array(
						'title' => __( 'Change Voucher Options', 'evoucherwp-woocommerce' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'change_voucher_wc_options',
					),

					array(
						'title'    => __( 'Allow voucher exchanges', 'evoucherwp-woocommerce' ),
						'desc'     => __( 'Allow customers to change vouchers after completing purchase.', 'evoucherwp-woocommerce' ),
						'desc_tip' => __( 'The voucher price (excluding taxes) will be added as discount into customer current cart', 'evoucherwp-woocommerce' ),
						'default'  => 'no',
						'id'       => 'evoucherwp_change_enabled',
						'type'     => 'checkbox',
					),
					array(
						'title'             => __( 'Maximum number of days to change voucher', 'evoucherwp-woocommerce' ),
						'desc'              => __( 'After the specified number of days customer will not be allowed to change the voucher', 'evoucherwp-woocommerce' ),
						'desc_tip'          => true,
						'id'                => 'evoucherwp_days_to_change',
						'type'              => 'number',
						'custom_attributes' => array(
							'min' => 0,
						),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'change_voucher_wc_options',
					),

					array(
						'title' => __( 'Gift Options', 'evoucherwp-woocommerce' ),
						'type'  => 'title',
						'desc'  => '',
						'id'    => 'gift_wc_options',
					),

					array(
						'title'    => __( 'Show send as gift form', 'evoucherwp-woocommerce' ),
						'desc'     => __( 'Show send as gift form at WooCommerce checkout page', 'evoucherwp-woocommerce' ),
						'desc_tip' => false,
						'default'  => 'no',
						'id'       => 'evoucherwp_show_gift_form',
						'type'     => 'checkbox',
					),

					array(
						'id'                => 'evoucherwp_wc_voucher_per_page',
						'type'              => 'number',
						'title'             => __( 'Vouchers per page', 'evoucherwp-woocommerce' ),
						'desc'              => __( 'Select the number of vouchers in each PDF page', 'evoucherwp-woocommerce' ),
						'desc_tip'          => true,
						'default'           => 2,
						'custom_attributes' => array(
							'min' => 1,
						),
					),

					array(
						'type' => 'sectionend',
						'id'   => 'gift_wc_options',
					),

				)
			);

			return apply_filters( 'evoucherwp_get_settings_' . $this->id, $settings );
		}

		/**
		 * Save settings.
		 */
		public function save() {
			$settings = $this->get_settings();
			EVWP_Admin_Settings::save_fields( $settings );
		}

		/**
		 * Output settings
		 */
		public function output() {
			wp_enqueue_script( 'evoucherwp-wc-settings-script' );
			parent::output();
		}

	}

endif;

return new EVWP_WC_Settings();
