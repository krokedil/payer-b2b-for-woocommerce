<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$settings = array(
	'enabled'     => array(
		'title'   => __( 'Enable/Disable', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable ' . $this->method_title, 'payer-b2b-for-woocommerce' ), // phpcs:ignore
		'default' => 'no',
	),
	'title'       => array(
		'title'       => __( 'Title', 'payer-b2b-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'payer-b2b-for-woocommerce' ),
		'default'     => __( $this->method_title, 'payer-b2b-for-woocommerce' ), // phpcs:ignore
		'desc_tip'    => true,
	),
	'description' => array(
		'title'       => __( 'Description', 'payer-b2b-for-woocommerce' ),
		'type'        => 'textarea',
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'payer-b2b-for-woocommerce' ),
	),
);

// Payer B2B Card settings.
if ( 'payer_b2b_card' === $this->id ) {
	$settings['add_order_lines'] = array(
		'title'       => __( 'Add order lines', 'payer-b2b-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable Add order lines', 'payer-b2b-for-woocommerce' ),
		'desc_tip'    => true,
		'description' => __( 'Add order lines to the card payment.', 'payer-b2b-for-woocommerce' ),
		'default'     => 'no',
	);
}

// Payer B2B V2 Invoice settings.
if ( 'payer_b2b_prepaid_invoice' === $this->id ) {
	// Automatic Credit check checkbox.
	$settings['automatic_credit_check'] = array(
		'title'   => __( 'Automatic Credit Check', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( "Run an automatic check on the customer's credit status", 'payer-b2b-for-woocommerce' ),
		'default' => 'yes',
	);
}

// -------
if ( 'payer_b2b_normal_invoice' === $this->id || 'payer_b2b_prepaid_invoice' === $this->id ) {
	$settings['enable_all_fields'] = array(
		'title'       => __( 'Enable extra checkout fields', 'payer-b2b-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enables the extra checkout fields added by Payer.', 'payer-b2b-for-woocommerce' ),
		'default'     => 'yes',
		'description' => __( 'If you disable these fields you need to have your own field for PNO and Org Nr. And use the filter payer_pno_field_name to change what field is used.', 'payer-b2b-for-woocommerce' ),
		'desc_tip'    => true,

	);
	$settings['separate_signatory']     = array(
		'title'   => __( 'Enable separate signatory', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable the customer to enter a separate signatory for B2B purchases.', 'payer-b2b-for-woocommerce' ),
		'default' => 'yes',
	);
	$settings['default_invoice_type']   = array(
		'title'       => __( 'Default invoice type', 'payer-b2b-for-woocommerce' ),
		'type'        => 'select',
		'options'     => array(
			'EMAIL'    => 'Email',
			'PRINT'    => 'Mail',
			'PDF'      => 'PDF',
			'EINVOICE' => 'E-Invoice',
		),
		'description' => __( 'Select what invoice type you want to use', 'payer-b2b-for-woocommerce' ),
		'default'     => 'EMAIL',
		'desc_tip'    => false,
	);
	$settings['customer_invoice_type']  = array(
		'title'   => __( 'Customer selects invoice type', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'This allows the customer to select what invoice type they want.', 'payer-b2b-for-woocommerce' ),
		'default' => 'no',
	);
	$settings['allowed_customer_types'] = array(
		'title'       => __( 'Allowed Customer Types', 'payer-b2b-for-woocommerce' ),
		'type'        => 'select',
		'options'     => array(
			'B2C'  => __( 'B2C only', 'payer-b2b-for-woocommerce' ),
			'B2B'  => __( 'B2B only', 'payer-b2b-for-woocommerce' ),
			'B2CB' => __( 'B2C & B2B (defaults to B2C)', 'payer-b2b-for-woocommerce' ),
			'B2BC' => __( 'B2B & B2C (defaults to B2B)', 'payer-b2b-for-woocommerce' ),
		),
		'description' => __( 'Select what customer type that you want to sell to', 'payer-b2b-for-woocommerce' ),
		'default'     => 'B2BC',
		'desc_tip'    => false,
	);
}
// Payer B2B V1 Invoice settings.
if ( 'payer_b2b_normal_invoice' === $this->id ) {
	$settings['agent_id'] = array(
		'title'   => __( 'Agent ID', 'payer-b2b-for-woocommerce' ),
		'type'    => 'text',
		'default' => '',
	);
	$settings['api_key']  = array(
		'title'   => __( 'API Key', 'payer-b2b-for-woocommerce' ),
		'type'    => 'text',
		'default' => '',
	);

	$settings['order_management'] = array(
		'title'   => __( 'Enable Order Management', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Payer order capture on WooCommerce order completion and Payer order cancellation on WooCommerce order cancellation', 'payer-b2b-for-woocommerce' ),
		'default' => 'yes',
	);

	$settings['testmode'] = array(
		'title'   => __( 'Testmode', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Payer testmode', 'payer-b2b-for-woocommerce' ),
		'default' => 'yes',
	);
	$settings['debug']    = array(
		'title'   => __( 'Debug', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable debug logging for the plugin', 'payer-b2b-for-woocommerce' ),
		'default' => 'yes',
	);
} else {
	$settings['factory_notice'] = array(
		'title' => __( 'Put credentials in the Payer B2B Invoice settings.', 'payer-b2b-for-woocommerce' ),
		'type'  => 'title',
	);
}



return apply_filters( 'payer_b2b_factory_settings', $settings );
