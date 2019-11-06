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
class PB2B_Request_Credit_V1_Invoice extends PB2B_Request {
	/**
	 * Makes the request.
	 *
	 * @param float  $amount The refund amount.
	 * @param string $reason The reason for the refund.
	 * @return array
	 */
	public function request( $amount, $reason ) {
		$payer_order_id = get_post_meta( $this->order_id, '_payer_order_id', true );
		$request_url    = $this->base_url . '/api/v1/orders/' . $payer_order_id . '/credit';
		$request_args   = apply_filters( 'payer_credit_v1_invoice_args', $this->get_request_args( $this->order_id, $amount, $reason ) );
		$response       = wp_remote_request( $request_url, $request_args );
		$code           = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		$reference         = ! is_wp_error( $formated_response ) && isset( $formated_response['referenceId'] ) ? $formated_response['referenceId'] : null;

		// Log the request.
		$log = PB2B_Logger::format_log( $reference, 'PUT', 'Payer credit v1 invoice', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
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
			'method'  => 'PUT',
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
		return array(
			'creditAmountCents' => intval( round( $amount * 100 ) ),
			'vatPercentage'     => PB2B_V1_Credit_Data::get_refund_tax_rate( $order_id ),
			'description'       => empty( $reason ) ? 'None' : $reason,
		);
	}
}
