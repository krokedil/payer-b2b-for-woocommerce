<?php
/**
 * Main request class
 *
 * @package Payer_B2B/Classes/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main request class
 */
class PB2B_Request {
	/**
	 * Class constructor.
	 *
	 * @param int  $order_id The WooCommerce order id.
	 * @param bool $auth If the request is a auth or not.
	 */
	public function __construct( $order_id = null, $auth = false ) {
		$this->order_id = $order_id;
		$this->auth     = $auth;
		$this->set_environment_variables();
	}

	/**
	 * Returns headers.
	 *
	 * @return array
	 */
	public function get_headers() {
		return array(
			'Authorization' => $this->calculate_auth(),
			'Content-Type'  => $this->content_type,
		);
	}

	/**
	 * Sets the environment.
	 *
	 * @return void
	 */
	public function set_environment_variables() {
		$this->payer_settings = get_option( 'woocommerce_payer_b2b_v1_invoice_settings' );
		$this->agent_id       = $this->payer_settings['agent_id'];
		$this->password       = $this->payer_settings['api_key'];
		$this->testmode       = $this->payer_settings['testmode'];
		$this->customer_type  = $this->payer_settings['allowed_customer_types'];
		$this->content_type   = ( $this->auth ) ? 'application/x-www-form-urlencoded' : 'application/json';
		$this->base_url       = ( 'yes' === $this->testmode ) ? PAYER_B2B_TEST_ENV : PAYER_B2B_LIVE_ENV;
	}

	/**
	 * Checks response for any error.
	 *
	 * @param object $response The response.
	 * @param array  $request_args The request args.
	 * @param string $request_url The request URL.
	 * @return object|array
	 */
	public function process_response( $response, $request_args = array(), $request_url = '' ) {
		// If response is already WP_Error, then return that.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Check the status code.
		if ( wp_remote_retrieve_response_code( $response ) < 200 || wp_remote_retrieve_response_code( $response ) > 299 ) {
			$data          = 'URL: ' . $request_url . ' - ' . wp_json_encode( $request_args );
			$error_message = '';
			// Get the error messages.
			if ( null !== json_decode( $response['body'], true ) ) {
				$error         = json_decode( $response['body'], true );
				$error_message = $error_message . ' ' . $error['message'];
			}
			return new WP_Error( wp_remote_retrieve_response_code( $response ), $response['response']['message'] . $error_message, $data );
		}
		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	/**
	 * Calculates the auth needed for the different requests.
	 *
	 * @return string
	 */
	public function calculate_auth() {
		if ( $this->auth ) {
			return 'Basic ' . base64_encode( $this->agent_id . ':' . $this->password );
		} else {
			$token = payer_b2b_maybe_create_token( $this->order_id );
			if ( is_wp_error( $token ) ) {
				wp_die( esc_html( $token ) );
			}
			return 'Bearer ' . $token;
		}
	}

	/**
	 * Gets the language code for the different requests.
	 *
	 * @return string
	 */
	public function lang_code() {
		$iso_code        = explode( '_', get_locale() );
		$supported_codes = array( 'aa', 'ab', 'ae', 'af', 'ak', 'am', 'an', 'ar', 'as', 'av', 'ay', 'az', 'ba', 'be', 'bg', 'bh', 'bm', 'bi', 'bn', 'bo', 'br', 'bs', 'ca', 'ce', 'ch', 'co', 'cr', 'cs', 'cu', 'cv', 'cy', 'da', 'de', 'dv', 'dz', 'ee', 'el', 'en', 'eo', 'es', 'et', 'eu', 'fa', 'ff', 'fi', 'fj', 'fo', 'fr', 'fy', 'ga', 'gd', 'gl', 'gn', 'gu', 'gv', 'ha', 'he', 'hi', 'ho', 'hr', 'ht', 'hu', 'hy', 'hz', 'ia', 'id', 'ie', 'ig', 'ii', 'ik', 'io', 'is', 'it', 'iu', 'ja', 'jv', 'ka', 'kg', 'ki', 'kj', 'kk', 'kl', 'km', 'kn', 'ko', 'kr', 'ks', 'ku', 'kv', 'kw', 'ky', 'la', 'lb', 'lg', 'li', 'ln', 'lo', 'lt', 'lu', 'lv', 'mg', 'mh', 'mi', 'mk', 'ml', 'mn', 'mr', 'ms', 'mt', 'my', 'na', 'nb', 'nd', 'ne', 'ng', 'nl', 'nn', 'no', 'nr', 'nv', 'ny', 'oc', 'oj', 'om', 'or', 'os', 'pa', 'pi', 'pl', 'ps', 'pt', 'qu', 'rm', 'rn', 'ro', 'ru', 'rw', 'sa', 'sc', 'sd', 'se', 'sg', 'si', 'sk', 'sl', 'sm', 'sn', 'so', 'sq', 'sr', 'ss', 'st', 'su', 'sv', 'sw', 'ta', 'te', 'tg', 'th', 'ti', 'tk', 'tl', 'tn', 'to', 'tr', 'ts', 'tt', 'tw', 'ty', 'ug', 'uk', 'ur', 'uz', 've', 'vi', 'vo', 'wa', 'wo', 'xh', 'yi', 'yo', 'za', 'zh', 'zu' );
		if ( in_array( $iso_code[0], $supported_codes, true ) ) {
			$lang_code = $iso_code[0];
		} else {
			$lang_code = 'en';
		}
		return apply_filters( 'wc_payer_lang_code', $lang_code );
	}
}
