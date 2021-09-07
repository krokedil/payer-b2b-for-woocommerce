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
class PB2B_Factory_Gateway extends WC_Payment_Gateway {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_checkout_fields', array( $this, 'add_personal_number_field' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'thankyou_page' ) );
		$this->has_fields = true;
	}

	/**
	 * Process refund request.
	 *
	 * @param string $order_id The WooCommerce order ID.
	 * @param float  $amount The amount to be refunded.
	 * @param string $reason The reason given for the refund.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );
		// Run logic here.
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = include PAYER_B2B_PATH . '/includes/pb2b-factory-settings.php';
	}

	/**
	 * Unset sessions on thankyou pages.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return void
	 */
	public function thankyou_page( $order_id ) {
		// Unset sessions.
		WC()->session->__unset( 'pb2b_onboarding_skd_url' );
		WC()->session->__unset( 'pb2b_onboarding_client_token' );
		WC()->session->__unset( 'pb2b_onboarding_session_id' );
		WC()->session->__unset( 'pb2b_customer_details' );
		WC()->session->__unset( 'pb2b_credit_decision' );
	}

	/**
	 * Adds Personalnumber field to checkout.
	 *
	 * @param array $fields Generated personal number input fields.
	 * @return array $fields
	 */
	public function add_personal_number_field( $fields ) {
		$settings    = get_option( 'woocommerce_payer_b2b_normal_invoice_settings' );
		$b2b_default = in_array( $this->customer_type, array( 'B2B', 'B2BC' ), true );
		$pno_text    = $b2b_default ? __( 'Organisation Number', 'payer-b2b-for-woocommerce' ) : __( 'Personal Number', 'payer-b2b-for-woocommerce' );
		if ( 'yes' === $settings['enable_all_fields'] ) {
			$fields['billing'][ PAYER_PNO_FIELD_NAME ] = array(
				'label'       => $pno_text,
				'placeholder' => _x( 'xxxxxx-xxxx', 'placeholder', 'payer-for-woocommerce' ),
				'required'    => true,
				'class'       => array( 'form-row-wide' ),
				'clear'       => true,
			);
		}
		return $fields;
	}
}
