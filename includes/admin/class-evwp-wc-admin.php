<?php
/**
 * EVoucher WooCommerce Admin
 *
 * @class    EVWP_WC_Admin
 * @author   Jose A. Salim
 * @category Admin
 * @package  EvoucherWP_WooCommerce/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVWP_WC_Admin class.
 */
class EVWP_WC_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_filter( 'evoucherwp_general_settings', array( $this, 'add_dompdf_path_field' ), 10, 1 );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		// E-Voucher Product Tab
		include_once 'class-evwp-wc-admin-voucher-tab.php';
		// Meta-Box Class
		include_once 'metaboxes/class-evwp-wc-meta-box-order.php';
		include_once 'class-evwp-wc-admin-meta-boxes.php';
	}

	/**
	 * Add DOMPDF path admin settings
	 */
	public function add_dompdf_path_field( $settings ) {
		$settings[] = array(
			'title'    => __( 'DomPDF path', 'evoucherwp-woocommerce' ),
			'desc'     => __( 'Full path to DomPDF PHP library', 'evoucherwp-woocommerce' ),
			'desc_tip' => true,
			'css'      => '',
			'id'       => 'evoucherwp_wc_dompdf_path',
			'type'     => 'text',
			'default'  => EVWP_WC_ABSPATH . 'external/dompdf',
		);

		return $settings;
	}
}

return new EVWP_WC_Admin();
