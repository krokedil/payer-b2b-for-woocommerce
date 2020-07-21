<?php
/**
 * Handles AJAX calls.
 *
 *  @package  Payer/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Payer Ajax class.
 *
 * @class    Payer_Ajax
 * @package  Payer/Classes
 * @category Class
 * @author   Krokedil <info@krokedil.se>
 */
class PB2B_Ajax extends WC_AJAX {
	/**
	 * Initiatie the class.
	 *
	 * @return void
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Adds ajax events.
	 *
	 * @return void
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'get_address'              => true,
			'instant_product_purchase' => true,
			'instant_cart_purchase'    => true,
			'pb2b_credit_check'        => false,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
				// WC AJAX can be used for frontend ajax requests.
				add_action( 'wc_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

		/**
		 * Get Address Ajax event.
		 *
		 * @return void
		 */
	public static function get_address() {

		if ( isset( $_POST['get_address_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['get_address_nonce'] ), 'get_address_nonce' ) ) {

			$personal_number = $_POST['personal_number'];
			if ( '' !== $personal_number ) {
				if ( '' !== $_POST['zip_code'] ) {
					$zip_code = $_POST['zip_code'];
				} else {
					$zip_code = 12345;
				}

				$payer_address_information = new PB2B_Request_Get_Address();

				$zip_code = $_POST['zip_code'];
				$country  = $_POST['country'];
				$pno      = $_POST['personal_number'];

				$payer_address_information = $payer_address_information->request( $country, $pno, $zip_code );

				if ( is_wp_error( $payer_address_information ) ) {
					$return = array(
						'message' => __( 'Invalid request, please check the information or fill in the address manually.', 'payer-for-woocommerce' ),
					);
					wp_send_json_error( $return );
					wp_die();
				} else {
					self::set_address( $payer_address_information );
					$return = array(
						'address_information' => $payer_address_information,
						'message'             => __( 'Address found and added to the checkout form', 'payer-for-woocommerce' ),
					);
					wp_send_json_success( $return );
					wp_die();
				}
			} else {
				$return = array(
					'message' => __( 'Please fill in the personal number field', 'payer-for-woocommerce' ),
				);
				wp_send_json_error( $return );
				wp_die();
			}
		} else {
			wp_send_json_error( 'Bad request' );
			wp_die();
		}
	}

	/**
	 * Sets address as session.
	 *
	 * @param array $payer_address_information Array containing information from address fields.
	 *
	 * @return void
	 */
	private static function set_address( $payer_address_information ) {
		$payer_customer_details = array(
			'first_name' => $payer_address_information['firstName'],
			'last_name'  => $payer_address_information['lastName'],
			'address_1'  => $payer_address_information['streetAddress1'],
			'address_2'  => $payer_address_information['streetAddress2'],
			'company'    => $payer_address_information['coAddress'],
			'city'       => $payer_address_information['city'],
		);

		WC()->session->set( 'payer_customer_details', $payer_customer_details );
	}

	/**
	 * Perform credit check on a customer
	 *
	 * @return void
	 */
	public static function pb2b_credit_check() {
		if ( isset( $_GET['credit_check_nonce'] ) && wp_verify_nonce( sanitize_key( $_GET['credit_check_nonce'] ), 'credit_check_nonce' ) && current_user_can( 'edit_shop_order', $_GET['order_id'] ) ) {
			payer_b2b_make_credit_check( $_GET['order_id'] );
			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=shop_order' ) );
		}
	}

}
PB2B_Ajax::init();

