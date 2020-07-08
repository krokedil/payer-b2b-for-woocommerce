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
	 * Shows the snippet on the thankyou page.
	 *
	 * @param string $order_id The WooCommerce order id.
	 * @return void
	 */
	public function thankyou_page( $order_id ) {
		// Unset sessions.
	}


	/**
	 * Adds Personalnumber field to checkout.
	 *
	 * @param array $fields Generated personal number input fields.
	 * @return array $fields
	 */
	public function add_personal_number_field( $fields ) {
		$settings = get_option( 'woocommerce_payer_card_payment_settings' );
		if ( 'yes' !== $settings['get_address'] ) {
			$fields['billing'][ PAYER_PNO_FIELD_NAME ] = array(
				'label'       => apply_filters( 'payer_pno_label', __( 'Personal number', 'payer-for-woocommerce' ) ),
				'placeholder' => _x( 'xxxxxx-xxxx', 'placeholder', 'payer-for-woocommerce' ),
				'required'    => false,
				'class'       => array( 'form-row-wide' ),
				'clear'       => true,
			);
		}
		return $fields;
	}
}
