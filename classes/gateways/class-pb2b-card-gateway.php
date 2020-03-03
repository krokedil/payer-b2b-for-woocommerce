<?php
/**
 * Gateway class file.
 *
 * @package Payer_B2B/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gateway class.
 */
class PB2B_Card_Gateway extends PB2B_Factory_Gateway {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->id                 = 'payer_b2b_card';
		$this->method_title       = __( 'Payer B2B Card', 'payer-b2b-for-woocommerce' );
		$this->icon               = '';
		$this->method_description = __( 'Allows payments through ' . $this->method_title . '.', 'payer-b2b-for-woocommerce' ); // phpcs:ignore

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->enabled         = $this->get_option( 'enabled' );
		$this->title           = $this->get_option( 'title' );
		$this->description     = $this->get_option( 'description' );
		$this->add_order_lines = $this->get_option( 'add_order_lines' );

		// Supports.
		$this->supports = array(
			'products',
			'refunds',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_admin',
			'multiple_subscriptions',
		);

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'wp_head', array( $this, 'process_payer_payment' ) );

		// Filters.
		add_filter( 'woocommerce_get_settings_checkout', array( $this, 'show_keys_in_settings' ), 10, 2 );
	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 *
	 * @return boolean
	 */
	public function is_available() {
		if ( 'yes' === $this->enabled ) {
			if ( in_array( get_woocommerce_currency(), array( 'DKK', 'EUR', 'GBP', 'NOK', 'SEK', 'USD' ), true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Shows setting keys on the settings page.
	 *
	 * @return void
	 */
	public function show_keys_in_settings( $settings, $current_section ) {
		if ( $this->id === $current_section ) { // Check the current section is what we want.
			return payer_b2b_show_credentials_form();
		} else { // If not, return the standard settings.
			return $settings;
		}
	}

	/**
	 * Process refund request.
	 *
	 * @param string $order_id The WooCommerce order ID.
	 * @param float  $amount The amount to be refunded.
	 * @param string $reasson The reasson given for the refund.
	 * @return void|bool
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );
		// Run logic here.
		$request  = new PB2B_Request_Refund_Card_Payment( $order_id );
		$response = $request->request( $amount, $reason );
		if ( is_wp_error( $response ) ) {
			$order->add_order_note( __( 'Refund request failed with Payer. Please try again.', 'payer-b2b-for-woocommerce' ) );
			return false;
		}
		$order->add_order_note( wc_price( $amount ) . ' ' . __( 'refunded with Payer.', 'payer-b2b-for-woocommerce' ) );
		return true;
	}

	/**
	 * Processes the WooCommerce Payment
	 *
	 * @param string $order_id The WooCommerce order ID.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order              = wc_get_order( $order_id );
		$create_payer_order = true;

		if ( class_exists( 'WC_Subscriptions' ) && wcs_order_contains_subscription( $order ) && 0 >= $order->get_total() ) {
			$create_payer_order = false;
		}

		// Check if we want to create an subscription order.
		if ( class_exists( 'WC_Subscriptions' ) && wcs_order_contains_subscription( $order ) && 0 < $order->get_total() ) {
			if ( 'yes' === $this->add_order_lines ) {
				$args     = array( // values is null for now.
					'b2b'       => null,
					'pno_value' => null,
				);
				$request  = new PB2B_Request_Create_Order( $order_id, $args );
				$response = $request->request();

				if ( is_wp_error( $response ) || ! isset( $response['referenceId'] ) ) {
					return false;
				}

				update_post_meta( $order_id, '_payer_order_id', sanitize_key( $response['orderId'] ) );
				update_post_meta( $order_id, '_payer_reference_id', sanitize_key( $response['referenceId'] ) );

				return $this->payer_b2b_stored_card( $order, $order_id );
			} else {
				return $this->payer_b2b_stored_card( $order, $order_id );
			}
		}

		// Check if we want to create an order.
		if ( $create_payer_order ) {

			if ( 'yes' === $this->add_order_lines ) {
				$args     = array( // values is null for now.
					'b2b'       => null,
					'pno_value' => null,
				);
				$request  = new PB2B_Request_Create_Order( $order_id, $args );
				$response = $request->request();

				if ( is_wp_error( $response ) || ! isset( $response['referenceId'] ) ) {
					return false;
				}

				update_post_meta( $order_id, '_payer_order_id', sanitize_key( $response['orderId'] ) );
				update_post_meta( $order_id, '_payer_reference_id', sanitize_key( $response['referenceId'] ) );

				return $this->payer_b2b_direct_card( $order, $order_id );
			} else {
				return $this->payer_b2b_direct_card( $order, $order_id );
			}
		} else {
			$order->payment_complete();
			$order->add_order_note( __( 'Free subscription order. No order created with Payer', 'payer-b2b-for-woocommerce' ) );
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}

	/**
	 * Makes the Create Stored Card request.
	 *
	 * @param WC_Order $order WC order.
	 * @param int      $order_id Order id.
	 * @return array|bool
	 */
	public function payer_b2b_stored_card( $order, $order_id ) {
		$request  = new PB2B_Request_Create_Stored_Card( $order_id );
		$response = $request->request();

		if ( is_wp_error( $response ) || ! isset( $response['token'] ) ) {
			return false;
		}

		update_post_meta( $order_id, '_payer_token', sanitize_key( $response['token'] ) );
		$order->add_order_note( __( 'Customer redirected to Payer payment page.', 'payer-b2b-for-woocommerce' ) );

		return array(
			'result'   => 'success',
			'redirect' => $response['url'],
		);
	}

	/**
	 * Makes the Create Direct Card request.
	 *
	 * @param WC_Order $order WC order.
	 * @param int      $order_id Order id.
	 * @return array|bool
	 */
	public function payer_b2b_direct_card( $order, $order_id ) {
		$request  = new PB2B_Request_Create_Direct_Card( $order_id );
		$response = $request->request();

		if ( is_wp_error( $response ) || ! isset( $response['paymentId'] ) ) {
			return false;
		}

		update_post_meta( $order_id, '_payer_payment_id', sanitize_key( $response['paymentId'] ) );
		update_post_meta( $order_id, '_payer_token', sanitize_key( $response['token'] ) );
		$order->add_order_note( __( 'Customer redirected to Payer payment page.', 'payer-b2b-for-woocommerce' ) );

		return array(
			'result'   => 'success',
			'redirect' => $response['url'],
		);
	}

	/**
	 * Process payer card payment.
	 *
	 * @return void|bool
	 */
	public function process_payer_payment() {
		if ( is_order_received_page() ) {
			global $wp;
			// Get the order ID
			$order_id = absint( $wp->query_vars['order-received'] );
			$order    = wc_get_order( $order_id );

			if ( $this->id === $order->get_payment_method() && ! $order->has_status( array( 'on-hold', 'processing', 'completed' ) ) ) {
				if ( class_exists( 'WC_Subscriptions' ) && wcs_order_contains_subscription( $order ) ) {
					$request  = new PB2B_Request_Get_Stored_Payment_Status( $order_id );
					$response = $request->request();
					if ( is_wp_error( $response ) ) {
						return false; // TODO: Show error message.
					}

					if ( 'READY' === $response['status'] ) {
						$this->payer_authorize_payment( $order, $order_id );
					} else {
						return false; // TODO: Show error message.
					}
				} else { // Order not subscription.
					$request  = new PB2B_Request_Get_Payment( $order_id );
					$response = $request->request();
					if ( is_wp_error( $response ) ) {
						return false; // TODO: Show error message.
					}

					if ( 'AUTHORIZED' === $response['payment']['status'] ) {
						$payment_operations = $response['payment']['paymentOperations'][0];
						update_post_meta( $order_id, '_payer_card_created_date', $payment_operations['createdDate'] );
						update_post_meta( $order_id, '_payer_card_opertaion_id', $payment_operations['operationId'] );

						$payer_payment_id = get_post_meta( $order_id, '_payer_payment_id', true );
						$order->payment_complete( $payer_payment_id );
					} else {
						return false; // TODO: Show error message.
					}
				}
			}
		}
	}

	/**
	 * Make authorize payment request.
	 *
	 * @return void
	 */
	public function payer_authorize_payment( $order, $order_id ) {
		$request  = new PB2B_Request_Authorize_Payment( $order_id );
		$response = $request->request();
		if ( is_wp_error( $response ) ) {
			return false; // TODO: Show error message.
		}
		if ( 'AUTHORIZED' === $response['payment']['status'] ) {
			do_action( 'payer_authorize_payment', $order_id, $response );

			$payment_operations = $response['payment']['paymentOperations'][0];
			update_post_meta( $order_id, '_payer_card_created_date', $payment_operations['createdDate'] );
			update_post_meta( $order_id, '_payer_card_opertaion_id', $payment_operations['operationId'] );
			update_post_meta( $order_id, '_payer_payment_id', $response['paymentId'] );

			$payer_payment_id = empty( $response['paymentId'] ) ? '' : $response['paymentId'];
			$order->payment_complete( $payer_payment_id );
		} else {
			return false; // TODO: Show error message.
		}
	}

}

/**
 * Add Payer_B2B 2.0 payment gateway
 *
 * @wp_hook woocommerce_payment_gateways
 * @param  array $methods All registered payment methods.
 * @return array $methods All registered payment methods.
 */
function add_payer_b2b_card_method( $methods ) {
	$methods[] = 'PB2B_Card_Gateway';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_payer_b2b_card_method' );
