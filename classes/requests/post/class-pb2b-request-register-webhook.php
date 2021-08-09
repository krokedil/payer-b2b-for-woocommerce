<?php
/**
 * Register webhook request class
 *
 * @package Payer_B2B/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register webhook request class
 */
class PB2B_Request_Register_Webhook extends PB2B_Request {
	/**
	 * Makes the request.
	 *
	 * @param string $event_type The event type to subscribe to.
	 * @return array
	 */
	public function request( $event_type ) {

		$request_url  = add_query_arg(
			array(
				'type' => $event_type,
				'url'  => get_home_url() . '/wc-api/PB2B_WC_Notification',
			),
			$this->base_url . '/api/v1/events/callbacks'
		);
		$request_args = apply_filters( 'payer_register_webhook_args', $this->get_request_args(), $this->order_id );
		$response     = wp_remote_request( $request_url, $request_args );
		$code         = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );

		// Log the request.
		$log = PB2B_Logger::format_log( $this->order_id, 'POST', 'Payer register webhook', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
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
			'timeout' => apply_filters( 'pb2b_request_timeout', 10 ),
		);
	}
}
