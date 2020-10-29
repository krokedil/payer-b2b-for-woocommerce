<?php
/**
 * Refund card payment request class
 *
 * @package Payer_B2B/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Refund card payment request class
 */
class PB2B_Request_Refund_Card_Payment extends PB2B_Request {
	/**
	 * Makes the request.
	 *
	 * @param float  $amount The refund amount.
	 * @param string $reason The reason for the refund.
	 * @return array
	 */
	public function request( $amount, $reason ) {
		$payer_payment_id = get_post_meta( $this->order_id, '_payer_payment_id', true );
		$request_url      = $this->base_url . '/api/v2/payments/' . $payer_payment_id . '/cards/refund';
		$request_args     = apply_filters( 'payer_refund_card_payment_args', $this->get_request_args( $this->order_id, $amount, $reason ), $this->order_id );
		$response         = wp_remote_request( $request_url, $request_args );
		$code             = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		$reference         = ! is_wp_error( $formated_response ) && isset( $formated_response['referenceId'] ) ? $formated_response['referenceId'] : null;

		// Log the request.
		$log = PB2B_Logger::format_log( $reference, 'POST', 'Payer refund card payment', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
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
		$order = wc_get_order( $order_id );

		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode(
				array(
					'amount'                 => intval( round( $amount * 100 ) ),
					'transactionDescription' => empty( $reason ) ? 'None' : $reason,
				)
			),
			'timeout' => apply_filters( 'pb2b_request_timeout', 10 ),
		);
	}
}
