<?php
/**
 * Create v1 invoice request class
 *
 * @package Payer_B2B/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Create v1 invoice request class
 */
class PB2B_Request_Create_V1_Invoice extends PB2B_Request {
	/**
	 * Makes the request.
	 *
	 * @return array
	 */
	public function request() {
		$payer_order_id = get_post_meta( $this->order_id, '_payer_order_id', true );
		$request_url    = $this->base_url . '/api/v1/orders/' . $payer_order_id . '/invoices';
		$request_args   = apply_filters( 'payer_create_invoice_args', $this->get_request_args( $this->order_id ), $this->order_id );
		$response       = wp_remote_request( $request_url, $request_args );
		$code           = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		$reference         = ! is_wp_error( $formated_response ) && isset( $formated_response['referenceId'] ) ? $formated_response['referenceId'] : null;

		// Log the request.
		$log = PB2B_Logger::format_log( $reference, 'POST', 'Payer create v1 invoice', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
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
		$email_type = isset( $this->payer_settings['default_invoice_type'] ) ? $this->payer_settings['default_invoice_type'] : 'EMAIL';
		if ( get_post_meta( $order_id, 'pb2b_invoice_type' ) ) {
			$email_type = get_post_meta( $order_id, 'pb2b_invoice_type', true );
		}

		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode(
				array(
					'dueDays'      => 30,
					'deliveryType' => $email_type,
				)
			),
		);
	}
}
