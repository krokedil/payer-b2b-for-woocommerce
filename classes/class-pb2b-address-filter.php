<?php

/**
 * Unmasks field values to the admin.
 */
class PB2B_Address_Filter {

	/**
	 * Class constructor
	 */
	public function __construct() {
		add_filter( 'woocommerce_process_checkout_field_billing_first_name', array( $this, 'filter_pre_checked_value' ) );
		add_filter( 'woocommerce_process_checkout_field_billing_last_name', array( $this, 'filter_pre_checked_value' ) );
		add_filter( 'woocommerce_process_checkout_field_billing_address_1', array( $this, 'filter_pre_checked_value' ) );
		add_filter( 'woocommerce_process_checkout_field_billing_address_2', array( $this, 'filter_pre_checked_value' ) );
		add_filter( 'woocommerce_process_checkout_field_billing_postcode', array( $this, 'filter_pre_checked_value' ) );
		add_filter( 'woocommerce_process_checkout_field_billing_city', array( $this, 'filter_pre_checked_value' ) );
		add_filter( 'woocommerce_process_checkout_field_billing_company', array( $this, 'filter_pre_checked_value' ) );
		add_filter( 'woocommerce_default_address_fields', array( $this, 'override_checkout_check' ) );
	}

	/**
	 * Return values without '***' masks.
	 *
	 * @param [type] $value Field value.
	 * @return string
	 */
	public function filter_pre_checked_value( $value ) {
		$current_filter = current_filter();
		$current_field  = str_replace( array( 'woocommerce_process_checkout_field_billing_' ), '', $current_filter );
		if ( strpos( $value, '**' ) !== false ) {
			$customer_details = WC()->session->get( 'pb2b_customer_details' );

			if ( isset( $customer_details[ $current_field ] ) && '' !== $customer_details[ $current_field ] ) {
				return $customer_details[ $current_field ];
			} else {
				return $value;
			}
		} else {
			return $value;
		}
		return $value;
	}

	/**
	 * Override the checkout check.
	 *
	 * @param array $address_fields_array The value of address fields.
	 * @return array
	 */
	public function override_checkout_check( $address_fields_array ) {
		$chosen_payment_method = WC()->session->chosen_payment_method;
		if ( strpos( $chosen_payment_method, 'payer_b2b' ) !== false ) {
			unset( $address_fields_array['postcode']['validate'] );
		}
		return $address_fields_array;
	}


} new PB2B_Address_Filter();
