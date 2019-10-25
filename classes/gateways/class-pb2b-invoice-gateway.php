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
class PB2B_Invoice_Gateway extends PB2B_Factory_Gateway {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->id                 = 'payer_b2b_invoice';
		$this->method_title       = __( 'Payer B2B Invoice', 'payer-b2b-for-woocommerce' );
		$this->icon               = '';
		$this->method_description = __( 'Allows payments through ' . $this->method_title . '.', 'payer-b2b-for-woocommerce' ); // phpcs:ignore

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->enabled            = $this->get_option( 'enabled' );
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->debug              = $this->get_option( 'debug' );
		$this->customer_type      = $this->get_option( 'allowed_customer_types' );
		$this->separate_signatory = $this->get_option( 'separate_signatory' );
		$this->enable_all_fields  = $this->get_option( 'enable_all_fields' );
		// Supports.
		$this->supports = array(
			'products',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_admin',
			'multiple_subscriptions',
		);

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 *
	 * @return boolean
	 */
	public function is_available() {
		if ( 'yes' === $this->enabled ) {
			if ( in_array( get_woocommerce_currency(), array( 'DKK', 'EUR', 'GBP', 'NOK', 'SEK', 'USD' ), true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Process refund request.
	 *
	 * @param string $order_id The WooCommerce order ID.
	 * @param float  $amount The amount to be refunded.
	 * @param string $reasson The reasson given for the refund.
	 * @return void
	 */
	public function process_refund( $order_id, $amount = null, $reasson = '' ) {
		$order = wc_get_order( $order_id );
		// Run logic here.
	}

	/**
	 * Adds the fields to the payment box in WooCommerce.
	 *
	 * @return void
	 */
	public function payment_fields() {
		if ( 'yes' === $this->enable_all_fields ) {
			// Set the needed variables.
			$b2b_enabled = in_array( $this->customer_type, array( 'B2B', 'B2CB', 'B2BC' ), true );
			$b2b_switch  = in_array( $this->customer_type, array( 'B2CB', 'B2BC' ), true );
			$b2b_default = in_array( $this->customer_type, array( 'B2B', 'B2BC' ), true );
			$pno_text    = $b2b_default ? __( 'Organisation Number', 'payer-b2b-for-woocommerce' ) : __( 'Personal Number', 'payer-b2b-for-woocommerce' );

			// Check if we need to have the switch checkbox for the PNO field.
			if ( $b2b_switch ) {
			?>
				<label style="padding-bottom:15px;" for="payer_b2b_set_b2b"><?php esc_html_e( 'Bussiness', 'payer-b2b-for-woocommerce' ); ?>?</label>
				<span style="padding:5px;" class="woocommerce-input-wrapper">
					<input type="checkbox" name="payer_b2b_set_b2b" id="payer_b2b_set_b2b" <?php 'B2BC' === $this->customer_type ? esc_attr_e( 'checked', 'payer-b2b-for-woocommerce' ) : ''; ?> />
				</span>
			<?php
			}
			?>
			<p class="form-row validate-required form-row-wide" id="payer_b2b_pno_field">
				<label id="payer_b2b_pno_label" for="payer_b2b_pno"><?php echo esc_html( $pno_text ); ?></label>
				<span class="woocommerce-input-wrapper">
					<input type="text" name="<?php echo esc_attr( PAYER_PNO_FIELD_NAME ); ?>" id="payer_b2b_pno"/>
				</span>
			</p>
			<br>
			<?php

			// Check if we need the switch checkbox for signatory.
			if ( $b2b_switch && 'yes' === $this->separate_signatory ) {
			?>
				<div id="signatory_wrapper" style="<?php $b2b_default ? esc_attr_e( 'display:block' ) : esc_attr_e( 'display:none' ); ?>">
					<label style="padding-bottom:5px;" for="payer_b2b_signatory"><?php esc_html_e( 'Separate signatory', 'payer-b2b-for-woocommerce' ); ?>?</label>
					<span style="padding:10px;" class="woocommerce-input-wrapper">
						<input type="checkbox" name="payer_b2b_signatory" id="payer_b2b_signatory"/>
					</span>
				</div>
			<?php
			}

			// Check if we want to add the signatory field.
			if ( $b2b_enabled && 'yes' === $this->separate_signatory ) {
			?>
				<p class="form-row validate-required form-row-wide" id="payer_b2b_signatory_text_field" style="display:none">
					<label for="payer_b2b_signatory_text"><?php esc_html_e( 'Signatory name', 'payer-b2b-for-woocommerce' ); ?></label>
					<span class="woocommerce-input-wrapper">
						<input type="text" name="payer_b2b_signatory_text" id="payer_b2b_signatory_text"/>
					</span>
				</p>
			<?php
			}
		}
	}

	/**
	 * Processes the WooCommerce Payment
	 *
	 * @param string $order_id The WooCommerce order ID.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order              = wc_get_order( $order_id );
		$create_payer_order = true;
		if ( class_exists( 'WC_Subscriptions' ) && wcs_order_contains_subscription( $order ) && 0 <= $order->get_total() ) {
			$create_payer_order = false;
		}
		// Check if we want to create an order.
		if ( $create_payer_order ) {
			// @codingStandardsIgnoreStart
			update_post_meta( $order_id, PAYER_PNO_DATA_NAME, $_POST[ PAYER_PNO_FIELD_NAME ] );
			if ( isset( $_POST['payer_b2b_signatory'] ) ) {
				update_post_meta( $order_id, '_payer_signatory', $_POST['payer_b2b_signatory'] );
			}
			$args = array(
				'b2b'             => isset( $_POST['payer_b2b_set_b2b'] ),
				'pno_value'       => $_POST[ PAYER_PNO_FIELD_NAME ],
				'signatory_value' => isset( $_POST['payer_b2b_signatory_text'] ) ? $_POST['payer_b2b_signatory_text'] : '',
			);
			// @codingStandardsIgnoreEnd
			$request  = new PB2B_Request_Create_Order( $order_id, $args );
			$response = $request->request();

			if ( is_wp_error( $response ) || ! isset( $response['referenceId'] ) ) {
				return false;
			}

			update_post_meta( $order_id, '_payer_order_id', $response['orderId'] );
			update_post_meta( $order_id, '_payer_reference_id', $response['referenceId'] );
			$order->payment_complete( $response['orderId'] );
			$order->add_order_note( __( 'Payment made with Payer', 'payer-b2b-for-woocommerce' ) );
		} else {
			$order->payment_complete();
			$order->add_order_note( __( 'Free subscription order. No order created with Payer', 'payer-b2b-for-woocommerce' ) );
		}
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}
}

/**
 * Add Payer_B2B 2.0 payment gateway
 *
 * @wp_hook woocommerce_payment_gateways
 * @param  array $methods All registered payment methods.
 * @return array $methods All registered payment methods.
 */
function add_payer_b2b_invoice_method( $methods ) {
	$methods[] = 'PB2B_Invoice_Gateway';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_payer_b2b_invoice_method' );
