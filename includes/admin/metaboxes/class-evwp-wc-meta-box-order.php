<?php
/**
 * Voucher Woocommerce Metabox
 *
 * Functions for displaying the woocommerce options for voucher
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-meta-box-order-data.php
 *
 * @author      Jose A. Salim
 * @category    Admin
 * @package     EVoucherWP_Woocommerce/Admin/MetaBoxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVWP_WC_Meta_Box_Order Class.
 */
class EVWP_WC_Meta_Box_Order {

	/**
	 * Options fields.
	 *
	 * @var array
	 */
	protected static $options = array();

	/**
	 * Init fields we display + save.
	 */
	public static function init_fields() {

		self::$options = apply_filters(
			'evoucherwp_admin_order_fields',
			array(
				'_evoucherwp_order_id' => array(
					'label'   => __( 'Order:', 'evoucherwp-woocommerce' ),
					'show'    => false,
					'class'   => 'select short',
					'type'    => 'select',
					'options' => evwp_get_orders_options(),
				),
				'_evoucherwp_item_id'  => array(
					'label'   => __( 'Product Item:', 'evoucherwp-woocommerce' ),
					'show'    => false,
					'class'   => 'select short',
					'type'    => 'select',
					'options' => array(),
				),
			)
		);
	}

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {

		self::init_fields();

		wp_nonce_field( 'evoucherwp_save_data', 'evoucherwp_meta_nonce' );

		echo '<div class="panel-wrap">';

		// Update product options
		$order_id = get_post_meta( $post->ID, '_evoucherwp_order_id', true );
		if ( intval( $order_id ) > 0 ) {
			$options['_evoucherwp_item_id']['options'] = evwp_get_items_options( $order_id );
		}

		foreach ( self::$options as $key => $field ) {
			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}
			if ( ! isset( $field['id'] ) ) {
				$field['id'] = $key;
			}
			evoucherwp_wp_select( $field );
		}

		echo '<div class="resend-voucher">';
		echo '<p><button type="button" class="button button-small">' . esc_html__( 'Resend voucher', 'evoucherwp-woocommerce' ) . '</button></p>';
		echo '</div></div>';

		echo '<div class="create-voucher-pdf">';
		echo '<p><button type="button" class="button button-small">' . esc_html__( 'Create PDF', 'evoucherwp-woocommerce' ) . '</button></p>';
		echo '</div></div>';

	}

	/**
	 * Save meta box data.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

		self::init_fields();

		if ( ! empty( self::$options ) ) {
			foreach ( self::$options as $key => $field ) {
				if ( ! isset( $field['id'] ) ) {
					$field['id'] = $key;
				}
				if ( isset( $_POST[ $field['id'] ] ) ) {
					update_post_meta( $post_id, $field['id'], intval( evwp_clean( $_POST[ $field['id'] ] ) ) );
				}
			}
		}

		clean_post_cache( $post_id );
	}
}
