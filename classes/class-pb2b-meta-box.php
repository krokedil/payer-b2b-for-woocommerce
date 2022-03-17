<?php
/**
 * Payer meta box class.
 *
 * @package Payer_B2B/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the meta box for Payer
 */
class PB2B_Meta_Box {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'pb2b_meta_box' ) );

		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'pb2b_maybe_check_credit' ), 45 );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'pb2b_maybe_set_invoice_data' ), 45 );

	}
	/**
	 * Adds meta box to the side of a Payer order
	 *
	 * @param string $post_type The WordPress post type.
	 * @return void
	 */
	public function pb2b_meta_box( $post_type ) {
		if ( 'shop_order' === $post_type ) {
			$order_id = get_the_ID();
			$order    = wc_get_order( $order_id );
			// Invoice created metabox metabox.
			if ( in_array( $order->get_payment_method(), array( 'payer_b2b_normal_invoice', 'payer_b2b_prepaid_invoice' ), true )
				&& ( ! empty( get_post_meta( $order_id, '_payer_invoice_number', true ) )
				|| empty( get_post_meta( $order_id, '_transaction_id', true ) ) ) ) {
				add_meta_box( 'pb2b_meta_box', __( 'Payer B2B', 'payer-b2b-for-woocommerce' ), array( $this, 'pb2b_meta_box_invoice_content' ), 'shop_order', 'side', 'core' );
				return;
			}
		}

		if ( 'shop_order' === $post_type || 'shop_subscription' === $post_type ) {
			$order_id = get_the_ID();
			$order    = 'shop_order' === $post_type ? wc_get_order( $order_id ) : wcs_get_subscription( $order_id );
			// Invoice not created metabox.
			if ( in_array( $order->get_payment_method(), array( 'payer_b2b_normal_invoice', 'payer_b2b_prepaid_invoice' ), true ) ) {
				add_meta_box( 'pb2b_meta_box', __( 'Payer B2B', 'payer-b2b-for-woocommerce' ), array( $this, 'pb2b_meta_box_no_invoice_content' ), 'shop_order', 'side', 'core' );
				add_meta_box( 'pb2b_meta_box', __( 'Payer B2B', 'payer-b2b-for-woocommerce' ), array( $this, 'pb2b_meta_box_no_invoice_content' ), 'shop_subscription', 'side', 'core' );
				return;
			}
		}
	}
	/**
	 * Adds content for the Payer meta box.
	 *
	 * @return void
	 */
	public function pb2b_meta_box_invoice_content() {

		$order_id = get_the_ID();
		$order    = wc_get_order( $order_id );

		$invoice_number         = get_post_meta( $order_id, '_payer_invoice_number', true );
		$invoice_url            = get_post_meta( $order_id, '_payer_public_url', true );
		$invoice_ocr            = get_post_meta( $order_id, '_payer_ocr', true );
		$invoice_pno            = get_post_meta( $order_id, PAYER_PNO_DATA_NAME, true );
		$invoice_signatory      = get_post_meta( $order_id, '_payer_signatory' ) ? get_post_meta( $order_id, '_payer_signatory', true ) : false;
		$invoice_transaction_id = get_post_meta( $order_id, '_transaction_id', true );
		$customer_credit_check  = get_post_meta( $order_id, '_payer_credit_check_result', true );

		if ( $invoice_ocr ) {
			$request                = new PB2B_Request_Get_Invoice( $order_id );
			$response               = $request->request( $invoice_number );
			$invoice_payment_status = $response['invoice']['paymentStatus'];
		}

		if ( $invoice_number ) {
			?>
			<?php if ( 'payer_b2b_prepaid_invoice' === $order->get_payment_method() || 'payer_b2b_normal_invoice' === $order->get_payment_method() ) { ?>
			<div class="pb2b-modal-wrapper pb2b-hide-iframe" id="pb2b-modal-wrapper">
				<div class="pb2b-modal-content">
					<div class="pb2b-close-modal" id="pb2b-close-modal"href="#"><span class="dashicons dashicons-dismiss"></span></div>
					<iframe width="425" height="580" id="pb2b-iframe" src="<?php echo esc_html( $invoice_url ); ?>" width="auto" height="auto"></iframe>
				</div>
			</div>
			<?php } ?>
			<b><?php esc_html_e( 'PNO/Org number:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_pno ); ?><br>
			<b><?php esc_html_e( 'Invoice Number:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_number ); ?><br>
			<?php if ( 'payer_b2b_prepaid_invoice' === $order->get_payment_method() || 'payer_b2b_normal_invoice' === $order->get_payment_method() ) { ?>
			<b><?php esc_html_e( 'OCR Number:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_ocr ); ?><br>
			<?php } ?>
			<b><?php esc_html_e( 'Payment Status:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_payment_status ); ?><br>

			<?php if ( $invoice_signatory ) { ?>
			<b><?php esc_html_e( 'Signatory:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_signatory ); ?><br>
			<?php } if ( 'payer_b2b_prepaid_invoice' === $order->get_payment_method() || 'payer_b2b_normal_invoice' === $order->get_payment_method() ) { ?>

			<b>
				<?php
				if ( $customer_credit_check ) {
					esc_html_e( 'Customer Credit Status:', 'payer-b2b-for-woocommerce' );
					?>
				</b>
					<?php
					echo esc_html( $customer_credit_check );
					echo '<br>';
				} else {
					?>
					<form method="post">
						<?php wp_nonce_field( 'get_credit_nonce', 'get_credit_nonce' ); ?>
						<br>
							<button type="submit" id="pb2b-run-credit-check" name="pb2b-run-credit-check-value" class="button button-primary">
								<?php esc_html_e( 'Credit Check', 'payer-b2b-for-woocommerce' ); ?>
							</button>
						<br>
					</form>
					<?php
				}
				?>
				<br>
			<button type="button" id="pb2b-show-invoice" class="button button-primary"><?php esc_html_e( 'Show Invoice', 'payer-b2b-for-woocommerce' ); ?></button>
					<?php
			}
		} elseif ( empty( $invoice_transaction_id ) ) {
			?>
			<input type="text" name="<?php echo esc_attr( PAYER_PNO_FIELD_NAME ); ?>" id="payer_b2b_pno" placeholder="<?php esc_attr_e( 'PNO/Org number', 'payer-b2b-for-woocommerce' ); ?>"/>
			<br><br>
			<input type="submit" id="pb2b-create-invoice-order" name="pb2b-create-invoice-order" class="button button-primary" value="<?php esc_attr_e( 'Create Invoice Order', 'payer-b2b-for-woocommerce' ); ?>">
			<?php
		}
	}

	/**
	 * Adds content for the second Payer meta box.
	 *
	 * @return void
	 */
	public function pb2b_meta_box_no_invoice_content() {
		$order_id = get_the_ID();
		$order    = wc_get_order( $order_id );
		$settings = 'payer_b2b_normal_invoice' === $order->get_payment_method() ? get_option( 'woocommerce_payer_b2b_normal_invoice_settings' ) : get_option( 'woocommerce_payer_b2b_prepaid_invoice_settings' );

		$invoice_length = ! empty( get_post_meta( $order_id, 'pb2b_invoice_length', true ) ) ? get_post_meta( $order_id, 'pb2b_invoice_length', true ) : 30;
		$type           = ! empty( get_post_meta( $order_id, 'pb2b_invoice_type', true ) ) ? get_post_meta( $order_id, 'pb2b_invoice_type', true ) : $settings['default_invoice_type'];
		?>
		<p>
			<label for="payer_b2b_invoice_length"><?php esc_html_e( 'Invoice length', 'payer-b2b-for-woocommerce' ); ?>
				<input type="number" id="payer_b2b_invoice_length" name="payer_b2b_invoice_length" value="<?php esc_attr_e( $invoice_length ); ?>" style="max-width:90px" />
			</label>
		</p>
		<p>
		<label for="payer_b2b_invoice_type"><?php esc_html_e( 'Invoice method', 'payer-b2b-for-woocommerce' ); ?>
			<select id="payer_b2b_invoice_type" name="payer_b2b_invoice_type" style="max-width:90px">
				<option value="PRINT" <?php selected( 'PRINT', $type ); ?>><?php esc_html_e( 'Mail', 'payer-b2b-for-woocommerce' ); ?></option>
				<option value="EMAIL" <?php selected( 'EMAIL', $type ); ?>><?php esc_html_e( 'Email', 'payer-b2b-for-woocommerce' ); ?></option>
				<option value="EINVOICE" <?php selected( 'EINVOICE', $type ); ?>><?php esc_html_e( 'E-Invoice', 'payer-b2b-for-woocommerce' ); ?></option>
				<option value="NONE" <?php selected( 'NONE', $type ); ?>><?php esc_html_e( 'None', 'payer-b2b-for-woocommerce' ); ?></option>
			</select>
		</label>
		<br><br>
		<?php wp_nonce_field( 'set_invoice_data_nonce', 'set_invoice_data_nonce' ); ?>
		<input type="submit" id="pb2b-set-invoice-data" name="pb2b-set-invoice-data" class="button button-primary" value="<?php esc_attr_e( 'Set Invoice data', 'payer-b2b-for-woocommerce' ); ?>">
		</p>
		<?php
	}

	/**
	 * Run a credit check on the customer
	 *
	 * @param int $order_id The order ID.
	 * @return void
	 */
	public function pb2b_maybe_check_credit( $order_id ) {
		if ( isset( $_POST['get_credit_nonce'] ) &&
		wp_verify_nonce( $_POST['get_credit_nonce'], 'get_credit_nonce' ) ) {

			if ( isset( $_POST['pb2b-run-credit-check-value'] ) && current_user_can( 'edit_shop_order' ) ) {
				payer_b2b_make_credit_check( $order_id );
			}
		}
	}

	/**
	 * Run a credit check on the customer
	 *
	 * @param int $order_id The order ID.
	 * @return void
	 */
	public function pb2b_maybe_set_invoice_data( $order_id ) {
		if ( isset( $_POST['set_invoice_data_nonce'] ) &&
		wp_verify_nonce( $_POST['set_invoice_data_nonce'], 'set_invoice_data_nonce' ) ) {

			if ( isset( $_POST['pb2b-set-invoice-data'] ) && current_user_can( 'edit_shop_order' ) ) {
				update_post_meta( $order_id, 'pb2b_invoice_length', $_POST['payer_b2b_invoice_length'] );
				update_post_meta( $order_id, 'pb2b_invoice_type', $_POST['payer_b2b_invoice_type'] );
			}
		}
	}
} new PB2B_Meta_Box();
