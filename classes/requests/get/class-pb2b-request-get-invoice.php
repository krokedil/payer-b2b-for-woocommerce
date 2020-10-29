<?php
/**
 * Get invoice class
 *
 * @package Payer_B2B/Classes/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get invoice request class
 */
class PB2B_Request_Get_Invoice extends PB2B_Request {
	/**
	 * Makes the request
	 *
	 * @param int $invoice_number Invoice number.
	 * @return object
	 */
	public function request( $invoice_number ) {
		$payer_payment_id = isset( $payment_id ) ? $payment_id : get_post_meta( $this->order_id, '_payer_payment_id', true );
		$request_url      = $this->base_url . "/api/v2/orders/invoices/?invoiceNumber=$invoice_number";

		$request_args = apply_filters( 'payer_get_invoice_args', $this->get_request_args(), $invoice_number );
		$response     = wp_remote_request( $request_url, $request_args );
		$code         = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );

		// Log the request.
		$log = PB2B_Logger::format_log( $payer_payment_id, 'GET', 'Payer get invoice', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
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
