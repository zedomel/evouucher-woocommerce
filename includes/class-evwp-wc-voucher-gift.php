<?php
/**
 * EVoucherWP WooCommerce Checkout gift form
 *
 * @author      Jose A. Salim
 * @package     EVoucherWP_WooCommerce/Classes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVWP_WC_Voucher_Gift.
 */
class EVWP_WC_Voucher_Gift {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'woocommerce_before_order_notes', array( $this, 'gift_message_checkout' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'checkout_field_process' ) );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'checkout_field_display_admin_order_meta' ), 10, 1 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'checkout_field_update_order_meta' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts
	 *
	 * @return [type] [description]
	 */
	function enqueue_scripts() {
		if ( is_checkout() ) {
			wp_enqueue_script( 'evoucherwp-wc-script' );
		}
	}

	/**
	 * Display gift message checkout
	 *
	 * @param  WC_Checkout $checkout checkout
	 */
	function gift_message_checkout( $checkout ) {

		$show_gift_form = get_option( 'evoucherwp_show_gift_form', 'no' );

		if ( 'yes' === $show_gift_form ) {
			echo '<div id="gift-voucher"><span class="gift-icon fa fa-gift"><h3 class="gift-title">' . esc_html__( 'Send as gift', 'evoucherwp-woocommerce' ) . '</h3></span>';

			woocommerce_form_field(
				'_evoucherwp_gift_checkbox',
				array(
					'type'        => 'checkbox',
					'class'       => array( 'input-checkbox, form-row-wide' ),
					'label'       => __( 'Offer this voucher to someone special', 'evoucherwp-woocommerce' ),
					'require'     => false,
					'clear'       => true,
					'label_class' => array( 'gift-checkbox' ),
				),
				$checkout->get_value( '_evoucherwp_gift_checkbox' )
			);

			woocommerce_form_field(
				'_evoucherwp_gift_email',
				array(
					'type'        => 'text',
					'class'       => array( 'gift-email form-row-first gift-input hide' ),
					'label'       => __( 'E-mail to send the Voucher', 'evoucherwp-woocommerce' ),
					'placeholder' => '',
					'required'    => false,
					'clear'       => false,
				),
				$checkout->get_value( '_evoucherwp_gift_email' )
			);

			woocommerce_form_field(
				'_evoucherwp_gift_message',
				array(
					'type'        => 'textarea',
					'class'       => array( 'gift-message form-row-last gift-input hide' ),
					'label'       => __( 'Message', 'evoucherwp-woocommerce' ),
					'placeholder' => '',
					'required'    => false,
					'clear'       => false,
				),
				$checkout->get_value( '_evoucherwp_gift_message' )
			);

			echo '</div>';
		}
	}

	/**
	 * Display gift fields
	 * TODO: nonce
	 */
	function checkout_field_process() {
		// Check if set, if its not set add an error.
		if ( isset( $_POST['_evoucherwp_gift_checkbox'] ) && '1' === $_POST['_evoucherwp_gift_checkbox'] && empty( $_POST['_evoucherwp_gift_email'] ) ) {
			wc_add_notice( __( "Please give the recipient's Email to send the gift", 'evoucherwp-woocommerce' ), 'error' );
		}
	}

	/**
	 * Display field value on the order edit page
	 */
	function checkout_field_display_admin_order_meta( $order ) {
		$to_email = get_post_meta( $order->get_id(), '_evoucherwp_gift_email', true );
		if ( ! empty( $to_email ) ) {
			echo '<p><strong>' . esc_html__( "Recipient's Email", 'evoucherwp-woocommerce' ) . ':</strong> ' . esc_html( $to_email ) . '</p>';
			echo '<p><strong>' . esc_html__( 'Message', 'evoucherwp-woocommerce' ) . ':</strong> ' . absint( get_post_meta( $order->get_id(), '_evoucherwp_gift_message', true ) ) . '</p>';
		}
	}

	/**
	 * Update the order meta with field value
	 * TODO: nonce
	 */
	function checkout_field_update_order_meta( $order_id ) {
		if ( ! empty( $_POST['_evoucherwp_gift_checkbox'] ) && '1' === $_POST['_evoucherwp_gift_checkbox'] ) {
			update_post_meta( $order_id, '_evoucherwp_gift_email', sanitize_email( $_POST['_evoucherwp_gift_email'] ) );
			update_post_meta( $order_id, '_evoucherwp_gift_message', sanitize_text_field( $_POST['_evoucherwp_gift_message'] ) );
		}
	}
}


