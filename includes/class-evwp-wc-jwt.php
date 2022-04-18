<?php
/**
 * JWT Generator
 *
 * @package EVoucherWP_WooCommerce/Classes
 * @version 1.0.3
 */

defined( 'ABSPATH' ) || exit;


/**
 * EVWP_WC_JWT Class
 */
class EVWP_WC_JWT {


	/**
	 * JTW regex pattern
	 *
	 * @var string
	 */
	public static $token_pattern = '([A-Za-z0-9-_=]+\.[A-Za-z0-9-_=]+\.?[A-Za-z0-9-_.+/=]*)';

	/**
	 * When checking nbf, iat or expiration times,
	 * we want to provide some extra leeway time to
	 * account for clock skew.
	 *
	 * @var  int
	 */
	public static $leeway = 0;


	/**
	 * Get JWT token for REST access
	 *
	 * @param  int   $exp   expiration timestamp (unix timestamp)
	 * @param array $args token payload
	 * @return string                   JWT token
	 */
	public static function issue_token( $exp, $args = array() ) {
		$payload = array_replace(
			array(
				'exp' => $exp,
				'nbf' => time(),
				'iat' => time(),
			),
			$args
		);

		return self::encode( $payload );
	}

	/**
	 * Decodes a JWT string into a PHP object.
	 *
	 * @param string       $jwt             The JWT
	 * @param string|array $secret_key      The key, or map of keys.
	 *
	 * @return object The JWT's payload as a PHP object or false with error
	 */
	public static function decode( $jwt, $secret_key = '' ) {
		if ( empty( $jwt ) ) {
			return false;
		}

		if ( empty( $secret_key ) ) {
			$secret_key = self::get_secret_key();
		}

		$timestamp = time();
		$tks       = explode( '.', $jwt );
		if ( count( $tks ) != 3 ) {
			return false;
		}

		list($headb64, $bodyb64, $cryptob64) = $tks;
		if ( null === ( $header = json_decode( self::url_safe_base64_decode( $headb64 ) ) ) ) {
			return false;
		}
		if ( null === $payload = json_decode( self::url_safe_base64_decode( $bodyb64 ) ) ) {
			return false;
		}
		if ( false === ( $sig = self::url_safe_base64_decode( $cryptob64 ) ) ) {
			return false;
		}

		if ( empty( $header->alg ) ) {
			return false;
		}

		// Check the signature
		if ( ! self::verify( "$headb64.$bodyb64", $sig, $secret_key, $header->alg ) ) {
			return false;
		}

		// Check if the nbf if it is defined. This is the time that the
		// token can actually be used. If it's not yet that time, abort.
		if ( isset( $payload->nbf ) && $payload->nbf > ( $timestamp + self::$leeway ) ) {
			return false;
		}

		// Check that this token has been created before 'now'. This prevents
		// using tokens that have been created for later use (and haven't
		// correctly used the nbf claim).
		if ( isset( $payload->iat ) && $payload->iat > ( $timestamp + self::$leeway ) ) {
			return false;
		}

		// Check if this token has expired.
		if ( isset( $payload->exp ) && ( $timestamp - self::$leeway ) >= $payload->exp ) {
			return false;
		}

		return $payload;
	}

	/**
	 * Converts and signs a PHP object or array into a JWT string.
	 *
	 * @param object|array $payload       PHP object or array
	 * @param string       $secret_key   The secret key
	 *
	 * @return string A signed JWT
	 */
	public static function encode( $payload, $secret_key = '' ) {
		if ( empty( $secret_key ) ) {
			$secret_key = self::get_secret_key();
		}

		// Create token header as a JSON string
		$header = wp_json_encode(
			[
				'typ' => 'JWT',
				'alg' => 'HS256',
			]
		);

		$segments      = [];
		$segments[]    = self::url_safe_base64_encode( $header );
		$segments[]    = self::url_safe_base64_encode( wp_json_encode( $payload ) );
		$signing_input = implode( '.', $segments );

		$signature  = self::sign( $signing_input, $secret_key, 'HS256' );
		$segments[] = self::url_safe_base64_encode( $signature );

		return implode( '.', $segments );
	}

	/**
	 * Verify a signature with the message, key and method. Not all methods
	 * are symmetric, so we must have a separate verify and sign method.
	 *
	 * @param string          $msg        The original message (header and body)
	 * @param string          $signature  The original signature
	 * @param string|resource $key        For HS*, a string key works. for RS*, must be a resource of an openssl public key
	 * @param string          $alg        The algorithm
	 *
	 * @return bool
	 *
	 * @throws DomainException Invalid Algorithm or OpenSSL failure
	 */
	private static function verify( $msg, $signature, $key ) {
		$hash = hash_hmac( 'SHA256', $msg, $key, true );
		return hash_equals( $signature, $hash );
	}

	/**
	 * Sign a string with a given key and algorithm.
	 *
	 * @param string          $msg    The message to sign
	 * @param string|resource $key    The secret key
	 *
	 * @return string An encrypted message
	 *
	 * @throws DomainException Unsupported algorithm was specified
	 */
	public static function sign( $msg, $key ) {
		return hash_hmac( 'SHA256', $msg, $key, true );
	}

	/**
	 * Decode a string with URL-safe Base64.
	 *
	 * @param string $input A Base64 encoded string
	 *
	 * @return string A decoded string
	 */
	public static function url_safe_base64_decode( $input ) {
		$remainder = strlen( $input ) % 4;
		if ( $remainder ) {
			$padlen = 4 - $remainder;
			$input .= str_repeat( '=', $padlen );
		}
		return base64_decode( strtr( $input, '-_', '+/' ) );
	}
	/**
	 * Encode a string with URL-safe Base64.
	 *
	 * @param string $input The string you want encoded
	 *
	 * @return string The base64 encode of what you passed in
	 */
	public static function url_safe_base64_encode( $input ) {
		return str_replace( '=', '', strtr( base64_encode( $input ), '+/', '-_' ) );
	}

	/**
	 * Generate a random value
	 *
	 * @param  integer $min mininum
	 * @param  integer $max maximum
	 * @return integer       random value
	 */
	private static function rand( $min = 0, $max = 0 ) {
		global $rnd_value;

		// Reset $rnd_value after 14 uses
		// 32(md5) + 40(sha1) + 40(sha1) / 8 = 14 random numbers from $rnd_value
		if ( strlen( $rnd_value ) < 8 ) {
			if ( defined( 'WP_SETUP_CONFIG' ) ) {
				static $seed = '';
			} else {
				$seed = get_transient( 'random_seed' );
			}
			$rnd_value  = md5( uniqid( microtime() . mt_rand(), true ) . $seed );
			$rnd_value .= sha1( $rnd_value );
			$rnd_value .= sha1( $rnd_value . $seed );
			$seed       = md5( $seed . $rnd_value );
			if ( ! defined( 'WP_SETUP_CONFIG' ) ) {
				set_transient( 'random_seed', $seed );
			}
		}

		// Take the first 8 digits for our value
		$value = substr( $rnd_value, 0, 8 );

		// Strip the first eight, leaving the remainder for the next call to wp_rand().
		$rnd_value = substr( $rnd_value, 8 );

		$value = abs( hexdec( $value ) );

		// Some misconfigured 32bit environments (Entropy PHP, for example) truncate integers larger than PHP_INT_MAX to PHP_INT_MAX rather than overflowing them to floats.
		$max_random_number = 3000000000 === 2147483647 ? (float) '4294967295' : 4294967295; // 4294967295 = 0xffffffff

		// Reduce the value to be within the min - max range
		if ( $max != 0 ) {
			$value = $min + ( $max - $min + 1 ) * $value / ( $max_random_number + 1 );
		}

		return abs( intval( $value ) );
	}

	/**
	 * Creates a unique instance ID
	 *
	 * @param  integer $length              length
	 * @param  boolean $special_chars       use special characters
	 * @param  boolean $extra_special_chars use extra special characters
	 * @return string                       UUID
	 */
	public static function generate_secret( $length = 16, $special_chars = true, $extra_special_chars = false ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		if ( $special_chars ) {
			$chars .= '!@#$%^&*()';
		}
		if ( $extra_special_chars ) {
			$chars .= '-_ []{}<>~`+=,.;:/?|';
		}

		$password = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$password .= substr( $chars, self::rand( 0, strlen( $chars ) - 1 ), 1 );
		}

		return $password;
	}

	/**
	 * Get JWT secret key
	 *
	 * @return string secret key
	 */
	public static function get_secret_key() {
		return defined( JWT_SECRET_KEY ) ? JWT_SECRET_KEY : '';
	}
}
