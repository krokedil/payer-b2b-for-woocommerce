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
class PB2B_Crate_Credit_Check_Column {
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
			$customer_credit_check = get_post_meta( $order_id, '_payer_credit_check_result', true );

			if ( 'PASSED' === $customer_credit_check ) {
				?>
					<div style="display: flex">
						<div class="pb2b-icon-passed" style="height: 28px; width: 28px; display: flex;" >
							<span style="color:green; font-size: 28px" class="pb2b-credit-failed dashicons dashicons-yes woocommerce-help-tip" data-tip="PASSED"></span>
						</div>
					</div>
				<?php

			} elseif ( 'FAILED' === $customer_credit_check ) {
				?>
					<div style="display: flex">
						<div class="pb2b-icon-failed" style = "height: 28px; width: 28px; display: flex;" >
							<span style="text-align: center; color:red ; font-size: 28px" class="pb2b-credit-failed dashicons dashicons-no woocommerce-help-tip" data-tip="FAILED"></span>
						</div>
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
					<div style="display: flex;">
						<div class="pb2b-icon-perform-check" style = "display:flex; height:28px; width:28px;" >
							<a href="<?php echo esc_html( $url ); ?>"
							id="pb2b-run-credit-check" name="pb2b-run-credit-check-value"
							style="padding:0px; margin:0px;  border:none;">
							<span style="font-size: 28px; color: deepskyblue;" class="pb2b-credit-check dashicons dashicons-plus woocommerce-help-tip" data-tip="Check credit status"></span>
							</a>
						</div>
					</div>
									<?php
			}
		}
	}

} new PB2B_Crate_Credit_Check_Column();
