<?php
/**
 * Settings form fields for the gateway.
 *
 * @package Payer_B2B/Includes
 */

$settings = array(
	'enabled'                => array(
		'title'   => __( 'Enable/Disable', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable ' . $this->method_title, 'payer-b2b-for-woocommerce' ), // phpcs:ignore
		'default' => 'no',
	),
	'title'                  => array(
		'title'       => __( 'Title', 'payer-b2b-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'payer-b2b-for-woocommerce' ),
		'default'     => __( $this->method_title, 'payer-b2b-for-woocommerce' ), // phpcs:ignore
		'desc_tip'    => true,
	),
	'description'            => array(
		'title'       => __( 'Description', 'payer-b2b-for-woocommerce' ),
		'type'        => 'textarea',
		'default'     => __( 'Pay with Payer via invoice.', 'payer-b2b-for-woocommerce' ),
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'payer-b2b-for-woocommerce' ),
	),
	'agent_id'               => array(
		'title'   => __( 'Agent ID', 'payer-b2b-for-woocommerce' ),
		'type'    => 'text',
		'default' => '',
	),
	'api_key'                => array(
		'title'   => __( 'API Key', 'payer-b2b-for-woocommerce' ),
		'type'    => 'text',
		'default' => '',
	),
	'allowed_customer_types' => array(
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
	),
	'order_management'       => array(
		'title'   => __( 'Enable Order Management', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Payer order capture on WooCommerce order completion and Payer order cancellation on WooCommerce order cancellation', 'payer-b2b-for-woocommerce' ),
		'default' => 'yes',
	),
	'enable_all_fields'      => array(
		'title'       => __( 'Enable extra checkout fields', 'payer-b2b-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enables the extra checkout fields added by Payer.', 'payer-b2b-for-woocommerce' ),
		'default'     => 'yes',
		'description' => __( 'If you disable these fields you need to have your own field for PNO and Org Nr. And use the filter payer_pno_field_name to change what field is used.', 'payer-b2b-for-woocommerce' ),
		'desc_tip'    => true,

	),
	'separate_signatory'     => array(
		'title'   => __( 'Enable separate signatory', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable the customer to enter a separate signatory for B2B purchases.', 'payer-b2b-for-woocommerce' ),
		'default' => 'yes',
	),
	'default_invoice_type'   => array(
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
	),
	'customer_invoice_type'  => array(
		'title'   => __( 'Customer selects invoice type', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'This allows the customer to select what invoice type they want.', 'payer-b2b-for-woocommerce' ),
		'default' => 'no',
	),
	'testmode'               => array(
		'title'   => __( 'Testmode', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Payer testmode', 'payer-b2b-for-woocommerce' ),
		'default' => 'yes',
	),
	'debug'                  => array(
		'title'   => __( 'Debug', 'payer-b2b-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable debug logging for the plugin', 'payer-b2b-for-woocommerce' ),
		'default' => 'yes',
	),
);

return apply_filters( 'payer_b2b_settings', $settings );
