<?php
/**
 * Payer signup class.
 *
 * @package Payer_B2B/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the signup feature for Payer
 */
class PB2B_Signup {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		$settings      = get_option( 'woocommerce_payer_b2b_normal_invoice_settings' );
		$this->enabled = ( isset( $settings['signup'] ) && 'yes' === $settings['signup'] ) ? true : false;
		add_filter( 'wc_get_template', array( $this, 'override_template' ), 999, 2 );
		add_action( 'pb2b_wc_after_wrapper', array( $this, 'add_wc_form' ), 10 );
		add_action( 'pb2b_wc_after_wrapper', array( $this, 'add_wc_payment_methods' ), 15 );
	}

	/**
	 * Replaces the standard checkout template with the Payer Signup iframe.
	 *
	 * @param string $template
	 * @param string $template_name
	 * @return string
	 */
	public function override_template( $template, $template_name ) {
		if ( is_checkout() && $this->enabled ) {
			// Don't display pb2b template if we have a cart that doesn't needs payment.
			if ( apply_filters( 'pb2b_check_if_needs_payment', true ) ) {
				if ( ! WC()->cart->needs_payment() ) {
					return $template;
				}
			}
			// Payer B2B.
			if ( 'checkout/form-checkout.php' === $template_name ) {
				if ( locate_template( 'woocommerce/payer-checkout.php' ) ) {
					$payer_checkout_template = locate_template( 'woocommerce/payer-checkout.php' );
				} else {
					$payer_checkout_template = PAYER_B2B_PATH . '/templates/payer-checkout.php';
				}
				$template = $payer_checkout_template;
			}
		}
		return $template;
	}

	/*
	 * Adds the WC form and other fields to the checkout page.
	 *
	 * @return void
	 */
	public function add_wc_form() {
		?>
		<div aria-hidden="true" id="pb2b-wc-form" style="position:absolute; top:-99999px; left:-99999px;">
			<?php do_action( 'woocommerce_checkout_billing' ); ?>
			<?php do_action( 'woocommerce_checkout_shipping' ); ?>
		</div>
		<?php
	}

	/**
	 * Adds the selector for payment methods in WooCommerce.
	 *
	 * @return void
	 */
	public function add_wc_payment_methods() {
		?>
		<div id="pb2b-payment-wrapper" style="display:none; clear:both;">
		<?php woocommerce_checkout_payment(); ?>
		</div>
		<?php
	}
} new PB2B_Signup();
