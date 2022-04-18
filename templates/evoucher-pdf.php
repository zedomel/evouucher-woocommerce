<?php
/**
 * Voucher PDF Template
 *
 * @author      Jose A. Salim
 * @package     EVoucherWP_WC/Templates
 * @version     1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


$header_img       = get_option( 'evoucherwp_header_image', '' );
$header_title     = get_option( 'evoucherwp_header_title', '' );
$footer_content   = get_option( 'evoucherwp_footer_content', '' );
$voucher_per_page = get_option( 'evoucherwp_wc_voucher_per_page', 2 );

?>
<style>

@page{
	margin: 0px;
}

main{
	margin: 4px;
}

header{
	position: fixed;
	top: 0cm;
	left: 0cm;
	right: 0cm;
	height: 2.5cm;
}

footer{
	position: fixed;
	bottom: 0cm;
	left: 0cm;
	right: 0cm;
	height: 4.5cm;
	background-color: #600724;
}

body,p,h1,h2,h3,h4,h5,li,span{
	font-family: Arial,'Helvetica Neue',Helvetica,sans-serif !important;
	margin: 16px 0 16px 0;
}

h1,h2,h3{
	color: #f6c65b;
	margin: 12px 0 12px 0;
}

h4,h5{
	color: #600724;
	font-size: 16px;
	padding: 0;
	margin: 10px 0 10px 0;
}

#container{
	text-align: left;
	margin: 0 20px;
}

.header-image{
	text-align: center;
}

.header-image img{
	max-width: 35%;
}

.header-title{
	text-align: center;
}

.voucher-title{
	color: #600724;
	text-align: center;
	text-transform: uppercase;
	font-size: 1vw;
}

.voucher{
	background-color: #fff;
	border: 4px solid #600724;
	border-radius: 7px;
	border-style: dashed;
	width: 90%;
	margin: auto;
	padding: 5px 10px 5px 10px;
	margin-bottom: 10px;
}

.voucher p, .voucher strong{
	color: #2b2b2b;
	margin: 1px 0 1px 0;
	padding: 1px 0 1px 0;
}

.voucher table{
	width: 100%;
	margin: 0;
	padding: 0;
}

.voucher .summary{
	margin: 0;
	padding: 0;
}

.voucher .voucher-meta{
	width: 75%;
}
.voucher .qrcode{
	width: 25%;
	text-align: left;
}

.voucher-footer{
	background-color: #600724;
	padding: 15px;
	color: #fff;
	text-align: center;
	margin: 0;
}

.voucher-socials{
	text-align: center;
	display: inline-block;
}

.voucher-socials img{
	max-width: 24px;
}

.voucher-socials img,
.voucher-socials p,
.voucher-socials a {
	color: #fff;
	padding: 0 5px;
	margin: 2px;
}

.qrcode-image {
	width: 100%;
}

.qrcode-image {
	max-width: 300px;
}

<?php do_action( 'evwp_wc_voucher_pdf_styles' ); ?>

</style>

<body>

<div class="voucher-header">

	<?php do_action( 'evoucherwp_voucher_header_before_image' ); ?>

	<?php if ( ! empty( $header_img ) ) : ?>

		<div class="header-image">
		<?php echo sprintf( '<img src="%s"/>', esc_url( $header_img ) ); ?>
		</div>

	<?php endif; ?>

	<?php do_action( 'evoucherwp_voucher_header_before_title' ); ?>

	<?php if ( ! empty( $header_title ) ) : ?>
		<div class="header-title">
		<?php echo wp_kses_post( apply_filters( 'evwp_wc_voucher_header_title', sprintf( '<h1>%s</h1>', esc_html( $header_title ) ) ) ); ?>
		</div>
	<?php endif; ?>
</div>

<footer>
	<div class="voucher-footer">
		<?php do_action( 'evwp_wc_voucher_before_footer' ); ?>
		<?php if ( ! empty( $footer_content ) ) : ?>
		<div class="footer-content">
			<?php echo wp_kses_post( $footer_content ); ?>
		</div>
		<?php endif; ?>

		<?php do_action( 'evwp_wc_voucher_after_footer' ); ?>
	</div>
</footer>

<main>
	<div id="container">
	<?php
	if ( ! empty( $vouchers ) ) :
		$products = [];
		foreach ( $vouchers as $voucher ) :
			if ( ! in_array( $voucher->get_product_id(), $products, true ) ) :
				$product = $voucher->get_product();
				?>
			<div class="voucher-content">
				<?php
				do_action( 'evwp_wc_voucher_content', $voucher, $product );
				?>
			</div>
				<?php
			endif;
			$products[] = $product->get_id();
		endforeach;
		endif;
	?>

	<div class="vouchers">
		<?php
		if ( ! empty( $vouchers ) ) :
			$count = 0;
			foreach ( $vouchers as $voucher ) :
				$product = $voucher->get_product();

				if ( 1 < $count && 0 === ( $count % $voucher_per_page ) ) :
					?>
			<div style="page-break-after: always;"></div>
					<?php endif; ?>

			<div class="voucher">
			<h2 itemprop="name" class="voucher-title entry-title"><?php echo esc_html( apply_filters( 'evwp_wc_voucher_title', $product->get_title(), $voucher ) ); ?></h2>
			<div class="summary entry-summary">
				<table>
				<tr>
					<td class="voucher-meta" style="width:70%;">
					<?php do_action( 'evwp_wc_voucher_meta', $voucher ); ?>
					</td>
					<td class="qrcode" style="width:30%;">
					<?php do_action( 'evwp_wc_voucher_qrcode', $voucher ); ?>
					</td>
				</tr>
				</table>
			</div>
			</div>
				<?php $count++; ?>
				<?php
			endforeach;
		endif;
		?>
	</div>
	</div>
</main>
</body>
