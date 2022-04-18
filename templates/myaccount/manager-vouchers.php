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

do_action( 'evoucherwp_before_account_vouchers', $has_vouchers );

$can_change_voucher = get_option( 'evoucherwp_change_enabled', 'no' );
?>


<?php if ( $has_vouchers ) : ?>

	<table class="woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
				<?php foreach ( evwp_get_account_vouchers_columns() as $column_id => $column_name ) : ?>
					<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
				<th class="voucher-live"><span class="nobr"><?php esc_html_e( 'LIVE', 'evoucherwp-woocommerce' ); ?></span></th>
			</tr>
		</thead>

		<tbody>
			<?php
			foreach ( $vouchers as $voucher_id ) :
				$voucher = new EVWP_WC_Voucher( $voucher_id );
				$order   = wc_get_order( $voucher->get_order_id() );
				?>
				<tr class="order">
					<?php foreach ( evwp_get_account_vouchers_columns() as $column_id => $column_name ) : ?>
						<td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php if ( has_action( 'evwp_my_account_my_vouchers_column_' . $column_id ) ) : ?>
								<?php do_action( 'evwp_my_account_my_vouchers_column_' . $column_id, $order ); ?>

							<?php elseif ( 'order-number' === $column_id ) : ?>
								<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
									<?php echo esc_html_x( '#', 'hash before order number', 'evoucherwp-woocommerce' ) . esc_html( $order->get_order_number() ); ?>
								</a>

								<?php
							elseif ( 'order-date' === $column_id ) :
								$order_date = $order->get_date_created();
								?>
								<time datetime="<?php echo esc_attr( date( 'Y-m-d', strtotime( $order_date ) ) ); ?>" title="<?php echo esc_attr( strtotime( $order_date ) ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $order_date ) ) ); ?></time>

							<?php elseif ( 'voucher-status' === $column_id ) : ?>
								<?php
								$live    = $voucher->live;
								$expired = $voucher->has_expired();
								if ( 'yes' === $live && ! $expired ) {
									esc_html_e( 'Available', 'evoucherwp-woocommerce' );
								} elseif ( 'yes' !== $live ) {
									esc_html_e( 'Already used', 'evoucherwp-woocommerce' );
								} elseif ( $expired ) {
									esc_html_e( 'Expired', 'evoucherwp-woocommerce' );
								}
								?>

								<?php
							elseif ( 'voucher-expiry' === $column_id ) :
								if ( $voucher->expiry > 0 ) :
									?>
								<time datetime="<?php echo esc_attr( date( 'Y-m-d', $voucher->expiry ) ); ?>" title="<?php echo esc_attr( $voucher->expiry ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), $voucher->expiry ) ); ?></time>
									<?php
								else :
									?>
									<span><?php esc_html_e( 'Never expires', 'evoucherwp-woocommerce' ); ?></span>
							<?php endif; ?>

							<?php elseif ( 'voucher-code' === $column_id ) : ?>
								<?php echo esc_html( $voucher->prefix . $voucher->guid . $voucher->suffix ); ?>

							<?php elseif ( 'voucher-actions' === $column_id ) : ?>
								<?php
									$actions = array(
										'view'   => array(
											'url'  => $voucher->get_download_url(),
											'name' => __( 'View', 'evoucherwp-woocommerce' ),
										),
										'change' => array(
											'url'  => wc_get_endpoint_url( 'change-voucher', $voucher->id ),
											'name' => __( 'Change', 'evoucherwp-woocommerce' ),
										),
									);

									if ( ! $voucher->can_change() || 'yes' !== $can_change_voucher ) {
										unset( $actions['change'] );
									}
									$actions = apply_filters( 'evoucherwp_my_account_my_vouchers_actions', $actions, $voucher );
									if ( ! empty( $actions ) ) {
										foreach ( $actions as $key => $voucher_action ) {
											if ( 'valid' === $voucher->is_valid() ) {
												echo '<a href="' . esc_url( $voucher_action['url'] ) . '" class="button ' . sanitize_html_class( $key ) . '">' . esc_html( $voucher_action['name'] ) . '</a>';
											}
										}
									}
									?>
							<?php endif; ?>
						</td>
					<?php endforeach; ?>
					<td class="voucher-live" data-title="<?php echo esc_attr( __( 'LIVE', 'evoucherwp-woocommerce' ) ); ?>">
							<input type="checkbox" name="live" value="yes" <?php checked( $voucher->live, 'yes' ); ?> data-voucher-id="<?php echo absint( $voucher->id ); ?>"/>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php do_action( 'evoucherwp_before_account_vouchers_pagination' ); ?>

	<?php if ( 1 < $max_num_pages ) : ?>
		<div class="woocommerce-Pagination">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'vouchers', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'evoucherwp-woocommerce' ); ?></a>
			<?php endif; ?>

			<?php if ( absint( $max_num_pages ) !== $current_page ) : ?>
				<a class="woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'vouchers', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'evoucherwp-woocommerce' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>
	<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
		<?php esc_html_e( 'No voucher found.', 'evoucherwp-woocommerce' ); ?>
	</div>
<?php endif; ?>

<?php do_action( 'evoucherwp_after_account_vouchers', $has_vouchers ); ?>
