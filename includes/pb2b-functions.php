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
