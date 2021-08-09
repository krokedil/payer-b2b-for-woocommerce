<?php
/**
 * Create onboarding session request class
 *
 * @package Payer_B2B/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Create onboarding session request class
 */
class PB2B_Request_Create_Onboarding extends PB2B_Request {
	/**
	 * Makes the request.
	 *
	 * @return array
	 */
	public function request() {
		$request_url  = $this->base_url . '/api/v1/onboard';
		$request_args = stripslashes_deep(apply_filters( 'payer_create_onboarding_args', $this->get_request_args() ));
		error_log( var_export( $request_args, true ) );
		$response     = wp_remote_request( $request_url, $request_args );
		$code         = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		$reference         = ! is_wp_error( $formated_response ) && isset( $formated_response['referenceId'] ) ? $formated_response['referenceId'] : null;

		// Log the request.
		$log = PB2B_Logger::format_log( $reference, 'POST', 'Payer create onboarding session', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		PB2B_Logger::log( $log );

		return $formated_response;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @return array
	 */
	public function get_request_args() {
		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body() ),
			'timeout' => apply_filters( 'pb2b_request_timeout', 10 ),
		);
	}

	/**
	 * Gets the request body.
	 *
	 * @return array
	 */
	public function get_body() {
		return array(
			"callbackUrl"  => get_home_url() . '/wc-api/PB2B_WC_Onboarding/',
			"countryCode"  => "SE",
			"currencyCode" => get_woocommerce_currency(),
			"languageCode" => "sv",
			"buyer"        => array(
				"firstName"   => WC()->customer->get_billing_first_name(),
				"lastName"    => WC()->customer->get_billing_last_name(),
				"email"       => WC()->customer->get_billing_email(),
				"phoneNumber" => WC()->customer->get_billing_phone(),
			),
		);
	}
}
