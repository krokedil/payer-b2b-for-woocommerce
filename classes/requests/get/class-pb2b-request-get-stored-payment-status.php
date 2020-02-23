<?php
/**
 * Get stored payment status request class
 *
 * @package Payer_B2B/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get stored payment status request class
 */
class PB2B_Request_Get_Stored_Payment_Status extends PB2B_Request {
	/**
	 * Makes the request.
	 *
	 * @return array
	 */
	public function request() {
		$payer_token  = get_post_meta( $this->order_id, '_payer_token', true );
		$request_url  = $this->base_url . '/api/v2/payments/cards/stored/' . $payer_token . '/status';
		$request_args = apply_filters( 'payer_get_stored_payment_status_args', $this->get_request_args( $this->order_id ), $this->order_id );
		$response     = wp_remote_request( $request_url, $request_args );
		$code         = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		$reference         = null;

		// Log the request.
		$log = PB2B_Logger::format_log( $reference, 'GET', 'Payer get stored payment status', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		PB2B_Logger::log( $log );

		return $formated_response;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return array
	 */
	public function get_request_args( $order_id ) {
		$order = wc_get_order( $order_id );

		return array(
			'headers' => $this->get_headers(),
			'method'  => 'GET',
		);
	}
}
