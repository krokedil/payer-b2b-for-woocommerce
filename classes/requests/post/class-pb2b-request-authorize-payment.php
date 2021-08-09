<?php
/**
 * Authorize stored card payment request class
 *
 * @package Payer_B2B/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Authorize stored card payment  request class
 */
class PB2B_Request_Authorize_Payment extends PB2B_Request {
	/**
	 * Makes the request.
	 *
	 * @return array
	 */
	public function request() {
		$request_url  = $this->base_url . '/api/v2/payments/cards/stored/authorize';
		$request_args = apply_filters( 'payer_authorize_payment_args', $this->get_request_args( $this->order_id ), $this->order_id );
		$response     = wp_remote_request( $request_url, $request_args );

		$code = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		$reference         = ! is_wp_error( $formated_response ) && isset( $formated_response['paymentId'] ) ? $formated_response['paymentId'] : null;

		// Log the request.
		$log = PB2B_Logger::format_log( $reference, 'POST', 'Payer authorize stored card payment', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
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
		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode( $this->get_body( $order_id ) ),
			'timeout' => apply_filters( 'pb2b_request_timeout', 10 ),
		);
	}

	/**
	 * Gets the request body.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return array
	 */
	public function get_body( $order_id ) {
		$order          = wc_get_order( $order_id );
		$payer_order_id = get_post_meta( $order_id, '_payer_order_id', true );
		$payer_token    = get_post_meta( $order_id, '_payer_token', true );

		return array(
			'amount'                   => intval( round( $order->get_total() * 100 ) ),
			'currencyCode'             => get_woocommerce_currency(),
			'externalPaymentReference' => $order->get_order_number(),
			'orderId'                  => empty( $payer_order_id ) ? null : $payer_order_id,
			'redirectOnFailURL'        => $order->get_cancel_order_url_raw(),
			'redirectOnSuccessURL'     => $order->get_checkout_order_received_url(),
			'token'                    => $payer_token,
		);
	}
}
