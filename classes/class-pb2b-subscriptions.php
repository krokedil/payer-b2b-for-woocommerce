<?php
/**
 * Subscription class file.
 *
 * @package Payer_B2B/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Subscription class.
 */
class PB2B_Subscriptions {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_scheduled_subscription_payment_payer_b2b_invoice', array( $this, 'handle_invoice_recurring' ), 10, 2 );
	}

	/**
	 * Handles recurring payments with Payer Invoice.
	 *
	 * @param float    $renewal_total The total amount of the renewal order.
	 * @param WC_Order $renewal_order The WooCommerce order for the renewal order.
	 * @return void
	 */
	public function handle_invoice_recurring( $renewal_total, $renewal_order ) {
		$order_id      = $renewal_order->get_id();
		$subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );
		$pno           = get_post_meta( WC_Subscriptions_Renewal_Order::get_parent_order_id( $order_id ), PAYER_PNO_DATA_NAME, true );
		$b2b           = $renewal_order->get_billing_company() ? true : false;
		$signatory     = get_post_meta( WC_Subscriptions_Renewal_Order::get_parent_order_id( $order_id ), '_payer_signatory', true );
		$error         = false;
		// Run create order and invoice.
		$args     = array(
			'b2b'             => $b2b,
			'pno_value'       => $pno,
			'signatory_value' => ! empty( $signatory ) ? $signatory : '',
		);
		$request  = new PB2B_Request_Create_Order( $order_id, $args );
		$response = $request->request();
		if ( is_wp_error( $response ) ) {
			$error = $response;
		}

		foreach ( $subscriptions as $subscription ) {
			if ( is_wp_error( $error ) ) {
				$error = reset( $error->errors )[0];
				$renewal_order->add_order_note( __( 'Renewal order failed with Payer.' ) . ' ' . $error );
				$subscription->payment_failed();
			} else {
				update_post_meta( $order_id, '_payer_order_id', $response['orderId'] );
				update_post_meta( $order_id, '_payer_reference_id', $response['referenceId'] );
				$renewal_order->add_order_note( __( 'Renewal order created with Payer.' ) . ' ' . $response['orderId'] );
				$subscription->payment_complete( $response['orderId'] );
			}
		}
	}
}
if ( class_exists( 'WC_Subscriptions' ) ) {
	new PB2B_Subscriptions();
}
