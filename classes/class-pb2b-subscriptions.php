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
		add_action( 'payer_authorize_payment', array( $this, 'set_recurring_token_for_order' ), 10, 2 );
		add_action( 'woocommerce_scheduled_subscription_payment_payer_b2b_v1_invoice', array( $this, 'handle_invoice_recurring' ), 10, 2 );
		add_action( 'woocommerce_scheduled_subscription_payment_payer_b2b_v2_invoice', array( $this, 'handle_invoice_recurring' ), 10, 2 );
		add_action( 'woocommerce_scheduled_subscription_payment_payer_b2b_card', array( $this, 'handle_card_recurring' ), 10, 2 );
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
				update_post_meta( $order_id, '_payer_order_id', sanitize_key( $response['orderId'] ) );
				update_post_meta( $order_id, '_payer_reference_id', sanitize_key( $response['referenceId'] ) );
				$renewal_order->add_order_note( __( 'Renewal order created with Payer.' ) . ' ' . $response['orderId'] );
				$subscription->payment_complete( $response['orderId'] );
			}
		}
	}

	/**
	 * Handles recurring payments with Payer Card.
	 *
	 * @param [type] $renewal_total
	 * @param [type] $renewal_order
	 * @return void
	 */
	public function handle_card_recurring( $renewal_total, $renewal_order ) {
		$order_id      = $renewal_order->get_id();
		$subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );
		$error         = false;

		$request  = new PB2B_Request_Authorize_Payment( $order_id );
		$response = $request->request();
		if ( is_wp_error( $response ) ) {
			$error = $response;
		}
		if ( 'AUTHORIZED' === $response['payment']['status'] ) {
			foreach ( $subscriptions as $subscription ) {
				if ( is_wp_error( $error ) ) {
					$error = reset( $error->errors )[0];
					$renewal_order->add_order_note( __( 'Renewal order failed with Payer.' ) . ' ' . $error );
					$subscription->payment_failed();
				} else {
					$payment_operations = $response['payment']['paymentOperations'][0];
					update_post_meta( $order_id, '_payer_card_created_date', $payment_operations['createdDate'] );
					update_post_meta( $order_id, '_payer_card_opertaion_id', $payment_operations['operationId'] );
					update_post_meta( $order_id, '_payer_payment_id', $response['paymentId'] );
					$renewal_order->add_order_note( __( 'Renewal order created with Payer.' ) . ' ' . $response['paymentId'] );
					$subscription->payment_complete( $response['paymentId'] );
				}
			}
		} else {
			return false; // TODO: Show error message.
		}
	}

	/**
	 * Sets the recurring token for the subscription order.
	 *
	 * @param int   $order_id Order id.
	 * @param array $payer_order Payer order.
	 * @return array
	 */
	public function set_recurring_token_for_order( $order_id, $payer_order ) {
		$wc_order = wc_get_order( $order_id );
		if ( isset( $payer_order['token'] ) ) {
			update_post_meta( $order_id, '_payer_token', $payer_order['token'] );

			// This function is run after WCS has created the subscription order.
			// Let's add the _payer_token to the subscription as well.
			if ( class_exists( 'WC_Subscriptions' ) && ( wcs_order_contains_subscription( $wc_order, array( 'parent', 'renewal', 'resubscribe', 'switch' ) ) || wcs_is_subscription( $wc_order ) ) ) {
				$subcriptions = wcs_get_subscriptions_for_order( $order_id, array( 'order_type' => 'any' ) );
				foreach ( $subcriptions as $subcription ) {
					update_post_meta( $subcription->get_id(), '_payer_token', $payer_order['token'] );
				}
			}
		}

		return $payer_order;
	}

}
if ( class_exists( 'WC_Subscriptions' ) ) {
	new PB2B_Subscriptions();
}
