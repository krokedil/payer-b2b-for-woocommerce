=== Payer B2B for WooCommerce ===
Contributors: payertech, krokedil, NiklasHogefjord
Tags: ecommerce, e-commerce, woocommerce, payer, checkout
Requires at least: 4.7
Tested up to: 5.8.1
Requires PHP: 7.0
Stable tag: trunk
WC requires at least: 4.0.0
WC tested up to: 5.7.0
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
= 2021.09.30        - Version 2.2.1 =
* Fix               - Change endpoint for update calls to match the new order creation endpoint.

= 2021.09.27        - Version 2.2.0 =
* Enhancement       - Changed API endpoint for order creation to allow us to avoid rounding issues when sending prices to Payer.

= 2021.06.16        - Version 2.1.2 =
* Fix               - Fixed an issue that would trigger an update attempt when updating an order in WooCommerce that Payer has already completed.
* Fix               - Fixed not sending the PNO or Org number to Payer for Card payments.
* Tweak             - Remove UI Version from card payments.
* Tweak             - Change the endpoint for card refunds.
* Tweak             - Changed default value and name of some settings.

= 2021.02.18        - Version 2.1.1 =
* Fix               - Fixed an issue when a customer tried to complete a failed subscription payment using Payer B2B Card payments.
* Fix               - Fixed a logic error for the get address feature.

= 2020.12.11        - Version 2.1.0 =
* Feature           - Added feature to change invoice delivery type and due date length on orders and subscriptions placed with either invoice option.

= 2020.11.13        - Version 2.0.3 =
* Enhancement       - Allow customers to update the card used on a failed subscription purchase.
* Enhancement       - Improved refunds to prevent a mismatch on order line numbers for invoices.

= 2020.10.29        - Version 2.0.2 =
* Enhancement       - Added a filterable 10 second timeout for requests.
* Fix               - Correctly send the invoice delivery type to Payer.

= 2020.10.12        - Version 2.0.1 =
* Enhancement       - Added sending order lines on recurring payments with card payments.
* Enhancement       - Added tax calculations for fees.

= 2020.10.05        - Version 2.0.0 =
* Feature           - Moved all payment methods over to the v2 API.
* Feature           - Added address fetch functionality in the checkout.
* Feature           - Added automatic or manual credit checks for invoice payments.
* Feature           - Added metabox to be able to see the payment status for invoices and other information.
* Enhancement       - Improved logic on checkout to only attempt to create an order with Payer if it is needed.

= 2020.09.30        - Version 1.1.2 =
* Fix               - Fixed incorrect price calculation of fees when creating a Payer order.

= 2020.06.01        - Version 1.1.1 =
* Fix               - Able to create Payer Invoice from admin without having to add shipping address.

= 2020.04.22        - Version 1.1.0 =
* Feature           - Added card payment method.
* Feature           - Add subscription support for card payments.
* Fetaure           - Added feature to create Payer invoice from order admin view.
* Tweak             - Excluding unitPrice and unitVatAmount sent to Payer due to rounding issues.
* Tweak             - Only try to make a cancel request if order has been captured.
* Tweak             - Added support for multiple language codes in requests sent to Payer (for card payments).

= 2020.02.12        - Version 1.0.1 =
* Enhancement       - Improved the error handling in the request class.
* Enhancement       - Improved the activation and cancelation calls to Payer to unhook the update function as well.
* Fix               - Changed so we do not make an attempt at updating an order without a Payer Order ID.

= 2019.12.17        - Version 1.0.0 =
* First release on Wordpress.org
