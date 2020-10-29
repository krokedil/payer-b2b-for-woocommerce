<?php
/**
 * Create v2 invoice request class
 *
 * @package Payer_B2B/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Create v2 invoice request class
 */
class PB2B_Request_Create_V2_Invoice extends PB2B_Request {
	/**
	 * Makes the request.
	 *
	 * @param string $type Type of transaction.
	 *
	 * @return array
	 */
	public function request( $type ) {
		$payer_order_id = get_post_meta( $this->order_id, '_payer_order_id', true );
		$request_url    = $this->base_url . '/api/v2/orders/' . $payer_order_id . '/invoices';
		$request_args   = apply_filters( 'payer_create_invoice_args', $this->get_request_args( $this->order_id, $type ), $this->order_id );
		$response       = wp_remote_request( $request_url, $request_args );
		$code           = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		$reference         = ! is_wp_error( $formated_response ) && isset( $formated_response['referenceId'] ) ? $formated_response['referenceId'] : null;

		// Log the request.
		$log = PB2B_Logger::format_log( $reference, 'POST', 'Payer create v2 invoice', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
		PB2B_Logger::log( $log );

		return $formated_response;
	}

	/**
	 * Gets the request args for the API call.
	 *
	 * @param int    $order_id WooCommerce order id.
	 * @param string $type Type of transaction.
	 * @return array
	 */
	public function get_request_args( $order_id, $type ) {

		return array(
			'headers' => $this->get_headers(),
			'method'  => 'POST',
			'body'    => wp_json_encode(
				array(
					'dueDays'      => 30,
					'deliveryType' => $this->get_invoice_type( $order_id ),
					'type'         => $type,
				)
			),
		);
	}

	/**
	 * Get the invoice type for the invoice.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return string
	 */
	public function get_invoice_type( $order_id ) {
		$order = wc_get_order( $order_id );

		$invoice_type = get_post_meta( $order_id, 'pb2b_invoice_type', true );

		if ( 'payer_b2b_prepaid_invoice' === $order->get_payment_method() && empty( $invoice_type ) ) {
			$options      = get_option( 'woocommerce_payer_b2b_prepaid_invoice_settings' );
			$invoice_type = $options['default_invoice_type'];
		} elseif ( 'payer_b2b_normal_invoice' === $order->get_payment_method() && empty( $invoice_type ) ) {
			$options      = get_option( 'woocommerce_payer_b2b_normal_invoice_settings' );
			$invoice_type = $options['default_invoice_type'];
		}

		if ( empty( $invoice_type ) ) {
			$invoice_type = 'EMAIL';
		}

		return $invoice_type;
	}
}
