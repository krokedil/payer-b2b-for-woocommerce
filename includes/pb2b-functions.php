<?php
/**
 * Functions file for the plugin.
 *
 * @package Payer_B2B/Includes
 */

/**
 * Maybe creates, stores a token as a transient and returns.AMFReader
 *
 * @param int $order_id WooCommerce order id.
 * @return string
 */
function payer_b2b_maybe_create_token( $order_id ) {
	$token = get_transient( 'payer_b2b_auth_token' );
	if ( false === $token ) {
		$request  = new PB2B_Request_Oauth( $order_id );
		$response = $request->request();
		if ( is_wp_error( $response ) || ! isset( $response['access_token'] ) ) {
			return $response;
		}
		// Set transient with 55minute life time.
		set_transient( 'payer_b2b_auth_token', $response['access_token'], 1 * 60 * 55 );
		$token = $response['access_token'];
	}
	return $token;
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'pb2b_add_pno_field_to_order', 10, 1 );
/**
 * Adds the PNO/Org nr field to the order view in WooCommerce Admin
 *
 * @param WC_Order $order The WooCommerce order.
 * @return void
 */
function pb2b_add_pno_field_to_order( $order ) {
	if ( get_post_meta( $order->get_id(), PAYER_PNO_DATA_NAME, true ) ) {
		echo '<p><strong>' . esc_html( 'PNO/Org number:', 'payer-b2b-for-woocommerce' ) . '</strong> <br/>' . esc_html( get_post_meta( $order->get_id(), PAYER_PNO_DATA_NAME, true ) ) . '</p>';
	}
}

/**
 * Register webhook.
 *
 * @param int    $order_id Order id.
 * @param string $event_type The event type to subscribe to.
 * @return bool
 */
function payer_b2b_register_webhook( $order_id, $event_type ) {
	$request  = new PB2B_Request_Register_Webhook( $order_id );
	$response = $request->request( $event_type );
	if ( is_wp_error( $response ) ) {
		return false;
	}
}
