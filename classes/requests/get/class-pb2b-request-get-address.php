<?php
/**
 * Get payment request class
 *
 * @package Payer_B2B/Classes/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get payment request class
 */
class PB2B_Request_Get_Address extends PB2B_Request {
	/**
	 * Makes the request
	 *
	 * @param int $country_code Country code abbreviated.
	 * @param int $pno Personal number.
	 * @param int $postal_code ZIP code.
	 * @return object
	 */
	public function request( $country_code, $pno, $postal_code ) {
		$payer_payment_id = isset( $payment_id ) ? $payment_id : get_post_meta( $this->order_id, '_payer_payment_id', true );
		$request_url      = $this->base_url . "/api/v1/customers/$country_code/$pno/address?zipCode=$postal_code";
		$request_args     = apply_filters( 'payer_get_address_args', $this->get_request_args(), $country_code, $pno, $postal_code );
		$response         = wp_remote_request( $request_url, $request_args );
		$code             = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );

		// Log the request.
		$log = PB2B_Logger::format_log( $payer_payment_id, 'GET', 'Payer get address', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
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
			'method'  => 'GET',
			'timeout' => apply_filters( 'pb2b_request_timeout', 10 ),
		);
	}
}
