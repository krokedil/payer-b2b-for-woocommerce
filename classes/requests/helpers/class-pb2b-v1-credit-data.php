<?php
/**
 * Gets the data needed for credit requests for v1 invoices.
 *
 * @package Payer_B2B/Classes/Requests/Helpers
 */

/**
 * Class to generate credit v1 data.
 */
class PB2B_V1_Credit_Data {

	/**
	 * Returns the tax rate for the refund.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return int
	 */
	public static function get_refund_tax_rate( $order_id ) {
		$order      = wc_get_order( $order_id );
		$tax_amount = $order->get_total_tax();
		$amount     = $order->get_total();
		return intval( round( ( $tax_amount / $amount ) * 100 ) );
	}
}
