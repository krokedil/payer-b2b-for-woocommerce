<?php
/**
 * Order management class file.
 *
 * @package Payer_B2B/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order management class.
 */
class PB2B_Order_Management {
	/**
	 * If order management is enabled.
	 *
	 * @var boolean
	 */
	public $order_management_enabled = false;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'cancel_reservation' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'activate_reservation' ) );
		add_action( 'woocommerce_saved_order_items', array( $this, 'update_order' ) );
		$settings                       = get_option( 'woocommerce_payer_b2b_v1_invoice_settings' );
		$this->order_management_enabled = 'yes' === $settings['order_management'] ? true : false;
	}

	/**
	 * Cancels the order with the payment provider.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return void
	 */
	public function cancel_reservation( $order_id ) {
		// Remove the update action to prevent error.
		remove_action( 'woocommerce_saved_order_items', array( $this, 'update_order' ) );
		$order           = wc_get_order( $order_id );
		$payer_reference = get_post_meta( $order_id, '_payer_reference_id', true );
		// If this order wasn't created using Payer payment method, bail.
		if ( 'payer_b2b_v1_invoice' === $order->get_payment_method() && $this->order_management_enabled && $payer_reference && 0 < $order->get_total() ) {
			if ( get_post_meta( $order_id, '_payer_invoice_number' ) ) {
				$order->set_status( 'on-hold', __( 'An invoice has already been created for this order, can not cancel at this point use refund instead.', 'payer-b2b-for-woocommerce' ) );
				$order->save();
				return;
			}

			// Cancel the order.
			$request  = new PB2B_Request_Delete_Order( $order_id );
			$response = $request->request();
			if ( is_wp_error( $response ) ) {
				$error = reset( $response->errors )[0];
				$order->set_status( 'on-hold', __( 'Failed to cancel the order with Payer. Please try again.', 'payer-b2b-for-woocommerce' ) . ' ' . $error );
				$order->save();
				return;
			}

			$order->add_order_note( __( 'Order canceled with Payer', 'payer-b2b-for-woocommerce' ) );
		}
	}

	/**
	 * Activate the order with the payment provider.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return void
	 */
	public function activate_reservation( $order_id ) {
		// Remove the update action to prevent error.
		remove_action( 'woocommerce_saved_order_items', array( $this, 'update_order' ) );
		$order                = wc_get_order( $order_id );
		$payer_payment_method = in_array(
			$order->get_payment_method(),
			array( // Payer payment methods.
				'payer_b2b_v1_invoice',
				'payer_b2b_v2_invoice',
				'payer_b2b_card',
			),
			true
		);
		// If this order wasn't created using Payer payment method, bail.
		if ( $payer_payment_method && $this->order_management_enabled && 0 < $order->get_total() ) {
			if ( get_post_meta( $order_id, '_payer_invoice_number' ) ) { // TODO: add check for card.
				// Invoice already created with Payer, bail.
				return;
			}
			if ( 'yes' !== get_post_meta( $order_id, '_payer_invoice_approved' ) ) {
				$request  = new PB2B_Request_Approve_Invoice( $order_id );
				$response = $request->request();
				if ( is_wp_error( $response ) ) {
					$error = reset( $response->errors )[0];
					$order->set_status( 'on-hold', __( 'Invoice approval failed with Payer. Please try again.', 'payer-b2b-for-woocommerce' ) . ' ' . $error );
					$order->save();
					return;
				}
				update_post_meta( $order_id, '_payer_invoice_approved', sanitize_key( 'yes' ) );
			}
			// V1 Invoice.
			if ( 'payer_b2b_v1_invoice' === $order->get_payment_method() ) {
				$request  = new PB2B_Request_Create_V1_Invoice( $order_id );
				$response = $request->request();
				if ( is_wp_error( $response ) ) {
					$error = reset( $response->errors )[0];
					$order->set_status( 'on-hold', __( 'Invoice creation failed with Payer. Please try again.', 'payer-b2b-for-woocommerce' ) . ' ' . $error );
					$order->save();
					return;
				}
				$invoice_number = $response['invoiceNumber'];
				update_post_meta( $order_id, '_payer_invoice_number', sanitize_key( $invoice_number ) );
				$text          = __( 'Invoice created with Payer. Invoice Number:', 'payer-b2b-for-woocommerce' ) . ' %s ';
				$formated_text = sprintf( $text, $invoice_number );
				$order->add_order_note( $formated_text );
			}

			// V2 Invoice.
			if ( 'payer_b2b_v2_invoice' === $order->get_payment_method() ) {
				$request  = new PB2B_Request_Create_V2_Invoice( $order_id );
				$response = $request->request();
				if ( is_wp_error( $response ) ) {
					$error = reset( $response->errors )[0];
					$order->set_status( 'on-hold', __( 'Invoice creation failed with Payer. Please try again.', 'payer-b2b-for-woocommerce' ) . ' ' . $error );
					$order->save();
					return;
				}
				$invoice_number = $response['invoice']['invoiceNumber'];
				$invoice_url    = $response['invoice']['publicInvoiceUrl'];
				$invoice_ocr    = $response['invoice']['referenceNumber'];
				update_post_meta( $order_id, '_payer_invoice_number', sanitize_key( $invoice_number ) );
				update_post_meta( $order_id, '_payer_public_url', esc_url_raw( $invoice_url ) );
				update_post_meta( $order_id, '_payer_ocr', sanitize_key( $invoice_ocr ) );
				$text          = __( 'Invoice created with Payer. Invoice Number:', 'payer-b2b-for-woocommerce' ) . ' %s ' . __( 'OCR Number:', 'payer-b2b-for-woocommerce' ) . ' <a href="%s" target="_blank">%s</a>';
				$formated_text = sprintf( $text, $invoice_number, $invoice_url, $invoice_ocr );
				$order->add_order_note( $formated_text );
			}

			// Card.
			if ( 'payer_b2b_card' === $order->get_payment_method() ) {
				$this->payer_b2b_card( $order, $order_id );
			}
		}
	}

	/**
	 * Capture card payment.
	 *
	 * @param WC_Order $order WC order.
	 * @param int      $order_id Order id.
	 * @return void
	 */
	public function payer_b2b_card( $order, $order_id ) {
		$request  = new PB2B_Request_Capture_Card_Payment( $order_id );
		$response = $request->request();
		error_log( 'capture card response ' . var_export( $response, true ) );

		if ( is_wp_error( $response ) ) {
			$error = reset( $response->errors )[0];
			$order->set_status( 'on-hold', __( 'Card capture failed with Payer. Please try again.', 'payer-b2b-for-woocommerce' ) . ' ' . $error );
			$order->save();
			return;
		}
		// TODO: Save information to order. Add order note.
	}

	/**
	 * Updates the order with the payment provider.
	 *
	 * @param int $order_id WooCommerce order id.
	 * @return void
	 */
	public function update_order( $order_id ) {
		$order = wc_get_order( $order_id );

		// If this is a subscription order, bail.
		if ( 'shop_subscription' === $order->get_type() ) {
			return;
		}

		// If we are missing a Payer order id, bail.
		if ( empty( get_post_meta( $order_id, '_payer_order_id', true ) ) ) {
			return;
		}

		if ( 'payer_b2b_v1_invoice' === $order->get_payment_method() && $this->order_management_enabled && 0 < $order->get_total() ) {
			if ( get_post_meta( $order_id, '_payer_invoice_approved' ) ) {
				$order->set_status( 'on-hold', __( 'Failed to update the order with Payer. An invoice has already been approved for this order', 'payer-b2b-for-woocommerce' ) );
				$order->save();
				return;
			}
			// Make request if we get here.
			$request  = new PB2B_Request_Update_Order( $order_id );
			$response = $request->request();

			if ( is_wp_error( $response ) ) {
				$error = reset( $response->errors )[0];
				$order->set_status( 'on-hold', __( 'Failed to update the order with Payer. Please try again.', 'payer-b2b-for-woocommerce' ) . ' ' . $error );
				$order->save();
				return;
			}

			$order->add_order_note( __( 'Order updated with Payer', 'payer-b2b-for-woocommerce' ) );
			$order->save();
		}
	}
}
new PB2B_Order_Management();
