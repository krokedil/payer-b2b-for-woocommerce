<?php
/**
 * Gets the data needed for credit requests for Prepaid (V2) invoices.
 *
 * @package Payer_B2B/Classes/Requests/Helpers
 */

/**
 * Class to generate credit V2 data.
 */
class PB2B_Credit_Data {

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

	public static function get_partial_refund_items( $order_id ) {
		$order           = wc_get_order( $order_id );
		$refund_order    = self::get_refunded_order( $order_id );
		$refund_items    = $refund_order->get_items();
		$refund_shipping = $refund_order->get_items( 'shipping' );
		$refund_fees     = $refund_order->get_items( 'fee' );

		$modified_item_prices = 0;

		$position = 0;

		$partial_refund_data = array();
		$manual_refund_data  = array();

		if ( $refund_items ) {

			// Item refund.
			foreach ( $refund_items as $item ) {
				foreach ( $order->get_items() as $original_order_item ) {

					$position += 1;

					if ( $item->get_product_id() === $original_order_item->get_product_id() ) {
						break;
					}
				}

				if ( abs( $item->get_total() ) / abs( $item->get_quantity() ) === $original_order_item->get_total() / $original_order_item->get_quantity() ) {
					$partial_refund_data[] = array(
						'position' => $position,
						'quantity' => abs( $item->get_quantity() ),
					);
				} else {
					error_log( 'Item manual' );
					// The item is partial refunded.
					$tmp                      = PB2B_Order_Lines::get_order_item( $refund_order, $item );
					$tmp['quantity']          = 1;
					$tmp['subtotalPrice']     = abs( $tmp['subtotalPrice'] );
					$tmp['subtotalVatAmount'] = abs( $tmp['subtotalVatAmount'] );
					$tmp['position']          = $position;
					$manual_refund_data[]     = $tmp;
				}
			}
		}

		if ( $refund_fees ) {

			// Fee item refund.
			if ( $refund_fees ) {
				foreach ( $refund_fees as $fee ) {

					foreach ( $order->get_items( 'shipping' ) as $original_order_fee ) {
						$position += 1;

						if ( $fee->get_name() === $original_order_fee->get_name() ) {
							// Found product match, continue.
							break;
						}
					}

					if ( abs( $fee->get_total() ) / abs( $fee->get_quantity() ) === $original_order_fee->get_total() / $original_order_fee->get_quantity() ) {
						$partial_refund_data[] = array(
							'position' => $position,
							'quantity' => 1,
						);

					} else {
						error_log( 'Fee manual' );
						// The fee is partial refunded.
						$tmp                      = PB2B_Order_Lines::get_fee( $fee );
						$tmp['subtotalPrice']     = abs( $tmp['subtotalPrice'] );
						$tmp['subtotalVatAmount'] = abs( $tmp['subtotalVatAmount'] );
						$tmp['position']          = $position;
						$manual_refund_data[]     = $tmp;
					}
				}
			}
		}

		if ( $refund_shipping ) {

			// Shipping item refund.
			if ( $refund_shipping ) {
				foreach ( $refund_shipping as $shipping ) {

					foreach ( $order->get_items( 'shipping' ) as $original_order_shipping ) {
						$position += 1;

						if ( $shipping->get_name() === $original_order_shipping->get_name() ) {
							// Found product match, continue.
							break;
						}
					}

					if ( abs( $shipping->get_total() ) / abs( $shipping->get_quantity() ) === $original_order_shipping->get_total() / $original_order_shipping->get_quantity() ) {
						$partial_refund_data[] = array(
							'position' => $position,
							'quantity' => 1,
						);

					} else {
						error_log( 'Shipping manual' );
						// The shipping is partial refunded.
						$tmp                      = PB2B_Order_Lines::get_shipping( $refund_order );
						$tmp['subtotalPrice']     = abs( $tmp['subtotalPrice'] );
						$tmp['subtotalVatAmount'] = abs( $tmp['subtotalVatAmount'] );
						$tmp['position']          = $position;
						$manual_refund_data[]     = $tmp;

					}
				}
			}
		}

		return array(
			'partial_refund_data' => $partial_refund_data,
			'manual_refund_data'  => $manual_refund_data,
		);
	}


	/**
	 * Gets refunded order
	 *
	 * @param int $order_id
	 * @return WC_Order
	 */
	public static function get_refunded_order( $order_id ) {
		$query_args      = array(
			'fields'         => 'id=>parent',
			'post_type'      => 'shop_order_refund',
			'post_status'    => 'any',
			'posts_per_page' => -1,
		);
		$refunds         = get_posts( $query_args );
		$refund_order_id = array_search( $order_id, $refunds );
		if ( is_array( $refund_order_id ) ) {
			foreach ( $refund_order_id as $key => $value ) {
				if ( ! get_post_meta( $value, '_krokedil_refunded' ) ) {
					$refund_order_id = $value;
					break;
				}
			}
		}
		$refund_order = wc_get_order( $refund_order_id );

		return $refund_order;
	}
}
