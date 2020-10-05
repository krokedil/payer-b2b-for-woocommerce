<?php
/**
 * Get order lines helper class.
 *
 * @package Payer_B2B/Classes/Requests/Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Helper class for order creation.
 */
class PB2B_Order_Lines {
	/**
	 * Counter for order line position.
	 *
	 * @var integer
	 */
	public static $i = 0;
	/**
	 * Gets formated order items.
	 *
	 * @param int $order_id The WooCommerce order object.
	 * @return array Formated order items.
	 */
	public static function get_order_items( $order_id ) {
		$formated_order_items = array();
		$order                = wc_get_order( $order_id );
		// Get order items.
		$order_items = $order->get_items();
		foreach ( $order_items as $order_item ) {
			self::$i++;
			$formated_order_items[] = self::get_order_item( $order, $order_item );
		}

		// Get order fees.
		$order_fees = $order->get_fees();
		foreach ( $order_fees as $fee ) {
			self::$i++;
			$formated_order_items[] = self::get_fee( $fee );
		}

		// Get order shipping.
		if ( $order->get_shipping_method() ) {
			self::$i++;
			$shipping = self::get_shipping( $order );
			if ( null !== $shipping ) {
				$formated_order_items[] = $shipping;
			}
		}
		return $formated_order_items;
	}
	/**
	 * Gets formated order item.
	 *
	 * @param object $order The WooCommerce order.
	 * @param object $order_item WooCommerce order item object.
	 * @return array Formated order item.
	 */
	public static function get_order_item( $order, $order_item ) {
		return array(
			'itemType'          => 'FREEFORM',
			'position'          => self::$i,
			'articleNumber'     => self::get_product_sku( $order_item->get_product() ),
			'description'       => $order_item->get_name(),
			'quantity'          => $order_item->get_quantity(),
			'unit'              => 'pcs',
			'unitPrice'         => self::get_product_unit_price( $order_item ),
			'unitVatAmount'     => self::get_product_unit_tax( $order_item ),
			'vatPercentage'     => self::get_product_tax_rate( $order, $order_item ),
			'subtotalPrice'     => self::get_line_total( $order_item ),
			'subtotalVatAmount' => self::get_line_tax( $order_item ),
		);
	}
	/**
	 * Gets the product name.
	 *
	 * @param object $order_item The order item.
	 * @return string
	 */
	public static function get_product_name( $order_item ) {
		$item_name = $order_item->get_name();
		return strip_tags( $item_name );
	}
	/**
	 * Gets the products unit price.
	 *
	 * @param WC_Order_Item_Product $order_item The order item.
	 * @return int
	 */
	public static function get_product_unit_price( $order_item ) {
		$quantity      = 0 === abs( $order_item->get_quantity() ) ? 1 : abs( $order_item->get_quantity() );
		$item_subtotal = round( ( $order_item->get_total() + $order_item->get_total_tax() ) / $quantity * 100 );
		return intval( $item_subtotal );
	}

	/**
	 * Gets the line total.
	 *
	 * @param WC_Order_Item_Product $order_item WooCommerce order item.
	 * @return int
	 */
	public static function get_line_total( $order_item ) {
		$line_total = round( ( $order_item->get_total() + $order_item->get_total_tax() ) * 100 );
		return intval( $line_total );
	}

	/**
	 * Gets the product tax amount.
	 *
	 * @param WC_Order_Item_Product $order_item WooCommerce order item.
	 * @return int
	 */
	public static function get_product_unit_tax( $order_item ) {
		$quantity    = 0 === abs( $order_item->get_quantity() ) ? 1 : abs( $order_item->get_quantity() );
		$product_tax = round( $order_item->get_total_tax() / $quantity * 100 );
		return intval( $product_tax );
	}

	/**
	 * Gets the product tax amount.
	 *
	 * @param WC_Order_Item_Product $order_item WooCommerce order item.
	 * @return int
	 */
	public static function get_line_tax( $order_item ) {
		$product_tax = round( $order_item->get_total_tax() * 100 );
		return intval( $product_tax );
	}

	/**
	 * Gets the tax rate for the product.
	 *
	 * @param WC_Order              $order The order item.
	 * @param WC_Order_Item_Product $order_item WooCommerce order item.
	 * @return float
	 */
	public static function get_product_tax_rate( $order, $order_item ) {
		$tax_items = $order->get_items( 'tax' );
		foreach ( $tax_items as $tax_item ) {
			$rate_id = $tax_item->get_rate_id();
			if ( key( $order_item->get_taxes()['total'] ) === $rate_id ) {
				return round( WC_Tax::_get_tax_rate( $rate_id )['tax_rate'] * 100 );
			}
		}
	}

	/**
	 * Gets the product SKU.
	 *
	 * @param WC_Product $product WooCommerce product item.
	 * @return string
	 */
	public static function get_product_sku( $product ) {
		if ( $product->get_sku() ) {
			$item_reference = $product->get_sku();
		} else {
			$item_reference = $product->get_id();
		}
		return substr( (string) $item_reference, 0, 64 );
	}
	/**
	 * Formats the fee.
	 *
	 * @param object $fee A WooCommerce Fee.
	 * @return array
	 */
	public static function get_fee( $fee ) {
		return array(
			'itemType'          => 'FREEFORM',
			'position'          => self::$i,
			'articleNumber'     => $fee->get_id(),
			'description'       => $fee->get_name(),
			'quantity'          => 1,
			'unit'              => 'pcs',
			'unitPrice'         => intval( round( $fee->get_total() * 100 ) ),
			'unitVatAmount'     => 0,
			'vatPercentage'     => 0,
			'subtotalPrice'     => intval( round( $fee->get_total() * 100 ) ),
			'subtotalVatAmount' => 0,
		);
	}

	/**
	 * Formats the shipping.
	 *
	 * @param WC_Order $order The WooCommerce order.
	 * @return array
	 */
	public static function get_shipping( $order ) {
		if ( $order->get_shipping_total() <= 0 ) {
			return array(
				'itemType'          => 'FREEFORM',
				'position'          => self::$i,
				'articleNumber'     => 0,
				'description'       => __( 'Free Shipping', 'payer-b2b-for-woocommerce' ),
				'quantity'          => 1,
				'unit'              => 'pcs',
				'unitPrice'         => 0,
				'unitVatAmount'     => 0,
				'vatPercentage'     => 0,
				'subtotalPrice'     => 0,
				'subtotalVatAmount' => 0,
			);
		} else {
			return array(
				'itemType'          => 'FREEFORM',
				'position'          => self::$i,
				'articleNumber'     => $order->get_shipping_method(),
				'description'       => $order->get_shipping_method(),
				'quantity'          => 1,
				'unit'              => 'pcs',
				'unitPrice'         => intval( round( ( $order->get_shipping_total() + $order->get_shipping_tax() ) * 100 ) ),
				'unitVatAmount'     => intval( round( $order->get_shipping_tax() * 100 ) ),
				'vatPercentage'     => ( '0' !== $order->get_shipping_tax() ) ? self::get_product_tax_rate( $order, current( $order->get_items( 'shipping' ) ) ) : 0,
				'subtotalPrice'     => intval( round( ( $order->get_shipping_total() + $order->get_shipping_tax() ) * 100 ) ),
				'subtotalVatAmount' => intval( round( $order->get_shipping_tax() * 100 ) ),
			);
		}
	}
}
