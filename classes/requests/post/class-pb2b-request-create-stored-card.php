<?php
/**
 * Create Stored Card request class
 *
 * @package Payer_B2B/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Create Stored Card request class
 */
class PB2B_Request_Create_Stored_Card extends PB2B_Request {
	/**
	 * Makes the request.
	 *
	 * @return array
	 */
	public function request() {
		$request_url  = $this->base_url . '/api/v2/payments/cards/stored?uiVersion=V1';
		$request_args = apply_filters( 'payer_create_stored_card_args', $this->get_request_args( $this->order_id ), $this->order_id );
		$response     = wp_remote_request( $request_url, $request_args );

		$code = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );

		// Log the request.
		$log = PB2B_Logger::format_log( $this->order_id, 'POST', 'Payer create stored card', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
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
		$order = wc_get_order( $order_id );

		return array(
			'backToShopURL'        => $order->get_cancel_order_url_raw(),
			'currencyCode'         => get_woocommerce_currency(),
			'languageCode'         => $this->lang_code(),
			'redirectOnFailURL'    => $order->get_cancel_order_url_raw(),
			'redirectOnSuccessURL' => $order->get_checkout_order_received_url(),
		);
	}
}
