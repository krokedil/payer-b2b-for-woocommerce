<?php
/**
 * Handles callbacks for the plugin.
 *
 * @package Payer_B2B/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Callback class.
 */
class PB2B_API_Callbacks {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_api_pb2b_wc_notification', array( $this, 'notification_cb' ) );
		add_action( 'woocommerce_api_pb2b_wc_signup', array( $this, 'signup_cb' ) );
		add_action( 'payer_check_for_order', array( $this, 'pb2b_check_for_order_callback' ), 10, 2 );
	}

	/**
	 * Handles notification callbacks.
	 *
	 * @return void
	 */
	public function notification_cb() {
		$payer_token = null;
		if ( isset( $_GET['token'] ) ) {
			$payer_token = $_GET['token'];
		}

		if ( isset( $payer_token ) ) {
			// Get event payload.
			$request  = new PB2B_Request_Get_Event_Payload();
			$response = $request->request( $payer_token );
			if ( is_wp_error( $response ) ) {
				PB2B_Logger::log( 'Could not get event payload in notification callback.' );
			} else {
				// Check payload and proceed with schedule event if its ok.
				if ( isset( $response['type'] ) && isset( $response['payload']['paymentId'] ) ) {
					PB2B_Logger::log( 'Notification Listener hit: ' . json_encode( $_GET ) . ' URL: ' . $_SERVER['REQUEST_URI'] );
					wp_schedule_single_event( time() + 120, 'payer_check_for_order', array( $response['payload']['paymentId'], $response['type'] ) );
				}

				// Let Payer know the callback request went OK.
				$request  = new PB2B_Request_Get_Event_Acknowledge();
				$response = $request->request( $payer_token );
				if ( is_wp_error( $response ) ) {
					PB2B_Logger::log( 'Could not acknowledge event in notification callback.' );
				}
			}
		} else {
			PB2B_Logger::log( 'No token found in notification callback URL.' );
		}
	}

	/**
	 * Check for order callback.
	 *
	 * @param int    $payment_id The Payer payment id.
	 * @param string $event_type The Payer event type.
	 * @return void
	 */
	public function pb2b_check_for_order_callback( $payment_id, $event_type ) {
		$query          = new WC_Order_Query(
			array(
				'limit'          => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'return'         => 'ids',
				'payment_method' => 'payer_b2b_card',
				'date_created'   => '>' . ( time() - MONTH_IN_SECONDS ),
			)
		);
		$orders         = $query->get_orders();
		$order_id_match = '';

		foreach ( $orders as $order_id ) {
			$order_payment_id = get_post_meta( $order_id, '_payer_payment_id', true );

			if ( $order_payment_id == $payment_id ) {
				$order_id_match = $order_id;
				break;
			}
		}

		// Did we get a match?
		if ( $order_id_match ) {
			$order = wc_get_order( $order_id_match );

			if ( $order ) {
				PB2B_Logger::log( 'API-callback hit. Payment id ' . $payment_id . '. already exist in order ID ' . $order_id_match . ' Checking order status...' );
				$this->check_order_status( $payment_id, $order );
			} else {
				// No order, why?
				PB2B_Logger::log( 'API-callback hit. Payment id ' . $payment_id . '. already exist in order ID ' . $order_id_match . '. But we could not instantiate an order object' );
			}
		} else {
			// No order found.
			PB2B_Logger::log( 'API-callback hit. We could NOT find Payment id ' . $payment_id );
		}
	}

	/**
	 * Check order status.
	 *
	 * @param int      $payment_id The Payer payment id.
	 * @param WC_Order $order The WC order.
	 * @return void
	 */
	public function check_order_status( $payment_id, $order ) {

		if ( is_object( $order ) ) {
			PB2B_Logger::log( 'API-callback order status check. Order status: ' . $order->get_status() );
			// Check order status.
			if ( ! $order->has_status( array( 'on-hold', 'processing', 'completed' ) ) ) {
				$request     = new PB2B_Request_Get_Payment();
				$payer_order = $request->request( $payment_id );

				// Check so order totals match.
				$order_totals_match = $this->check_order_totals( $payer_order, $order );
				// Set order status in Woo if order totals match.
				if ( true === $order_totals_match ) {
					$this->set_order_status( $payer_order, $order );
				}
			}
		}
	}

	/**
	 * Set order status.
	 *
	 * @param array    $payer_order The Payer order.
	 * @param WC_Order $order The WC order.
	 * @return void
	 */
	public function set_order_status( $payer_order, $order ) {
		$order->payment_complete( $payer_order['payment']['id'] );
		$order->add_order_note( 'Payment via Payer. Payment ID: ' . sanitize_key( $payer_order['payment']['id'] ) );
		PB2B_Logger::log( 'Order status not set correctly for order ' . $order->get_order_number() . ' during checkout process. Setting order status to Processing/Completed.' );
	}

	/**
	 * Check order totals.
	 *
	 * @param array    $payer_order The Payer order.
	 * @param WC_Order $order The WC order.
	 * @return bool
	 */
	public function check_order_totals( $payer_order, $order ) {

		$order_totals_match = true;

		// Check order total and compare it with Woo.
		$woo_order_total   = intval( round( $order->get_total() * 100 ) );
		$payer_order_total = intval( round( $payer_order['payment']['authorizedAmount'] ) );
		if ( $woo_order_total > $payer_order_total && ( $woo_order_total - $payer_order_total ) > 30 ) {
			$order->update_status( 'on-hold', sprintf( __( 'Order needs manual review. WooCommerce order total and Payer order total do not match. Payer order total: %s.', 'payer-b2b-for-woocommerce' ), $payer_order_total ) );
			PB2B_Logger::log( 'Order total missmatch in order:' . $order->get_order_number() . '. Woo order total: ' . $woo_order_total . '. Payer order total: ' . $payer_order_total );
			$order_totals_match = false;
		} elseif ( $payer_order_total > $woo_order_total && ( $payer_order_total - $woo_order_total ) > 30 ) {
			$order->update_status( 'on-hold', sprintf( __( 'Order needs manual review. WooCommerce order total and Payer order total do not match. Payer order total: %s.', 'payer-b2b-for-woocommerce' ), $payer_order_total ) );
			PB2B_Logger::log( 'Order total missmatch in order:' . $order->get_order_number() . '. Woo order total: ' . $woo_order_total . '. Payer order total: ' . $payer_order_total );
			$order_totals_match = false;
		}

		return $order_totals_match;
	}

	/**
	 * Handles the callback for Signup.
	 *
	 * @return void
	 */
	public function signup_cb() {
		$body = file_get_contents( 'php://input' );
		$data = json_decode( $body, true );

		PB2B_Logger::log( "Signup callback recieved: {$body}" );

		$email = $data['customer']['email'];
		$user  = get_user_by( 'email', $email );

		if ( empty( $user ) ) {
			PB2B_Logger::log( "Signup callback: No user found for the email {$email}" );
			header( 'HTTP/1.1 200 OK' );
			die();
		}

		$status = $data['sessionStatus'];
		update_user_meta( $user->ID, 'pb2b_signup_status', $status );
		header( 'HTTP/1.1 200 OK' );
		die();
	}
}

new PB2B_API_Callbacks();
