<?php // phpcs:ignore
/**
 * Plugin Name:     Payer B2B SignUp for WooCommerce
 * Plugin URI:      https://krokedil.com/products
 * Description:     Provides a Payer B2B gateway for WooCommerce.
 * Version:         2.2.5
 * Author:          Krokedil
 * Author URI:      https://krokedil.com/
 * Developer:       Krokedil
 * Developer URI:   https://krokedil.com/
 * Text Domain:     payer-b2b-for-woocommerce
 * Domain Path:     /languages
 *
 * WC requires at least: 3.5
 * WC tested up to: 6.1.1
 *
 * Copyright:       © 2016-2022 Krokedil.
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Payer_B2B
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'PAYER_B2B_VERSION', '2.2.5' );
define( 'PAYER_B2B_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'PAYER_B2B_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'PAYER_B2B_LIVE_ENV', 'https://b2b.payer.se' );
define( 'PAYER_B2B_TEST_ENV', 'https://stage-b2b.payer.se' );

if ( ! class_exists( 'Payer_B2B' ) ) {

	/**
	 * Main class for the plugin.
	 */
	class Payer_B2B {
		/**
		 * The reference the *Singleton* instance of this class.
		 *
		 * @var $instance
		 */
		protected static $instance;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			// Initiate the plugin.
			add_action( 'plugins_loaded', array( $this, 'init' ) );
			add_action( 'after_setup_theme', array( $this, 'set_defines' ) );
		}

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return self::$instance The *Singleton* instance.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Private clone method to prevent cloning of the instance of the
		 * *Singleton* instance.
		 *
		 * @return void
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}

		/**
		 * Private unserialize method to prevent unserializing of the *Singleton*
		 * instance.
		 *
		 * @return void
		 */
		private function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}

		/**
		 * Initiates the plugin.
		 *
		 * @return void
		 */
		public function init() {
			load_plugin_textdomain( 'payer-b2b-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

			$this->include_files();

			// Load scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );

			do_action( 'payer_initiated' );
		}

		/**
		 * Conditionaly set additional deffinitions.
		 *
		 * @return void
		 */
		public function set_defines() {
			// Set definitions.
			if ( ! defined( 'PAYER_PNO_FIELD_NAME' ) ) {
				define( 'PAYER_PNO_FIELD_NAME', apply_filters( 'payer_pno_field_name', 'payer_b2b_pno' ) );
			}
			if ( ! defined( 'PAYER_PNO_DATA_NAME' ) ) {
				define( 'PAYER_PNO_DATA_NAME', '_' . apply_filters( 'payer_pno_field_name', 'payer_b2b_pno' ) );
			}
		}

		/**
		 * Includes the files for the plugin.
		 *
		 * @return void
		 */
		public function include_files() {
			// Gateways.
			include_once PAYER_B2B_PATH . '/classes/gateways/class-pb2b-factory-gateway.php';
			include_once PAYER_B2B_PATH . '/classes/gateways/class-pb2b-normal-invoice-gateway.php';
			include_once PAYER_B2B_PATH . '/classes/gateways/class-pb2b-prepaid-invoice-gateway.php';
			include_once PAYER_B2B_PATH . '/classes/gateways/class-pb2b-card-gateway.php';

			// Requests.
			include_once PAYER_B2B_PATH . '/classes/requests/class-pb2b-request.php';
			// Post.
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-oauth.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-create-order.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-create-v2-invoice.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-create-direct-card.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-create-stored-card.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-capture-card-payment.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-refund-card-payment.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-authorize-payment.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-register-webhook.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-full-credit-invoice.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-partial-credit-invoice.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-manual-credit-invoice.php';
			include_once PAYER_B2B_PATH . '/classes/requests/post/class-pb2b-request-create-signup-session.php';
			// Put.
			include_once PAYER_B2B_PATH . '/classes/requests/put/class-pb2b-request-update-order.php';
			include_once PAYER_B2B_PATH . '/classes/requests/put/class-pb2b-request-approve-order.php';
			include_once PAYER_B2B_PATH . '/classes/requests/put/class-pb2b-request-release-card-payment.php';
			// Delete.
			include_once PAYER_B2B_PATH . '/classes/requests/delete/class-pb2b-request-delete-order.php';
			// Get.
			include_once PAYER_B2B_PATH . '/classes/requests/get/class-pb2b-request-get-payment.php';
			include_once PAYER_B2B_PATH . '/classes/requests/get/class-pb2b-request-get-stored-payment-status.php';
			include_once PAYER_B2B_PATH . '/classes/requests/get/class-pb2b-request-get-event-payload.php';
			include_once PAYER_B2B_PATH . '/classes/requests/get/class-pb2b-request-get-event-acknowledge.php';
			include_once PAYER_B2B_PATH . '/classes/requests/get/class-pb2b-request-get-address.php';
			include_once PAYER_B2B_PATH . '/classes/requests/get/class-pb2b-request-get-invoice.php';
			include_once PAYER_B2B_PATH . '/classes/requests/get/class-pb2b-request-get-credit-check.php';
			// Request helpers.
			include_once PAYER_B2B_PATH . '/classes/requests/helpers/class-pb2b-customer-data.php';
			include_once PAYER_B2B_PATH . '/classes/requests/helpers/class-pb2b-order-lines.php';
			include_once PAYER_B2B_PATH . '/classes/requests/helpers/class-pb2b-credit-data.php';

			// Classes.
			include_once PAYER_B2B_PATH . '/classes/class-pb2b-logger.php';
			include_once PAYER_B2B_PATH . '/classes/class-pb2b-order-management.php';
			include_once PAYER_B2B_PATH . '/classes/class-pb2b-subscriptions.php';
			include_once PAYER_B2B_PATH . '/classes/class-pb2b-meta-box.php';
			include_once PAYER_B2B_PATH . '/classes/class-pb2b-api-callbacks.php';
			include_once PAYER_B2B_PATH . '/classes/class-pb2b-ajax.php';
			include_once PAYER_B2B_PATH . '/classes/class-pb2b-order-columns.php';
			include_once PAYER_B2B_PATH . '/classes/class-pb2b-address-filter.php';
			include_once PAYER_B2B_PATH . '/classes/class-pb2b-signup.php';
			include_once PAYER_B2B_PATH . '/classes/class-pb2b-user-column.php';
			// Includes.
			include_once PAYER_B2B_PATH . '/includes/pb2b-functions.php';
			include_once PAYER_B2B_PATH . '/includes/pb2b-credentials-form-fields.php';
		}

		/**
		 * Adds plugin action links
		 *
		 * @param array $links Plugin action link before filtering.
		 * @return array Filtered links.
		 */
		public function plugin_action_links( $links ) {
			$setting_link = $this->get_setting_link();
			$plugin_links = array(
				'<a href="' . $setting_link . '">' . __( 'Settings', 'payer-b2b-for-woocommerce' ) . '</a>',
				'<a href="http://krokedil.se/">' . __( 'Support', 'payer-b2b-for-woocommerce' ) . '</a>',
			);
			return array_merge( $plugin_links, $links );
		}

		/**
		 * Get setting link.
		 *
		 * @return string Setting link
		 */
		public function get_setting_link() {
			$section_slug = 'payer_b2b_normal_invoice';
			return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $section_slug );
		}

		/**
		 * Loads the needed scripts for Payer_B2B.
		 */
		public function load_scripts() {
			if ( is_checkout() && ! is_order_received_page() ) {
				wp_register_script(
					'payer_wc',
					PAYER_B2B_URL . '/assets/js/payer_checkout.js',
					array( 'jquery' ),
					PAYER_B2B_VERSION,
					true
				);

				wp_register_style(
					'payer_wc',
					PAYER_B2B_URL . '/assets/css/payer_checkout.css',
					array(),
					PAYER_B2B_VERSION
				);

				$settings            = get_option( 'woocommerce_payer_b2b_normal_invoice_settings' );
				$get_address_enabled = isset( $settings['get_address'] ) ? ( ( 'yes' === $settings['get_address'] ) ? true : false ) : true;
				$signup_enabled      = ( isset( $settings['signup'] ) && 'yes' === $settings['signup'] ) ? true : false;

				$params = array(
					'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
					'b2c_text'                  => __( 'Personal Number', 'payer-b2b-for-woocommerce' ),
					'b2b_text'                  => __( 'Organisation Number', 'payer-b2b-for-woocommerce' ),
					'pno_name'                  => PAYER_PNO_FIELD_NAME,
					'get_address_enabled'       => $get_address_enabled,
					'get_address_text'          => __( 'Get address', 'payer-b2b-for-woocommerce' ),
					'get_address'               => WC_AJAX::get_endpoint( 'get_address' ),
					'get_address_nonce'         => wp_create_nonce( 'get_address_nonce' ),
					'set_credit_decision'       => WC_AJAX::get_endpoint( 'set_credit_decision' ),
					'set_credit_decision_nonce' => wp_create_nonce( 'set_credit_decision_nonce' ),
					'signup_enabled'            => $signup_enabled,
				);

				if ( $signup_enabled ) {
					$error = false;
					if ( WC()->session->get( 'pb2b_signup_client_token' ) && ! empty( WC()->session->get( 'pb2b_signup_expiry_time' ) ) && WC()->session->get( 'pb2b_signup_expiry_time' ) > time() ) {
						$sdk_url      = WC()->session->get( 'pb2b_signup_sdk_url' );
						$client_token = WC()->session->get( 'pb2b_signup_client_token' );
						$session_id   = WC()->session->get( 'pb2b_signup_session_id' );
					} else {
						$signup = new PB2B_Request_Create_Signup( array() );
						$signup = $signup->request();
						if ( ! is_wp_error( $signup ) ) {
							$sdk_url      = $signup['sdkUrl'];
							$client_token = $signup['clientToken'];
							$session_id   = $signup['sessionId'];
							WC()->session->set( 'pb2b_signup_sdk_url', $sdk_url );
							WC()->session->set( 'pb2b_signup_client_token', $client_token );
							WC()->session->set( 'pb2b_signup_session_id', $session_id );
							WC()->session->set( 'pb2b_signup_expiry_time', strtotime( '+55 minutes' ) );
						} else {
							$error = true;
						}
					}
					if ( ! $error ) {
						wp_register_script(
							'payer_signup',
							$sdk_url,
							array(),
							null,
							true
						);
						$params['client_token'] = $client_token;
					}
				}

				wp_localize_script(
					'payer_wc',
					'payer_wc_params',
					$params
				);
				wp_enqueue_script( 'payer_wc' );
				wp_enqueue_style( 'payer_wc' );
				wp_enqueue_script( 'payer_signup' );
			}
		}

		/**
		 * Loads admin scripts.
		 */
		public function load_admin_scripts() {
			wp_register_style( 'payer-b2b-admin', PAYER_B2B_URL . '/assets/css/payer_admin_style.css', false, PAYER_B2B_VERSION );
			wp_enqueue_style( 'payer-b2b-admin' );
			wp_register_script( 'payer-b2b-admin', PAYER_B2B_URL . '/assets/js/payer_admin.js', true, PAYER_B2B_VERSION, true );
			wp_enqueue_script( 'payer-b2b-admin' );
		}
	}
	Payer_B2B::get_instance();

	/**
	 * Main instance Payer_B2B_For_WooCommerce.
	 *
	 * Returns the main instance of Payer_B2B_For_WooCommerce.
	 *
	 * @return Payer_B2B
	 */
	function payer_WC() { // phpcs:ignore
		return Payer_B2B::get_instance();
	}
}
