<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Shows credential form on settings page.
 */
function payer_b2b_show_credentials_form() {

	$payer_invoice_normal_settings = get_option( 'woocommerce_payer_b2b_normal_invoice_settings' );

	// Setting values.
	$agent_id = $payer_invoice_normal_settings['agent_id'];
	$api_key  = $payer_invoice_normal_settings['api_key'];

	$settings_payer = array();
	// Add Title to the Settings
	$settings_payer[] = array(
		'name' => __( 'Payer B2B credentials:', 'payer-b2b-for-woocommerce' ),
		'type' => 'title',
	);
	$settings_payer[] = array(
		'name' => sprintf( __( 'Agent ID: %s', 'payer-b2b-for-woocommerce' ), $agent_id ),
		'type' => 'title',
	);
	$settings_payer[] = array(
		'name' => sprintf( __( 'Soap ID: %s', 'payer-b2b-for-woocommerce' ), $api_key ),
		'type' => 'title',
	);
	$settings_payer[] = array(
		'type' => 'sectionend',
	);
	return $settings_payer;
}
