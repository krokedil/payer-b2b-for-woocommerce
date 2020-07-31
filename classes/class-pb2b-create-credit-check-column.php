<?php
/**
 * Payer create credit check column class.
 *
 * @package Payer_B2B/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks customer credit score
 */
class PB2B_Create_Credit_Check_Column {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'create_credit_check_column' ) );

		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'create_credit_check_column_content' ), 10, 2 );
	}

	/**
	 * Create column in the Orders page.
	 *
	 * @param string $columns Column array.
	 * @return string
	 */
	public function create_credit_check_column( $columns ) {
		$columns['credit_status'] = 'Credit status';

		return $columns;
	}

	/**
	 * Create column content.
	 *
	 * @param string $column Credit status column.
	 * @param int    $order_id The order ID.
	 * @return void
	 */
	public function create_credit_check_column_content( $column, $order_id ) {

		if ( 'credit_status' === $column ) {
			$order = wc_get_order( $order_id );

			if ( ! in_array( $order->get_payment_method(), array( 'payer_b2b_prepaid_invoice', 'payer_b2b_normal_invoice' ) ) ) {
				return;
			}

			$customer_credit_check = get_post_meta( $order_id, '_payer_credit_check_result', true );
			if ( 'PASSED' === $customer_credit_check ) {
				?>

						<div class="pb2b-icon-passed">
							<span class="pb2b-credit-passed dashicons dashicons-yes woocommerce-help-tip" data-tip="PASSED"></span>
						</div>

				<?php

			} elseif ( 'FAILED' === $customer_credit_check ) {
				?>

						<div class="pb2b-icon-failed">
							<span class="pb2b-credit-failed dashicons dashicons-no woocommerce-help-tip" data-tip="FAILED"></span>
						</div>

				<?php
			} else {
					$url = add_query_arg(
						array(
							'action'             => 'woocommerce_pb2b_credit_check',
							'order_id'           => $order_id,
							'credit_check_nonce' => wp_create_nonce( 'credit_check_nonce' ),
						),
						admin_url( 'admin-ajax.php' )
					);
				?>

						<div class="pb2b-icon-perform-check" >
							<a href="<?php echo esc_html( $url ); ?>" id="pb2b-run-credit-check" name="pb2b-run-credit-check-value">
							<span class="pb2b-credit-check dashicons dashicons-plus woocommerce-help-tip" data-tip="Check credit status"></span>
							</a>
						</div>

				<?php
			}
		}
	}

} new PB2B_Create_Credit_Check_Column();
