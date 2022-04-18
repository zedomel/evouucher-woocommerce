<?php
/**
 * EVoucherWP WooCommerce Voucher Mail PDF
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-meta-boxes.php
 *
 * Class for add WooCommerce Notification E-mail and send vouchers by e-mail
 *
 * @author      Jose A. Salim
 * @package     EVoucherWP_WooCommerce/Classes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'EVWP_WC_Voucher_Mail_PDF', false ) ) :

	/**
	 * EVWP_WC_Voucher_Mail_PDF Class
	 */
	class EVWP_WC_Voucher_Mail_PDF extends WC_EMail {

		/**
		 * Vouchers
		 *
		 * @var array
		 */
		public $vouchers = [];

		/**
		 * Send a copy to administrator
		 *
		 * @var string
		 */
		private $send_admin_copy;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id             = 'evoucherwp_voucher_pdf';
			$this->customer_email = true;
			$this->title          = __( 'Voucher Available', 'evoucherwp-woocommerce' );
			$this->description    = __( 'Voucher emails are sent when a customer completes a order', 'evoucherwp-woocommerce' );

			$this->send_admin_copy = 'no';

			// Default template base if not declared in child constructor
			$this->template_base = EVoucherWP_WC()->plugin_path() . '/templates/';

			$this->template_html  = 'emails/customer-new-voucher-pdf.php';
			$this->template_plain = 'emails/customer-new-voucher-plain-pdf.php';

			// Trigger on payment complete
			// Trigger just after order has completed
			$order_status = $this->get_option( 'order_status' );
			add_action( 'woocommerce_order_status_' . $order_status, [ $this, 'trigger' ], 30, 2 );

			parent::__construct();
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int      $orderid WC_Order id
		 * @param WC_Order $order WC_Order object
		 * @param array    $vouchers vouchers to send
		 */
		public function trigger( $order_id, $order, $vouchers = '' ) {
			$this->setup_locale();

			if ( ! $order_id ) {
				return;
			}

			$this->object = $order;
			if ( empty( $vouchers ) ) {
				$this->vouchers = EVWP_WC_Voucher_Factory::create_vouchers_by_order( $order );
			} else {
				$this->vouchers = $vouchers;
			}

			// If no voucher exists, do not send email
			if ( empty( $this->vouchers ) ) {
				$this->restore_locale();
				return;
			}

			// Get the e-mail of receiver to send vouchers as gifts
			$this->recipient = get_post_meta( $order_id, '_evoucherwp_gift_email', true );
			$this->message   = '';
			if ( ! empty( $this->recipient ) ) {
				$this->message = get_post_meta( $order_id, '_evoucherwp_gift_message', true );
				$this->message = apply_filters( 'evoucherwp_wc_gift_message_email', $this->message, $this->vouchers, $order );
			} else {
				// If it is not a gift, send vouchers to buyer
				$this->recipient = $order->get_billing_email();
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$headers = $this->get_headers();
				if ( $this->is_send_admin_copy() ) {
					$admin_email = get_option( 'admin_email' );
					$blogname    = get_option( 'blogname' );
					$headers    .= "BCC: {$blogname} <{$admin_email}>\r\n";
				}

				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $headers, $this->get_attachments() );

				$order->add_order_note(
					_n(
						'Voucher has been sent to customer',
						'Vouchers have been sent to customer',
						count( $this->vouchers ),
						'evoucherwp-woocommerce'
					)
				);

				update_post_meta( $order->get_id(), '_evwp_wc_order_vouchers_sent', true );
			}

			$this->restore_locale();
		}

		/**
		 * Send a copy to administrator
		 *
		 * @return boolean true if it is enabled, false otherwise
		 */
		public function is_send_admin_copy() {
			return 'yes' === $this->get_option( 'send_admin_copy' );
		}

		/**
		 * Get email attachments.
		 *
		 * @return array
		 */
		public function get_attachments() {
			$attachments_pdfs = EVWP_Voucher_PDF_Creator::create_pdf( $this->vouchers );
			return apply_filters( 'woocommerce_email_attachments', $attachments_pdfs, $this->id, $this->object );
		}

		/**
		 * Get email subject.
		 *
		 * @since  1.0.3
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Your voucher is available', 'evoucherwp-woocommerce' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  1.0.3
		 * @return string
		 */
		public function get_default_heading() {
			return _n( 'Your Voucher is Available', 'Your Vouchers are Available', count( $this->vouchers ), 'evoucherwp-woocommerce' );
		}

		/**
		 * get_content_html function.
		 *
		 * @since 1.0
		 * @return string
		 */
		public function get_content_html() {
			return evwp_wc_get_template_html(
				$this->template_html,
				[
					'order'         => $this->object,
					'vouchers'      => $this->vouchers,
					'gift_message'  => $this->message,
					'plain_text'    => false,
					'email_heading' => $this->get_heading(),
					'email'         => $this,
				]
			);
		}

		/**
		 * get_content_plain function.
		 *
		 * @since 0.1
		 * @return string
		 */
		public function get_content_plain() {
			return evwp_wc_get_template_html(
				$this->template_plain,
				[
					'order'         => $this->object,
					'vouchers'      => $this->vouchers,
					'gift_message'  => $this->message,
					'plain_text'    => true,
					'email_heading' => $this->get_heading(),
					'email'         => $this,
				]
			);
		}

		/**
		 * Initialize Settings Form Fields
		 *
		 * @since 0.1
		 */
		public function init_form_fields() {
			$this->form_fields = [
				'enabled'         => [
					'title'   => __( 'Enable/Disable', 'evoucherwp-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'evoucherwp-woocommerce' ),
					'default' => 'yes',
				],
				'subject'         => [
					'title'       => __( 'Subject', 'evoucherwp-woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'evoucherwp-woocommerce' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				],
				'heading'         => [
					'title'       => __( 'Email Heading', 'evoucherwp-woocommerce' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'evoucherwp-woocommerce' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				],
				'order_status'    => [
					'title'       => __( 'Order Status', 'evoucherwp-woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose when send vouchers according to order status', 'evoucherwp-woocommerce' ),
					'default'     => 'completed',
					'class'       => 'order_status',
					'options'     => [
						'completed'  => __( 'Completed', 'evoucherwp-woocommerce' ),
						'processing' => __( 'Processing', 'evoucherwp-woocommerce' ),
					],
				],
				'email_type'      => [
					'title'       => __( 'Email type', 'evoucherwp-woocommerce' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'evoucherwp-woocommerce' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				],
				'send_admin_copy' => [
					'title'   => __( 'Send a copy to administrator', 'evoucherwp-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Send a copy to administrator', 'evoucherwp-woocommerce' ),
					'default' => 'yes',
				],
			];
		}
	}

endif;
