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
			if ( in_array( $order->get_payment_method(), array( 'payer_b2b_v1_invoice', 'payer_b2b_v2_invoice' ), true ) && get_post_meta( $order_id, '_payer_invoice_number' ) ) {
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

		$invoice_number    = get_post_meta( $order_id, '_payer_invoice_number', true );
		$invoice_url       = get_post_meta( $order_id, '_payer_public_url', true );
		$invoice_ocr       = get_post_meta( $order_id, '_payer_ocr', true );
		$invoice_pno       = get_post_meta( $order_id, PAYER_PNO_DATA_NAME, true );
		$invoice_signatory = get_post_meta( $order_id, '_payer_signatory' ) ? get_post_meta( $order_id, '_payer_signatory', true ) : false;
		?>
		<div class="pb2b-modal-wrapper pb2b-hide-iframe" id="pb2b-modal-wrapper">
			<div class="pb2b-modal-content">
				<a class="pb2b-close-modal" id="pb2b-close-modal"href="#">X</a>
				<iframe width="425" height="575" id="pb2b-iframe" src="<?php echo esc_html( $invoice_url ); ?>" width="auto" height="auto"></iframe>
			</div>
		</div>
		<b><?php esc_html_e( 'PNO/Org number:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_pno ); ?><br>
		<b><?php esc_html_e( 'Invoice Number:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_number ); ?><br>
		<?php if ( 'payer_b2b_v2_invoice' === $order->get_payment_method() ) { ?>
		<b><?php esc_html_e( 'OCR Number:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_ocr ); ?><br>
		<?php } ?>
		<?php if ( $invoice_signatory ) { ?>
		<b><?php esc_html_e( 'Signatory:', 'payer-b2b-for-woocommerce' ); ?> </b> <?php echo esc_html( $invoice_signatory ); ?><br>
		<?php } ?>
		<br>
		<button type="button" id="pb2b-show-invoice" class="button button-primary"><?php esc_html_e( 'Show Invoice', 'payer-b2b-for-woocommerce' ); ?></button>
		<?php
	}
} new PB2B_Meta_Box();
