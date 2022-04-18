<?php
/**
 * EVoucherWP Woocommerce Template Functions
 *
 * Functions for the templating system.
 *
 * File based in:
 *
 * @author   Jose A. Salim
 * @package  EVoucherWP_WooCommerce/Functions
 * @version  1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get other templates passing attributes and including the file.
 *
 * @access public
 * @param string $template_name
 * @param array  $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function evwp_wc_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = evwp_wc_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '1.0' );
		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'evwp_wc_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'evoucherwp_wc_before_template_part', $template_name, $template_path, $located, $args );

	include $located;

	do_action( 'evoucherwp_wc_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Get template part
 *
 * @access public
 * @param mixed  $slug
 * @param string $name (default: '')
 */
function evwp_wc_get_template_part( $slug, $name = '' ) {
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/evoucherwp/slug-name.php
	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", EvoucherWP_WC()->template_path() . "{$slug}-{$name}.php" ) );
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( EvoucherWP_WC()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = EvoucherWP_WC()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/evoucherwp/slug.php
	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", EvoucherWP_WC()->template_path() . "{$slug}.php" ) );
	}

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'evwp_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *      yourtheme       /   $template_path  /   $template_name
 *      yourtheme       /   $template_name
 *      $default_path   /   $template_name
 *
 * @access public
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function evwp_wc_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	if ( ! $template_path ) {
		$template_path = EvoucherWP_WC()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = EvoucherWP_WC()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template/
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'evoucherwp_wc_locate_template', $template, $template_name, $template_path );
}

/**
 * Like evwp_wc_get_template, but returns the HTML instead of outputting.
 *
 * @see evwp_wc_get_template
 * @since 1.0.3
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function evwp_wc_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	evwp_wc_get_template( $template_name, $args, $template_path, $default_path );
	return ob_get_clean();
}


if ( ! function_exists( 'evwp_voucher_product_image' ) ) {
	/**
	 * Output the product image before the single voucher summary.
	 */
	function evwp_voucher_product_image( $image, $voucher ) {

		$product_id = $voucher->product_id;
		if ( ! empty( $product_id ) ) {
			$product = wc_get_product( $product_id );
			$image   = $product->get_image( 'woocommerce_thumbnail', array(), false );
			if ( $image ) {
				return $image;
			}
		}
		return $image;
	}
}


if ( ! function_exists( 'evoucherwp_template_product_title' ) ) {

	/**
	 * Replaces voucher title for product title in template
	 */
	function evoucherwp_template_product_title( $title ) {
		global $voucher;

		$product_id = $voucher->product_id;
		if ( ! empty( $product_id ) ) {
			$product = wc_get_product( $product_id );
			return '<h1 itemprop="name" class="voucher-title entry-title">' . esc_html( $product->get_title() ) . '</h1>';
		}
		return $title;
	}
}

if ( ! function_exists( 'evoucherwp_product_content_after_voucher_content' ) ) {

	/**
	 * Replace voucher's content by product's content in template
	 */
	function evoucherwp_product_content_after_voucher_content() {
		global $voucher;

		$product_id = $voucher->product_id;
		if ( ! empty( $product_id ) ) {
			$product = wc_get_product( $product_id );

			// Allow themes/plugins to modify the content before display
			$content = apply_filters( 'evoucherwp_wc_product_content', $product->get_description() );

			// Prepare to print content: pass through the_content filter
			$content = apply_filters( 'the_content', $content );
			echo wp_kses_post( $content );
		}
	}
}

if ( ! function_exists( 'evoucherwp_wc_product_variation' ) ) {

	/**
	 * Add product variation data if exist.
	 */
	function evoucherwp_wc_product_variation( $voucher_meta, $voucher ) {

		if ( is_a( $voucher, 'EVWP_WC_Voucher' ) ) {
			$wc_voucher = $voucher;
		} else {
			$wc_voucher = new EVWP_WC_Voucher( $voucher );
		}

		if ( ! $wc_voucher->get_product_id() ) {
			return $voucher_meta;
		}

		$product_id = $wc_voucher->get_product_id();
		if ( ! empty( $product_id ) ) {
			$product = wc_get_product( $product_id );
			if ( 'variation' === $product->get_type() ) {
				$variations = $product->get_variation_attributes();
				$value      = '';
				foreach ( $variations as $name => $value ) {
					$label  = wc_attribute_label( str_replace( 'attribute_', '', $name ), $product );
					$value .= '<p class="variation-details">' . esc_html( $label ) . ': ' . esc_html( $value ) . '</p>';
				}

				$voucher_meta['details'] = array(
					'title' => __( 'Details: ', 'evoucherwp-woocommerce' ),
					'value' => $value,
				);
			}
		}

		return $voucher_meta;
	}
}

if ( ! function_exists( 'evoucherwp_wc_customer_data' ) ) {

	/**
	 * Display voucher customer data
	 *
	 * @param  array           $voucher_meta voucher meta
	 * @param  EVWP_WC_Voucher $voucher      voucher
	 */
	function evoucherwp_wc_customer_data( $voucher_meta, $voucher ) {

		$order_id = $voucher->order_id;
		if ( ! $order_id ) {
			return $voucher_meta;
		}

		$order     = wc_get_order( $order_id );
		$gift_name = update_post_meta( $order_id, '_evoucherwp_gift_name', true );
		if ( ! empty( $gift_name ) ) {
			$voucher_meta['owner'] = array(
				'label' => __( "Voucher owner's: ", 'evoucherwp-woocommerce' ),
				'value' => $gift_name,
			);
			return $voucher_meta;
		}

		$owner = $order->get_billing_first_name();
		if ( empty( $owner ) ) {
			$user = $order->get_user();
			if ( $user ) {
				$owner = $user->display_name;
			}
		} else {
			$owner .= ' ' . $order->get_billing_last_name();
		}

		if ( ! empty( $owner ) ) {
			$voucher_meta['owner'] = array(
				'label' => __( "Voucher owner's: ", 'evoucherwp-woocommerce' ),
				'value' => $owner,
			);
		}

		$country_code = $order->get_billing_country();
		if ( ! empty( $country_code ) ) {
			$countries               = WC()->countries->get_countries();
			$voucher_meta['country'] = array(
				'label' => __( 'Country: ', 'evoucherwp-woocommerce' ),
				'value' => $countries[ $country_code ],
			);
		}

		return $voucher_meta;
	}
}

if ( ! function_exists( 'evwp_wc_voucher_qrcode' ) ) {

	/**
	 * Generates QR Code for vouchers
	 */
	function evwp_wc_voucher_qrcode( $voucher ) {
		if ( ! empty( $voucher ) ) {
			include_once 'phpqrcode/qrlib.php';

			$endpoint_url = EVWP_WC_Validate_Voucher_EndPoint::get_endpoint_url( home_url() );
			$qrcode       = generate_svg_qrcode( trailingslashit( $endpoint_url ) . $voucher->guid, QR_ECLEVEL_Q );
			// TODO: escape wp_kses
			// https://github.com/cferdinandi/gmt-wordpress-svg/blob/master/wordpress-svg.php
			// https://stackoverflow.com/questions/53253395/phpcs-svg-escape-function-wordpress
			// https://wordpress.stackexchange.com/questions/312625/escaping-svg-with-kses
			echo $qrcode;
		}
	}
}

/**
 * Display QRCode as png image (base64 encoded)
 *
 * @param  EVWP_WC_Voucher $voucher voucher
 */
function evwp_wc_voucher_qrcode_image( $voucher ) {
	include_once 'phpqrcode/qrlib.php';
	$upload_dir   = wp_upload_dir();
	$endpoint_url = EVWP_WC_Validate_Voucher_EndPoint::get_endpoint_url( home_url() );
	$filename     = sprintf( 'voucher_%d.png', $voucher->get_order_id() );

	$vouchers_dir = trailingslashit( $upload_dir['basedir'] ) . 'evwp_vouchers';
	if ( ! file_exists( $vouchers_dir ) ) {
		wp_mkdir_p( $vouchers_dir );
	}

	$voucher_token = EVWP_WC_JWT::issue_token(
		$voucher->expiry,
		apply_filters(
			'evwp_wc_jwt_data',
			array(
				'code' => $voucher->guid,
			),
			$voucher
		)
	);
	$file_path     = trailingslashit( $vouchers_dir ) . $filename;
	QRcode::png( trailingslashit( $endpoint_url ) . $voucher_token, $file_path, QR_ECLEVEL_L, 3 );

	global $wp_filesystem;
	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();
	}

	$qr_image = $wp_filesystem->get_contents( $file_path );
	if ( ! $qr_image ) {
		return __( 'Failed to create read QRCode', 'evoucherwp-woocommerce' );
	}

	unlink( $file_path );

	?>
	<div class="qrcode-image">
		<img src="data:image/png;base64,<?php echo base64_encode( $qr_image ); ?>"/>
	</div>
	<?php
}

/**
 * Generates SVG QRCode
 *
 * @param  string  $text         text
 * @param  integer $level        QRCode level
 * @param  integer $size         size
 * @param  integer $margin       margin
 * @param  boolean $saveandprint should save and print
 * @param  hex     $back_color   background color
 * @param  hex     $fore_color   foreground color
 * @return string                SVG QRCode
 */
function generate_svg_qrcode( $text, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint = false, $back_color = 0xFFFFFF, $fore_color = 0x000000 ) {

	$enc = QRencode::factory( $level, $size, $margin, $back_color, $fore_color );
	ob_start();
	$tab = $enc->encode( $text );
	$err = ob_get_contents();
	ob_end_clean();

	if ( '' !== $err ) {
		QRtools::log( false, $err );
	}

	$max_size = (int) ( QR_PNG_MAXIMUM_SIZE / ( count( $tab ) + 2 * $enc->margin ) );

	$vect = QRvect::vectSVG( $tab, min( max( 1, $enc->size ), $max_size ), $enc->margin, $enc->back_color, $enc->fore_color );

	return $vect;
}

/**
 * Get My Account > Vouchers columns.
 *
 * @since 1.0.0
 * @return array
 */
function evwp_get_account_vouchers_columns() {
	$columns = apply_filters(
		'evoucherwp_account_vouchers_columns',
		array(
			'order-number'    => __( 'Order', 'evoucherwp-woocommerce' ),
			'order-date'      => __( 'Date', 'evoucherwp-woocommerce' ),
			'voucher-expiry'  => __( 'Expiry', 'evoucherwp-woocommerce' ),
			'voucher-code'    => __( 'PIN', 'evoucherwp-woocommerce' ),
			'voucher-status'  => __( 'Status', 'evoucherwp-woocommerce' ),
			'voucher-actions' => '&nbsp;',
		)
	);
	return $columns;
}

if ( ! function_exists( 'evwp_wc_get_email_vouchers' ) ) {

	/**
	 * Get vouchers email template
	 *
	 * @param  array $vouchers vouchers
	 * @param  array $args    additional arguments
	 */
	function evwp_wc_get_email_vouchers( $vouchers, $args = array() ) {
		ob_start();

		$defaults = array(
			'show_image' => false,
			'image_size' => array( 32, 32 ),
			'plain_text' => false,
		);

		$args     = wp_parse_args( $args, $defaults );
		$template = $args['plain_text'] ? 'emails/plain/email-vouchers.php' : 'emails/email-vouchers.php';

		evwp_wc_get_template(
			$template,
			apply_filters(
				'evwp_wc_email_vouchers_args',
				array(
					'vouchers'   => $vouchers,
					'show_image' => $args['show_image'],
					'image_size' => $args['image_size'],
					'plain_text' => $args['plain_text'],
				)
			)
		);

		return apply_filters( 'evwp_wc_email_vouchers_table', ob_get_clean(), $vouchers );
	}
}


if ( ! function_exists( 'evwp_wc_single_voucher' ) ) {
	/**
	 * Template for single voucher
	 *
	 * @param  EVWP_WC_Voucher $voucher voucher
	 */
	function evwp_wc_single_voucher( $voucher = '' ) {
		ob_start();
		evwp_get_template( 'single-voucher/voucher-image.php', array( 'voucher' => $voucher ) );
		evwp_get_template( 'single-voucher/voucher-meta.php', array( 'voucher' => $voucher ) );

		return ob_get_clean();
	}
}

if ( ! function_exists( 'evwp_wc_voucher_meta' ) ) {
	/**
	 * Display voucher meta
	 *
	 * @param  EVWP_WC_Voucher $voucher voucher
	 */
	function evwp_wc_voucher_meta( $voucher ) {
		$code   = $voucher->get_voucher_code();
		$expiry = $voucher->expiry;
		if ( empty( $expiry ) && 0 !== $expiry ) {
			$days_to_expiry = intval( get_option( 'evoucherwp_expiry', 0 ) );
			$startdate      = floatval( $voucher->startdate );
			$expiry         = $startdate + ( $days_to_expiry * 24 * 60 * 60 );
		}

		$expiry = $expiry > 0 ? date_i18n( get_option( 'date_format' ), $expiry ) : false;

		$voucher_meta['pin'] = array(
			'label' => __( 'PIN: ', 'evoucherwp-woocommerce' ),
			'value' => $code,
		);

		if ( $expiry ) {
			$voucher_meta['expiry'] = array(
				'label' => __( 'Valid until: ', 'evoucherwp-woocommerce' ),
				'value' => $expiry,
			);
		}

		$voucher_meta = apply_filters( 'evoucherwp_voucher_meta', $voucher_meta, $voucher );

		foreach ( $voucher_meta as $key => $value ) {
			if ( isset( $value['label'] ) && isset( $value['value'] ) ) {
				/* translators: %1$s: voucher meta label, %2$s: voucher meta value */
				echo wp_kses_post( sprintf( __( '<p><strong>%1$s</strong>%2$s</p>', 'evoucherwp-woocommerce' ), $value['label'], esc_html( $value['value'] ) ) );
			}
		}
	}
}

if ( ! function_exists( 'evwp_wc_voucher_content' ) ) {

	/**
	 * Get voucher content
	 *
	 * @param  EVWP_WC_Voucher $voucher voucher
	 * @param  WC_Product      $product product
	 */
	function evwp_wc_voucher_content( $voucher, $product ) {
		$content = apply_filters( 'evwp_wc_voucher_content_filter', $product->get_description(), $voucher, $product );
		if ( $content ) {
			echo wp_kses_post( wpautop( wptexturize( $content ) ) );
		}
	}
}
