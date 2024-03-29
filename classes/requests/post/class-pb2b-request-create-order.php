<?php
/**
 * Create order request class
 *
 * @package Payer_B2B/Classes/Post/Requests
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Create order request class
 */
class PB2B_Request_Create_Order extends PB2B_Request {

	/**
	 * Class constructor.
	 *
	 * @param int   $order_id WooCommerce order id.
	 * @param array $args Arguments for the request.
	 */
	public function __construct( $order_id, $args ) {
		parent::__construct( $order_id );

		$this->args = $args;
	}

	/**
	 * Makes the request.
	 *
	 * @return array
	 */
	public function request() {
		$request_url  = $this->base_url . '/api/v2/orders/with-amount-excluding-vat';
		$request_args = apply_filters( 'payer_create_order_args', $this->get_request_args( $this->order_id ), $this->order_id );
		$response     = wp_remote_request( $request_url, $request_args );
		$code         = wp_remote_retrieve_response_code( $response );

		$formated_response = $this->process_response( $response, $request_args, $request_url );
		$reference         = ! is_wp_error( $formated_response ) && isset( $formated_response['referenceId'] ) ? $formated_response['referenceId'] : null;

		// Log the request.
		$log = PB2B_Logger::format_log( $reference, 'POST', 'Payer create order', $request_args, json_decode( wp_remote_retrieve_body( $response ), true ), $code );
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
		$customer = $this->args['b2b'] || 'B2B' === $this->customer_type ? 'ORGANISATION' : 'PRIVATE';
		$order    = wc_get_order( $order_id );

		$body = array(
			'currencyCode'                          => get_woocommerce_currency(),
			'purchaseChannel'                       => 'ECOMMERCE',
			'referenceId'                           => $order->get_order_number(),
			'description'                           => 'Woo Order',
			'invoiceCustomer'                       => array(
				'customerType' => $customer,
				'regNumber'    => $this->args['pno_value'],
				'address'      => PB2B_Customer_Data::get_customer_billing_data( $order_id ),
			),
			'items'                                 => PB2B_Order_Lines::get_order_items( $order_id ),
			'totalVatAmount'                        => intval( round( $order->get_total_tax() * 100, 2 ) ),
			'totalVatAmountsGroupedByVatPercentage' => PB2B_Order_Lines::get_tax_array( $order ),
			'totalAmountIncludingVat'               => intval( round( $order->get_total() * 100, 2 ) ),
		);

		// Roundoff && totalAmountExcludingVat.
		$total_amount_excluding_vat = 0;
		foreach ( $body['items'] as $key => $value ) {
			$total_amount_excluding_vat += $value['unitPriceExcludingVat'] * $value['quantity'];
		}
		$roundoff                        = $body['totalAmountIncludingVat'] - $total_amount_excluding_vat - $body['totalVatAmount'];
		$body['totalAmountExcludingVat'] = $total_amount_excluding_vat;
		$body['roundOff']                = $roundoff;

		if ( $order->has_shipping_address() ) {
			$body['deliveryCustomer'] = array(
				'customerType' => $customer,
				'regNumber'    => $this->args['pno_value'],
				'address'      => PB2B_Customer_Data::get_customer_shipping_data( $order_id ),
			);
		}
		return $body;
	}
}
