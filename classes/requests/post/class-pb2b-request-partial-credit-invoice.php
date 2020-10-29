<?php
/**
 * Credit order request class.
 *
 * @package Payer_B2B/Classes/Put/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Credit order request class.
 */
class PB2B_Request_Partial_Refund_Credit_Invoice extends PB2B_Request {
	/**
	 * Makes the request.
	 *
	 * @param array $refund_data The refund data.
	 * @return array
	 */
	public function request( $refund_data ) {
		$request_url       = $this->base_url . '/api/v2/orders/invoices/credit/partial';
		$request_args      = apply_filters( 'payer_partial_credit_v2_invoice_args', $this->get_request_args( $this->order_id, $refund_data ), $this->order_id );
		$response          = wp_remote_request( $request_url, $request_args );
		$code              = wp_remote_retrieve_response_code( $response );
		$formated_response = $this->process_response( $response, $request_args, $request_url );
		$reference         = ! is_wp_error( $formated_response ) && isset( $formated_response['referenceId'] ) ? $formated_response['referenceId'] : null;

		// Log the request.
		$log = PB2B_Logger::format_log( $reference, 'POST', 'Payer partial credit v2 invoice', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		PB2B_Logger::log( $log );

		return $formated_response;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @param int   $order_id WooCommerce order id.
	 * @param array $refund_data The refund data.
	 * @return array
	 */
	public function get_request_args( $order_id, $refund_data ) {
		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body( $order_id, $refund_data ) ),
			'timeout' => apply_filters( 'pb2b_request_timeout', 10 ),
		);
	}

	/**
	 * Gets the request body.
	 *
	 * @param int   $order_id WooCommerce order id.
	 * @param array $refund_data The refund data.
	 * @return array
	 */
	public function get_body( $order_id, $refund_data ) {
		$order = wc_get_order( $order_id );

		return array(
			'invoiceNumber' => $order->get_meta( '_payer_invoice_number' ),
			'standalone'    => false,
			'items'         => $refund_data['partial_refund_data'],
		);
	}
}
