<?php
/**
 * Product data voucher HTML
 *
 * @package EVoucherWP_WooCommerce/Admin/Views
 * @version 1.0.0
 */
?>

<div id="evoucherwp_product_data" class="panel woocommerce_options_panel">
		<div class="options_group">
			<p class="form-field">
			<?php

			global $post;

			woocommerce_wp_select(
				array(
					'id'          => '_evoucherwp_codestype',
					'options'     => EVWP_WC_Admin_Voucher_Tab::get_codestype(),
					'label'       => __( 'Voucher Code Type', 'evoucherwp-woocommerce' ),
					'description' => __( 'Select the type of voucher code generator used by this product', 'evoucherwp-woocommerce' ),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'          => '_evoucherwp_singlecode',
					'label'       => __( 'Custom code', 'evoucherwp-woocommerce' ),
					'description' => __( 'Enter voucher custom code ', 'evoucherwp-woocommerce' ),
				)
			);

			woocommerce_wp_select(
				array(
					'id'          => '_evoucherwp_codelength',
					'options'     => array(
						'0'  => __( 'Select code length...', 'evoucherwp-woocommerce' ),
						'6'  => 6,
						'7'  => 7,
						'8'  => 8,
						'9'  => 9,
						'10' => 10,
					),
					'label'       => __( 'Voucher Code Length', 'evoucherwp-woocommerce' ),
					'description' => __( 'Select the length of voucher code', 'evoucherwp-woocommerce' ),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'    => '_evoucherwp_codeprefix',
					'label' => __( 'Voucher Code Prefix', 'evoucherwp-woocommerce' ),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'    => '_evoucherwp_codesuffix',
					'label' => __( 'Voucher Code Suffix', 'evoucherwp-woocommerce' ),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'    => '_evoucherwp_expiry',
					'label' => __( 'Voucher expiration in days', 'evoucherwp-woocommerce' ),
					'type'  => 'number',
					'min'   => '0',
				)
			);

			woocommerce_wp_checkbox(
				array(
					'id'          => '_evoucherwp_requireemail',
					'label'       => __( 'Require e-mail address', 'evoucherwp-woocommerce' ),
					'description' => __( 'Require a valid e-mail address to download voucher', 'evoucherwp-woocommerce' ),
				)
			);

			?>
			</p>
		</div>
	</div>
