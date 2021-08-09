<?php
/**
 * Gets the customer data for a request.
 *
 * @package Payer_B2B/Classes/Requests/Helpers
 */

/**
 * Class to generate customer data for requests.
 */
class PB2B_Customer_Data {
	/**
	 * Returns the customer billing data.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return array
	 */
	public static function get_customer_billing_data( $order_id ) {
		$order = wc_get_order( $order_id );
		return array(
			'city'           => $order->get_billing_city(),
			'countryCode'    => $order->get_billing_country(),
			'emailAddress'   => $order->get_billing_email(),
			'firstName'      => $order->get_billing_first_name(),
			'lastName'       => $order->get_billing_last_name(),
			'zipCode'        => $order->get_billing_postcode(),
			'streetAddress1' => $order->get_billing_address_1(),
			'phoneNumber'    => $order->get_billing_phone(),
			'companyName'    => $order->get_billing_company(),
			'yourReference'  => '',
		);
	}

	/**
	 * Returns the customer shipping data.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return array
	 */
	public static function get_customer_shipping_data( $order_id ) {
		$order = wc_get_order( $order_id );
		return array(
			'city'           => $order->get_shipping_city(),
			'countryCode'    => $order->get_shipping_country(),
			'emailAddress'   => $order->get_billing_email(),
			'firstName'      => $order->get_shipping_first_name(),
			'lastName'       => $order->get_shipping_last_name(),
			'zipCode'        => $order->get_shipping_postcode(),
			'streetAddress1' => $order->get_shipping_address_1(),
			'phoneNumber'    => $order->get_billing_phone(),
			'companyName'    => $order->get_shipping_company(),
			'yourReference'  => '',
		);
	}
}
