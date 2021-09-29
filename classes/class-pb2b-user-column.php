<?php
/**
 * User column class file
 *
 * @package Payer_B2B/Classes
 */

/**
 * User column class.
 */
class PB2B_User_Column {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_filter( 'manage_users_columns', array( $this, 'add_onboarding_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'add_onboarding_column_content' ), 10, 3 );
	}

	/**
	 * Add onboarding session status column to user table.
	 *
	 * @param array $columns The columns in the user table.
	 * @return array
	 */
	public function add_onboarding_column( $columns ) {
		$columns['onboarding'] = 'Payer Onboarding Status';
		return $columns;
	}

	/**
	 * Undocumented function
	 *
	 * @since 1.0.0
	 *
	 * @param string $val The value.
	 * @param string $column_name The column name.
	 * @param int    $user_id The WordPress user id.
	 * @return string
	 */
	public function add_onboarding_column_content( $val, $column_name, $user_id ) {
		switch ( $column_name ) {
			case 'onboarding':
				$status = get_user_meta( $user_id, 'pb2b_onboarding_status', true );
				return ! empty( $status ) ? $status : 'N/A';
			default:
		}
		return $val;
	}
}
new PB2B_User_Column();
