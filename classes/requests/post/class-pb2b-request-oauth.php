<?php
/**
 * Oauth request class
 *
 * @package Payer_B2B/Classes/Put/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Oauth request class
 */
class PB2B_Request_Oauth extends PB2B_Request {

	/**
	 * Class constructor.
	 *
	 * @param int $order_id WooCommerce order id.
	 */
	public function __construct( $order_id ) {
		// Run parent constructor and set auth to true.
		parent::__construct( $order_id, true );
	}
	/**
	 * Makes the request.
	 *
	 * @return array
	 */
	public function request() {
		$request_url  = $this->base_url . '/oauth2/token';
		$request_args = apply_filters( 'payer_create_oauth_args', $this->get_request_args() );
		$response     = wp_remote_request( $request_url, $request_args );
		$code         = wp_remote_retrieve_response_code( $response );

		// Log the request.
		$log = PB2B_Logger::format_log( '', 'POST', 'Payer create auth token', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		PB2B_Logger::log( $log );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
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
			'body'    => array( 'grant_type' => 'client_credentials' ),
			'timeout' => apply_filters( 'pb2b_request_timeout', 10 ),
		);
	}
}
