<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Shows credential form on settings page.
 */
function payer_b2b_show_credentials_form() {

	$payer_invoice_v1_settings = get_option( 'woocommerce_payer_b2b_v1_invoice_settings' );

	// Setting values.
	$agent_id = $payer_invoice_v1_settings['agent_id'];
	$api_key  = $payer_invoice_v1_settings['api_key'];

	$settings_payer = array();
	// Add Title to the Settings
	$settings_payer[] = array(
		'name' => __( 'Payer B2B credentials:', 'payer-b2b-for-woocommerce' ),
		'type' => 'title',
	);
	$settings_payer[] = array(
		'name' => __( 'Agent ID: ' . $agent_id, 'payer-b2b-for-woocommerce' ),
		'type' => 'title',
	);
	$settings_payer[] = array(
		'name'  => __( 'Soap ID: ' . $api_key, 'payer-b2b-for-woocommerce' ),
		'type'  => 'title',
		'class' => 'titledesc',
	);
	$settings_payer[] = array(
		'type' => 'sectionend',
	);
	return $settings_payer;
}
