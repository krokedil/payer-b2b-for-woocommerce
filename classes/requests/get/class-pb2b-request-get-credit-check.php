<?php
/**
 * Make a credit check on the customer.
 *
 * @package Payer_B2B/Classes/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get invoice request class
 */
class PB2B_Request_Credit_Check extends PB2B_Request {
	/**
	 * Makes the request
	 *
	 * @param int $order_id The Order ID.
	 * @return object
	 */
	public function request( $order_id ) {
		$payer_payment_id = isset( $payment_id ) ? $payment_id : get_post_meta( $this->order_id, '_payer_payment_id', true );

		$order = wc_get_order( $order_id );

		$country_code        = $order->get_billing_country();
		$registration_number = get_post_meta( $order->get_id(), PAYER_PNO_DATA_NAME )[0];
		$currency_code       = get_woocommerce_currency();
		$credit_check_amount = intval( ( $order->get_total() + $order->get_total_tax() ) * 100 );

		$request_url       = $this->base_url . "/api/v1/customers/$country_code/$registration_number/credit/validate?creditCheckAmount=$credit_check_amount&currencyCode=$currency_code";
		$request_args      = apply_filters( 'payer_get_credit_args', $this->get_request_args(), $country_code, $registration_number, $currency_code, $credit_check_amount );
		$response          = wp_remote_request( $request_url, $request_args );
		$code              = wp_remote_retrieve_response_code( $response );
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
