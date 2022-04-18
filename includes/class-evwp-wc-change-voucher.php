<?php
/**
 * EVoucherWP Change Voucher Shortcode
 *
 * @class    EVWP_Change_Voucher
 * @author   Jose A. Salim
 * @category Admin
 * @package  EVoucherWP_WooCommerce/Classes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'EVWP_WC_Change_Voucher' ) ) {

	/**
	 * EVWP_WC_Change_Voucher class.
	 */
	class EVWP_WC_Change_Voucher {


		public static function init() {
			// add_action( 'woocommerce_checkout_update_order_meta', 'EVWP_WC_Change_Voucher::update_voucher_after_changed', 10, 2 );
			add_filter( 'woocommerce_coupon_is_valid_for_product', 'EVWP_WC_Change_Voucher::validate_coupon', 10, 3 );
		}

		public static function validate_coupon( $valid, $product, $coupon ) {
			$product_id  = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
			$is_evoucher = get_post_meta( $product_id, '_evoucherwp_voucher', true );
			return $valid && 'yes' === $is_evoucher;
		}

		// public static function update_voucher_after_changed( $order_id, $data ){

		// $changed_vouchers = WC()->session->get( 'evwp_voucher', array() );
		// if ( ! empty( $changed_vouchers ) ){
		// foreach ($changed_vouchers as $voucher_id => $guid) {
		// update_post_meta( $voucher_id, '_evoucherwp_live', 'no' );

		// At this point coupon has been used, so delete it.
		// TODO: someone should keep coupons for history,
		// considers to not delete it, perhaps using a hook
		// $coupon_data = new WC_Coupon( $guid );
		// if( !empty( $coupon_data->get_id() ) ) {
		// $order = wc_get_order()
		// $coupon_data->set_used_by( array( ))
		// }
		// }
		// }
		// }

		/**
		 * Change voucher
		 */
		public static function change_voucher() {

			if ( ! check_ajax_referer( '_evoucherwp_voucher_wpnonce', 'security', false ) ) {
				echo wp_json_encode(
					array(
						'valid'   => false,
						'message' => __(
							'Could not change your voucher. Please try again.',
							'evoucherwp-woocommerce'
						),
					)
				);
				die();
			}

			if ( isset( $_POST['cv_code'] ) ) {

				$current_user = wp_get_current_user();
				if ( ( $current_user instanceof WP_User ) ) {
					$email = $current_user->exists() && isset( $current_user->user_email ) ? $current_user->user_email : '';
				}

				if ( empty( $email ) && isset( $_POST['cv_email'] ) ) {
					$email = sanitize_email( $_POST['cv_email'] );
				}

				$code = sanitize_text_field( $_POST['cv_code'] );

				if ( ! empty( $email ) && ! empty( $code ) ) {

					$voucher = evwp_get_voucher_by_guid( $code );

					if ( ! empty( $voucher ) ) {

						// Check the owner of this voucher
						$order = wc_get_order( $voucher->order_id );
						if ( $order ) {

							$owner_email = get_post_meta( $order->get_id(), '_evoucherwp_gift_email', true );
							if ( empty( $owner_email ) ) {
								$owner_email = $order->get_billing_email();
							}

							// Valid email?
							if ( ! empty( $owner_email ) && $owner_email === $email ) {
								// Check if voucher is alive
								if ( ! $voucher->live ) {
									echo wp_json_encode(
										array(
											'valid'   => false,
											'message' => __(
												'Voucher already used! You can not change it.',
												'evoucherwp-woocommerce'
											),
										)
									);
									die();
								}

								// Check if it does not exceds allowed period to change
								if ( ! $voucher->can_change() ) {
									echo wp_json_encode(
										array(
											'valid '  => false,
											'message' => __(
												'The exchange period has ended or your voucher has expired!',
												'evoucherwp-woocommerce'
											),
										)
									);
									die();
								}

								// Check if this voucher is not in WC cart yet
								$coupons = WC()->cart->get_coupons();
								if ( empty( $coupons ) || ! in_array( $voucher->guid, array_keys( $coupons ), true ) ) {
									$item_id = $voucher->get_item_id();
									if ( $item_id ) {
										$total = $order->get_line_total( $order->get_item( $item_id ) );
										if ( self::create_discount_coupon( $voucher, $total, $owner_email ) !== false ) {
											if ( WC()->cart->add_discount( $voucher->guid ) ) {
												echo wp_json_encode(
													array(
														'valid'   => true,
														'message' => sprintf(
															/* translators: %s: voucher total price */
															__( 'A discount of %s (voucher total price excluding taxes) was added to you cart.', 'evoucherwp-woocommerce' ),
															wc_price( $total )
														),
													)
												);
												die();
											}
										}
									}
								} else {
									echo wp_json_encode(
										array(
											'valid'   => false,
											'message' => __( 'This voucher was already selected to change.', 'evoucherwp-woocommerce' ),
										)
									);
										die();
								}
							}
						}
					}
				}
			}
			echo wp_json_encode(
				array(
					'valid'   => false,
					'message' => __( 'Could not change your voucher. Please try again.', 'evoucherwp-woocommerce' ),
				)
			);
			die();
		}

		/**
		 * Create a discount coupon for a voucher
		 *
		 * @param  EVoucherWP_WC $voucher     voucher
		 * @param  float         $amount      coupon amount
		 * @param  string        $owner_email voucher owner email
		 * @return WC_Coupon|boolea     discount coupon or false voucher is invalid
		 */
		static function create_discount_coupon( $voucher, $amount = 0, $owner_email = '' ) {

			if ( empty( $voucher->guid ) ) {
				return false;
			}

			$coupon = array(
				'post_title'   => $voucher->guid,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_author'  => 1,
				'post_type'    => 'shop_coupon',
			);

			$new_coupon_id = wp_insert_post( $coupon );
			$new_coupon    = new WC_Coupon( $new_coupon_id );

			// Add meta
			$new_coupon->set_discount_type( 'fixed_cart' );
			$new_coupon->set_amount( $amount );
			$new_coupon->set_individual_use( false );
			$new_coupon->set_product_ids( array() );
			$new_coupon->set_excluded_product_ids( array() );
			$new_coupon->set_usage_limit( 1 );
			$new_coupon->set_free_shipping( false );
			$new_coupon->set_email_restrictions( $owner_email );

			update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' ); // Not used WC 3.0+
			update_post_meta( $new_coupon_id, '_evoucherwp_coupon_voucher_id', $voucher->id );

			$new_coupon->save();

			return $new_coupon;
		}
	}

	EVWP_WC_Change_Voucher::init();
}
