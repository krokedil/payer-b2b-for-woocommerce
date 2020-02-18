<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Shows credential form on settings page.
 */
function payer_b2b_show_credentials_form() {
	$payer_invoice_v1_settings = get_option( 'woocommerce_payer_b2b_v1_invoice_settings' );

	// Setting values.
	$agent_id = $payer_invoice_v1_settings['agent_id'];
	$api_key  = $payer_invoice_v1_settings['api_key'];
	?>
	<h2><?php esc_html_e( 'Payer B2B credentials:', 'payer-b2b-for-woocommerce' ); ?></h2>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="agent_id"><?php esc_html_e( 'Agent ID: ', 'payer-b2b-for-woocommerce' ); ?></label>
				</th>
				<td class="forminp">
					<input class="input-text regular-input" type="text" value="<?php echo esc_attr( $agent_id ); ?>" name="agent_id" disabled></br>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="api_key"><?php esc_html_e( 'Soap ID: ', 'payer-b2b-for-woocommerce' ); ?></label>
				</th>
				<td class="forminp">
					<input class="input-text regular-input" type="text" value="<?php echo esc_attr( $api_key ); ?>" name="api_key" disabled></br>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}
