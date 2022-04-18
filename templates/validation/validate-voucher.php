<?php
/**
 * Vouchers Validatation
 *
 * @author  Jose A. Salim
 * @package EVoucherWP_WooCommerce/Templates/Validation
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
do_action( 'evoucherwp_woocommerce_after_validate_voucher_header' );
?>

<div id="content" class="site-content">
	<div class="container">

		<?php do_action( 'evoucherwp_before_account_validate_voucher' ); ?>

		<div class="validate-voucher">
			<form id="validate_voucher_form" class="validate-voucher-form" method="post" action="<?php echo esc_url( EVWP_WC_Validate_Voucher_EndPoint::get_endpoint_url( home_url() ) ); ?> ">
				<div class="voucher-input-group">
					<label for="voucher-number"> <?php esc_html_e( 'Voucher number (PIN):', 'evoucherwp-woocommerce' ); ?> </label>
					<input id="voucher-number" name="validate-voucher" class="regular-text" name="cv_code" required placeholder="XXXXXXXXXX">
					<span id="helpBlock" class="help-block"> <?php esc_html_e( 'Input only the voucher number without prefix or suffix (if any)', 'evoucherwp-woocommerce' ); ?></span>
				</div>
				<button id="cv_submit" class="validate-voucher-btn button button-primary" type="submit"><?php esc_html_e( 'Validate', 'evoucherwp-woocommerce' ); ?></button>

				<?php wp_nonce_field( 'evoucherwp_validate_voucher', '_evoucherwp_validate_voucher_wpnonce' ); ?>

			</form>

		<?php
		if ( ! empty( $voucher ) ) :
			$msg   = '';
			$class = '';
			switch ( $voucher_status ) {
				case 'valid':
					$class = 'valid';
					$msg   = __( 'Success! Voucher validated!', 'evoucherwp-woocommerce' );
					break;
				case 'expired':
					$class = 'invalid';
					/* translators: %s voucher expiration date */
					$msg = sprintf( __( 'Oh! This Voucher has expired in %s!', 'evoucherwp-woocommerce' ), date_i18n( get_option( 'date_format' ), strtotime( $voucher->expiry ) ) );
					break;
				case 'notyetavailable':
					$class = 'invalid';
					/* translators: %s voucher availability date */
					$msg = sprintf( __( 'Oh! This Voucher will only be available after %s!', 'evoucherwp-woocommerce' ), date_i18n( get_option( 'date_format' ), strtotime( $voucher->startdate ) ) );
					break;
				case 'unavailable':
					$class = 'invalid';
					$msg   = __( 'Oh! This Voucher is not available anymore! It was already used.', 'evoucherwp-woocommerce' );
					break;
				default:
					$class = 'invalid';
					$msg   = __( 'Oh! This Voucher is not valid!', 'evoucherwp-woocommerce' );
					break;
			}
			?>

			<div class="alert <?php echo esc_attr( $class ); ?>"><h2><?php echo esc_html( $msg ); ?></h2></div>
		<?php endif; ?>
		</div>

		<?php do_action( 'evoucherwp_after_account_validate_voucher' ); ?>
	</div>
</div>
<?php
	do_action( 'evoucherwp_woocommerce_before_validate_voucher_footer' );
	get_footer();
?>
