=== Payer B2B for WooCommerce ===
Contributors: payertech, krokedil, NiklasHogefjord
Tags: ecommerce, e-commerce, woocommerce, payer, checkout
Requires at least: 4.7
Tested up to: 5.3.1
Requires PHP: 5.6
Stable tag: trunk
WC requires at least: 3.0.0
WC tested up to: 3.8.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html


== DESCRIPTION ==
Payer for WooCommerce is a plugin that extends WooCommerce, allowing you to take payments via Payers payment methods.

= Get started =
To get started with Payer B2B you need to [sign up](https://www.payer.se/) for an account.

More information on how to get started can be found in the [plugin documentation](https://docs.krokedil.com/article/323-payer-introduction).


== INSTALLATION	 ==
1. Download the latest release zip file or install it directly via the plugins menu in WordPress Administration.
2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
3. Unzip and upload the entire plugin directory to your /wp-content/plugins/ directory.
4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
5. Go WooCommerce Settings --> Payment Gateways and configure your Payer settings.
6. Read more about the configuration process in the [plugin documentation](https://docs.krokedil.com/article/323-payer-introduction).


== Frequently Asked Questions ==

= Where can I find Payer B2B for WooCommerce documentation? =
For help setting up and configuring Payer B2B for WooCommerce please refer to our [documentation](https://docs.krokedil.com/article/323-payer-introduction).


== CHANGELOG ==
= 2020.02.12 		- Version 1.0.1 =
* Enhancement		- Improved the error handling in the request class.
* Enhancement		- Improved the activation and cancelation calls to Payer to unhook the update function as well.
* Fix				- Changed so we do not make an attempt at updating an order without a Payer Order ID.

= 2019.12.17 		- Version 1.0.0 =
* First release on Wordpress.org