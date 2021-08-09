<?php
/**
 * Get event payload request class
 *
 * @package Payer_B2B/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get event payload request class
 */
class PB2B_Request_Get_Event_Payload extends PB2B_Request {
	/**
	 * Makes the request.
	 *
	 * @return array
	 */
	public function request( $token = null ) {
		$request_url  = $this->base_url . '/api/v1/events/' . $token;
		$request_args = apply_filters( 'payer_get_event_payload_args', $this->get_request_args(), $this->order_id );
		$response     = wp_remote_request( $request_url, $request_args );
		$code         = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		$reference         = isset( $formated_response['payload']['paymentId'] ) ? $formated_response['payload']['paymentId'] : null;

		// Log the request.
		$log = PB2B_Logger::format_log( $reference, 'GET', 'Payer get event payload', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
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
