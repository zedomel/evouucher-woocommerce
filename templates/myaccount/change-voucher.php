<?php
/**
 * Vouchers
 *
 * Shows vouchers on the account page.
 *
 * @author  Jose A. Salim
 * @package EVoucherWP_WooCommerce/Templates/MyAccount
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'evoucherwp_before_account_change_voucher' );

$guid = '';
if ( ! empty( $voucher_id ) ) {
	$voucher = new EVWP_WC_Voucher( $voucher_id );
	if ( $voucher ) {
		$guid = $voucher->guid;
	}
}

?>

<div class="change-voucher">
	<form id="cv_form" class="change-voucher-form" method="post">
		<div class="form-group">
			<label for="voucher-number"> <?php esc_html_e( 'Voucher number (PIN):', 'evoucherwp-woocommerce' ); ?> </label>
			<input id="voucher-number" class="form-control" name="cv_code" required placeholder="XXXXXXXXXX" value="<?php echo esc_attr( $guid ); ?>">
			<span id="helpBlock" class="help-block"> <?php esc_html_e( 'Input only the voucher number without prefix or suffix (if any)', 'evoucherwp-woocommerce' ); ?></span>
		</div>
		<button id="cv_submit" class="voucher-change-btn btn btn-default" type="submit"><?php esc_html_e( 'Change Voucher', 'evoucherwp-woocommerce' ); ?></button>

		<?php wp_nonce_field( 'evoucherwp_change_voucher', '_evoucherwp_change_voucher_wpnonce' ); ?>

	</form>
</div>

<?php do_action( 'evoucherwp_after_account_change_voucher' ); ?>
