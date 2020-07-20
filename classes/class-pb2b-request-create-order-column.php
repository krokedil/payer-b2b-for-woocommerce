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
			echo ( get_post_meta( $order_id, '_payer_credit_check_result', true ) );
		}
	}

} new PB2B_Crate_Credit_Check_Column();
