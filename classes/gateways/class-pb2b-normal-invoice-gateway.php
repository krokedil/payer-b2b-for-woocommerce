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
class PB2B_Normal_Invoice_Gateway extends PB2B_Factory_Gateway {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->id                 = 'payer_b2b_normal_invoice';
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
		$this->credit                = $this->get_option( 'automatic_credit_check' );

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
		if ( 'yes' !== $this->enabled ) {
			return false;
		}

		if ( ! in_array( get_woocommerce_currency(), array( 'DKK', 'EUR', 'GBP', 'NOK', 'SEK', 'USD' ), true ) ) {
			return false;
		}

		if ( ( ! empty( WC()->session ) && property_exists( WC(), 'session' ) )
			&& ( ( empty( WC()->session->get( 'pb2b_credit_decision' ) ) || 'APPROVED' !== WC()->session->get( 'pb2b_credit_decision' ) )
			|| ( empty( WC()->session->get( 'pb2b_signup_status' ) ) || ! in_array( WC()->session->get( 'pb2b_signup_status' ), array( 'PENDING', 'COMPLETED' ) ) ) )
		) {
			return false;
		}

		return true;
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
		if ( $amount === $order->get_total() ) {
			// Full refund.
			$request  = new PB2B_Request_Credit_Invoice( $order_id );
			$response = $request->request();
			if ( is_wp_error( $response ) ) {
				$order->add_order_note( __( 'Full Refund request failed with Payer. Please try again.', 'payer-b2b-for-woocommerce' ) );
				return false;
			}
		} else {
			// Manual refund.
			$refund_data = PB2B_Credit_Data::get_refund_data( $order_id );

			if ( isset( $refund_data['manual_refund_data'] ) && ! empty( $refund_data['manual_refund_data'] ) ) {
				$request  = new PB2B_Request_Manual_Refund_Credit_Invoice( $order_id );
				$response = $request->request( $refund_data );

				if ( is_wp_error( $response ) ) {
					$order->add_order_note( __( 'Manual Refund request failed with Payer. Please try again.', 'payer-b2b-for-woocommerce' ) );
					return false;
				}
			}
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
			$customer_invoice_switch = isset( $this->customer_invoice_type ) ? 'yes' === $this->customer_invoice_type : false;
			$pno_text                = $b2b_default ? __( 'Organization Number', 'payer-b2b-for-woocommerce' ) : __( 'Personal Number', 'payer-b2b-for-woocommerce' );

			// Check if we need to have the switch checkbox for the PNO field.
			if ( $b2b_switch ) {
				?>
				<label style="padding-bottom:15px;" for="payer_b2b_set_b2b"><?php esc_html_e( 'Business', 'payer-b2b-for-woocommerce' ); ?>?</label>
				<span style="padding:5px;" class="woocommerce-input-wrapper">
					<input type="checkbox" name="payer_b2b_set_b2b" id="payer_b2b_set_b2b" class="payer_b2b_set_b2b" <?php 'B2BC' === $this->customer_type ? esc_attr_e( 'checked', 'payer-b2b-for-woocommerce' ) : ''; ?> />
				</span>
				<?php
			}
			?>
			<br>
			<?php
			if ( $customer_invoice_switch ) {
				?>
				<p class="form-row validate-required form-row-wide" id="payer_b2b_invoice_type_field">
					<label id="payer_b2b_invoice_type_label" for="payer_b2b_invoice_type"><?php esc_html_e( 'Invoice type', 'payer-b2b-for-woocommerce' ); ?></label>
					<span class="woocommerce-input-wrapper">
						<select name="payer_b2b_invoice_type" id="payer_b2b_invoice_type" class="payer_b2b_invoice_type">
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
				<div class="signatory_wrapper" style="<?php $b2b_default ? esc_attr_e( 'display:block' ) : esc_attr_e( 'display:none' ); ?>">
					<label style="padding-bottom:5px;" for="payer_b2b_signatory"><?php esc_html_e( 'Separate signatory', 'payer-b2b-for-woocommerce' ); ?>?</label>
					<span style="padding:10px;" class="woocommerce-input-wrapper">
						<input type="checkbox" name="payer_b2b_signatory" id="payer_b2b_signatory" class="payer_b2b_signatory"/>
					</span>
				</div>
				<?php
			}

			// Check if we want to add the signatory field.
			if ( $b2b_enabled && 'yes' === $this->separate_signatory ) {
				?>
				<p class="form-row validate-required form-row-wide payer_b2b_signatory_text_field" style="display:none">
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
		$pno               = isset( $_POST[ PAYER_PNO_FIELD_NAME ] ) ? sanitize_text_field( $_POST[ PAYER_PNO_FIELD_NAME ] ) : '';
		$signatory         = isset( $_POST['payer_b2b_signatory_text'] ) ? sanitize_text_field( $_POST['payer_b2b_signatory_text'] ) : '';
		$invoice_type	   = isset( $_POST['payer_b2b_invoice_type'] ) ? sanitize_text_field( $_POST['payer_b2b_invoice_type'] ) : '';
		$created_via_admin = isset( $_POST['pb2b-create-invoice-order'] ) ? true : false;
		// @codingStandardsIgnoreEnd

		// Check if we need to create a payer order or not.
		if ( ! empty( get_post_meta( $order_id, '_payer_order_id', true ) ) ) {
			$create_payer_order = false;
		}

		if ( 0 >= $order->get_total() ) {
			$create_payer_order = false;
		}

		// Check if we want to create an order.
		if ( empty( $pno ) ) {
			if ( $created_via_admin ) {
				$order->add_order_note( __( 'Please enter a valid Personal number or Organization number', 'payer-b2b-for-woocommerce' ) );
				return;
			} else {
				wc_add_notice( __( 'Please enter a valid Personal number or Organization number', 'payer-b2b-for-woocommerce' ) );
				return;
			}
		}

		// Add invoice type to the order if it exists.
		if ( ! empty( $invoice_type ) ) {
			update_post_meta( $order_id, 'pb2b_invoice_type', $invoice_type );
		}
		update_post_meta( $order_id, PAYER_PNO_DATA_NAME, $pno );
		update_post_meta( $order_id, '_payer_signup_credit_decision', WC()->session->get( 'pb2b_credit_decision' ) );
		update_post_meta( $order_id, '_payer_signup_status', WC()->session->get( 'pb2b_signup_status' ) );
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

			if ( $created_via_admin && is_wp_error( $response ) ) {
				$error_message = wp_json_encode( $response->errors );
				$order->add_order_note( $error_message );
				wc_print_notice( $error_message, 'error' );
				return array(
					'result' => 'error',
				);
			}

			if ( is_wp_error( $response ) || ! isset( $response['referenceId'] ) ) {
				return false;
			}

			// Customer credit check.
			if ( 'yes' === $this->credit || false === $this->credit ) {
				payer_b2b_make_credit_check( $order_id );
			}

			update_post_meta( $order_id, '_payer_order_id', sanitize_key( $response['orderId'] ) );
			update_post_meta( $order_id, '_payer_reference_id', sanitize_key( $response['referenceId'] ) );
			update_post_meta( $order_id, '_payer_customer_type', sanitize_key( 'on' === filter_input( INPUT_POST, 'payer_b2b_set_b2b', FILTER_SANITIZE_STRING ) ? 'B2B' : 'B2C' ) );

			if ( in_array( WC()->session->get( 'pb2b_signup_status' ), array( 'PENDING', 'MANUAL_CONTROL' ) ) ) {
				$order->set_status( 'on-hold', __( 'Signup status was not COMPLETED, the actual status is ', 'payer-b2b-for-woocommerce' ) . WC()->session->get( 'pb2b_signup_status' ) );
			} else {
				$order->payment_complete( $response['orderId'] );
			}
			$order->add_order_note( __( 'Payment made with Payer', 'payer-b2b-for-woocommerce' ) );
		} else {
			if ( in_array( WC()->session->get( 'pb2b_signup_status' ), array( 'PENDING', 'MANUAL_CONTROL' ) ) ) {
				$order->set_status( 'on-hold', __( 'Signup status was not COMPLETED, the actual status is ', 'payer-b2b-for-woocommerce' ) . WC()->session->get( 'pb2b_signup_status' ) );
			} else {
				$order->payment_complete();
			}
			if ( class_exists( 'WC_Subscriptions' ) && wcs_order_contains_subscription( $order ) && 0 >= $order->get_total() ) {
				$order->add_order_note( __( 'Free subscription order. No order created with Payer', 'payer-b2b-for-woocommerce' ) );
			}
		}
		if ( ! $created_via_admin ) {
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}
}

/**
 * Add Payer_B2B 2.0 payment gateway
 *
 * @wp_hook woocommerce_payment_gateways
 * @param  array $methods All registered payment methods.
 * @return array $methods All registered payment methods.
 */
function add_payer_b2b_normal_invoice_method( $methods ) {
	$methods[] = 'PB2B_Normal_Invoice_Gateway';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_payer_b2b_normal_invoice_method' );
