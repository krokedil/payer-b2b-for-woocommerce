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
	 * @param float  $amount The refund amount.
	 * @param string $reason The reason for the refund.
	 * @return array
	 */
	public function request( $amount, $reason ) {
		error_log( 'Partial refund Class' );

		$payer_order_id = get_post_meta( $this->order_id, '_payer_order_id', true );

		$request_url = $this->base_url . '/api/v2/orders/invoices/credit/partial';

		error_log( 'Request URL: ' . $request_url );
		error_log( $payer_order_id );

		// $request_args = apply_filters( 'payer_credit_v1_invoice_args', $this->get_request_args( $this->order_id, $amount, $reason ), $this->order_id );
		$request_args = apply_filters( 'payer_partial_credit_v2_invoice_args', $this->get_request_args( $this->order_id, $amount, $reason ), $this->order_id );
		$response     = wp_remote_request( $request_url, $request_args );
		$code         = wp_remote_retrieve_response_code( $response );

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
	 * @param int    $order_id WooCommerce order id.
	 * @param float  $amount The refund amount.
	 * @param string $reason The reason for the refund.
	 * @return array
	 */
	public function get_request_args( $order_id, $amount, $reason ) {

		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body( $order_id, $amount, $reason ) ),
		);
	}

	/**
	 * Gets the request body.
	 *
	 * @param int    $order_id WooCommerce order id.
	 * @param float  $amount The refund amount.
	 * @param string $reason The reason for the refund.
	 * @return array
	 */
	public function get_body( $order_id, $amount, $reason ) {

		$order = wc_get_order( $order_id );

		$items_array = array();
		if ( 0 != ( PB2B_Credit_Data::get_partial_refund_items( $order_id )['partial_refund_data'] ) ) {
			$items_array['items'] = PB2B_Credit_Data::get_partial_refund_items( $order_id )['partial_refund_data'];
		} else {
			$items_array['items'] = 0;
		}

		$items_array['invoiceNumber'] = $order->get_meta( '_payer_invoice_number' );
		$items_array['standalone']    = false;

		error_log( '--->>>' );
		error_log( 'Body PARTIAL :::' );
		error_log( var_export( $items_array, true ) );
		error_log( '<<<---' );

		return $items_array;
	}
}
