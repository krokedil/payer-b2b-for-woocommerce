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
			if ( in_array( $order->get_payment_method(), array( 'payer_b2b_normal_invoice', 'payer_b2b_v2_invoice' ), true ) && ( ! empty( get_post_meta( $order_id, '_payer_invoice_number', true ) ) || empty( get_post_meta( $order_id, '_transaction_id', true ) ) ) ) {
				add_meta_box( 'kom_meta_box', __( 'Payer B2B', 'payer-b2b-for-woocommerce' ), array( $this, 'pb2b_meta_box_content' ), 'shop_order', 'side', 'core' );
			}
		}
	}
	/**
	 * Adds content for the Payer meta box.
	 *
	 * @return void
	 */
	public function pb2b_meta_box_content() {
		$order_id = get_the_ID();
		$order    = wc_get_order( $order_id );

		$invoice_number         = get_post_meta( $order_id, '_payer_invoice_number', true );
		$invoice_url            = get_post_meta( $order_id, '_payer_public_url', true );
		$invoice_ocr            = get_post_meta( $order_id, '_payer_ocr', true );
		$invoice_pno            = get_post_meta( $order_id, PAYER_PNO_DATA_NAME, true );
		$invoice_signatory      = get_post_meta( $order_id, '_payer_signatory' ) ? get_post_meta( $order_id, '_payer_signatory', true ) : false;
		$invoice_transaction_id = get_post_meta( $order_id, '_transaction_id', true );
		if ( $invoice_number ) {
			?>
			<?php if ( 'payer_b2b_v2_invoice' === $order->get_payment_method() || 'payer_b2b_normal_invoice' === $order->get_payment_method() ) { ?>
			<div class="pb2b-modal-wrapper pb2b-hide-iframe" id="pb2b-modal-wrapper">
				<div class="pb2b-modal-content">
					<div class="pb2b-close-modal" id="pb2b-close-modal"href="#"><span class="dashicons dashicons-dismiss"></span></div>
					<iframe width="425" height="580" id="pb2b-iframe" src="<?php echo esc_html( $invoice_url ); ?>" width="auto" height="auto"></iframe>
				</div>
			</div>
			<?php } ?>
			<b><?php esc_html_e( 'PNO/Org number:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_pno ); ?><br>
			<b><?php esc_html_e( 'Invoice Number:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_number ); ?><br>
				<?php if ( 'payer_b2b_v2_invoice' === $order->get_payment_method() || 'payer_b2b_normal_invoice' === $order->get_payment_method() ) { ?>
			<b><?php esc_html_e( 'OCR Number:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_ocr ); ?><br>
			<?php } ?>
				<?php if ( $invoice_signatory ) { ?>
			<b><?php esc_html_e( 'Signatory:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_signatory ); ?><br>
			<?php } if ( 'payer_b2b_v2_invoice' === $order->get_payment_method() || 'payer_b2b_normal_invoice' === $order->get_payment_method() ) { ?>
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
} new PB2B_Meta_Box();
