<?php
/**
 * Payer B2B checkout page
 *
 * Overrides /checkout/form-checkout.php.
 *
 * @package  Payer/Templates
 */

wc_print_notices();

do_action( 'woocommerce_before_checkout_form', WC()->checkout() );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

$settings = get_option( 'woocommerce_pb2b_settings' );
?>

<form name="checkout" class="checkout woocommerce-checkout">
<?php do_action( 'pb2b_wc_before_wrapper' ); ?>
	<div id="pb2b-wrapper">

		<div id="pb2b-order-review">
			<?php do_action( 'pb2b_wc_before_order_review' ); ?>
			<?php woocommerce_order_review(); ?>
			<?php do_action( 'pb2b_wc_after_order_review' ); ?>
		</div>
		<div id="pb2b-iframe">
			<?php do_action( 'pb2b_wc_before_snippet' ); ?>
			<?php payer_b2b_show_shippet(); ?>
			<?php do_action( 'pb2b_wc_after_snippet' ); ?>
		</div>
	</div>
	<?php do_action( 'pb2b_wc_after_wrapper' ); ?>
</form>

<?php do_action( 'woocommerce_after_checkout_form', WC()->checkout() ); ?>