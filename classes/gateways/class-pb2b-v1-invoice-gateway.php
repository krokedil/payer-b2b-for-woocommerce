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
class PB2B_V1_Invoice_Gateway extends PB2B_Factory_Gateway {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->id                 = 'payer_b2b_v1_invoice';
		$this->method_title       = __( 'Payer B2B Invoice', 'payer-b2b-for-woocommerce' );
		$this->icon               = '';
		$this->method_description = __( 'Allows payments through ' . $this->method_title . '.', 'payer-b2b-for-woocommerce' ); // phpcs:ignore

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables.
		$this->enabled               = $this->get_option( 'enabled' );
		$this->title                 = $this->get_option( 'title' );
		$this->description           = $this->get_option( 'description' );
		$this->debug                 = $this->get_option( 'debug' );
		$this->customer_type         = $this->get_option( 'allowed_customer_types' );
		$this->separate_signatory    = $this->get_option( 'separate_signatory' );
		$this->enable_all_fields     = $this->get_option( 'enable_all_fields' );
		$this->default_invoice_type  = $this->get_option( 'default_invoice_type' );
		$this->customer_invoice_type = $this->get_option( 'customer_invoice_type' );
		// Supports.
		$this->supports = array(
			'products',
			'refunds',
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
	 * @param string $reason The reason given for the refund.
	 * @return bool
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );
		// Run logic here.
		$request  = new PB2B_Request_Credit_V1_Invoice( $order_id );
		$response = $request->request( $amount, $reason );
		if ( is_wp_error( $response ) ) {
			$order->add_order_note( __( 'Refund request failed with Payer. Please try again.', 'payer-b2b-for-woocommerce' ) );
			return false;
		}
		$order->add_order_note( wc_price( $amount ) . ' ' . __( 'refunded with Payer.', 'payer-b2b-for-woocommerce' ) );
		return true;
	}

	/**
	 * Adds the fields to the payment box in WooCommerce.
	 *
	 * @return void
	 */
	public function payment_fields() {
		if ( 'yes' === $this->enable_all_fields ) {
			// Set the needed variables.
			$b2b_enabled             = in_array( $this->customer_type, array( 'B2B', 'B2CB', 'B2BC' ), true );
			$b2b_switch              = in_array( $this->customer_type, array( 'B2CB', 'B2BC' ), true );
			$b2b_default             = in_array( $this->customer_type, array( 'B2B', 'B2BC' ), true );
			$customer_invoice_switch = isset( $this->customer_invoice_type ) ? $this->customer_invoice_type === 'yes' : false;
			$pno_text                = $b2b_default ? __( 'Organization Number', 'payer-b2b-for-woocommerce' ) : __( 'Personal Number', 'payer-b2b-for-woocommerce' );

			// Check if we need to have the switch checkbox for the PNO field.
			if ( $b2b_switch ) {
				?>
				<label style="padding-bottom:15px;" for="payer_b2b_set_b2b"><?php esc_html_e( 'Business', 'payer-b2b-for-woocommerce' ); ?>?</label>
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
			if ( $customer_invoice_switch ) {
				?>
				<p class="form-row validate-required form-row-wide" id="payer_b2b_invoice_type_field">
					<label id="payer_b2b_invoice_type_label" for="payer_b2b_invoice_type"><?php esc_html_e( 'Invoice type', 'payer-b2b-for-woocommerce' ); ?></label>
					<span class="woocommerce-input-wrapper">
						<select name="payer_b2b_invoice_type" id="payer_b2b_invoice_type">
							<option value="EMAIL" <?php 'EMAIL' === $this->default_invoice_type ? esc_html_e( 'selected' ) : ''; ?>>Email</option>
							<option value="PRINT" <?php 'PRINT' === $this->default_invoice_type ? esc_html_e( 'selected' ) : ''; ?>>Mail</option>
							<option value="PDF" <?php 'PDF' === $this->default_invoice_type ? esc_html_e( 'selected' ) : ''; ?>>PDF</option>
							<option value="EINVOICE" <?php 'EINVOICE' === $this->default_invoice_type ? esc_html_e( 'selected' ) : ''; ?>>E-Invoice</option>
						</select>
					</span>
				</p>
				<br>
				<?php
			}

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

		// @codingStandardsIgnoreStart // We can ignore this because Woo has already done a nonce check here.
		// Set and sanitize variables.
		$pno          = isset( $_POST[ PAYER_PNO_FIELD_NAME ] ) ? sanitize_text_field( $_POST[ PAYER_PNO_FIELD_NAME ] ) : '';
		$signatory    = isset( $_POST['payer_b2b_signatory_text'] ) ? sanitize_text_field( $_POST['payer_b2b_signatory_text'] ) : '';
		$invoice_type = isset( $_POST['payer_b2b_invoice_type'] ) ? sanitize_text_field( $_POST['payer_b2b_invoice_type'] ) : '';
		// @codingStandardsIgnoreEnd

		if ( class_exists( 'WC_Subscriptions' ) && wcs_order_contains_subscription( $order ) && 0 >= $order->get_total() ) {
			$create_payer_order = false;
		}

		// Check if we want to create an order.
		if ( empty( $pno ) ) {
			wc_add_notice( __( 'Please enter a valid Personal number or Organization number', 'payer-b2b-for-woocommerce' ) );
			return;
		}

		// Add invoice type to the order if it exists.
		if ( ! empty( $invoice_type ) ) {
			update_post_meta( $order_id, 'pb2b_invoice_type', $invoice_type );
		}
		update_post_meta( $order_id, PAYER_PNO_DATA_NAME, $pno );
		if ( $create_payer_order ) {

			if ( ! empty( $signatory ) ) {
				update_post_meta( $order_id, '_payer_signatory', $signatory );
			}
			$args     = array(
				'b2b'             => isset( $_POST['payer_b2b_set_b2b'] ), // phpcs:ignore
				'pno_value'       => $pno,
				'signatory_value' => $signatory,
			);
			$request  = new PB2B_Request_Create_Order( $order_id, $args );
			$response = $request->request();

			if ( is_wp_error( $response ) || ! isset( $response['referenceId'] ) ) {
				return false;
			}

			update_post_meta( $order_id, '_payer_order_id', sanitize_key( $response['orderId'] ) );
			update_post_meta( $order_id, '_payer_reference_id', sanitize_key( $response['referenceId'] ) );
			update_post_meta( $order_id, '_payer_customer_type', sanitize_key( $response['isB2B'] ? 'B2B' : 'B2C' ) );
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
function add_payer_b2b_v1_invoice_method( $methods ) {
	$methods[] = 'PB2B_V1_Invoice_Gateway';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_payer_b2b_v1_invoice_method' );
