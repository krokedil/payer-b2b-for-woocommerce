// Set contants and variables.
const targetNode = document.getElementById('order_review');

const init = function() {
	let signatoryStatus = 'hidden';
	// Add event listener for maybeSetB2B.
	const signatoryWrapper = document.getElementById('signatory_wrapper');
	const pnoFieldLabel = document.getElementById('payer_b2b_pno_label');
	const maybeSetB2B = document.getElementById('payer_b2b_set_b2b');
	if ( maybeSetB2B ) {
		maybeSetB2B.addEventListener('change', (maybeSetB2B) => {
			if ( maybeSetB2B.target.checked === true ) {
				pnoFieldLabel.innerHTML =  payer_wc_params.b2b_text;
				if ( signatoryWrapper ) {
					signatoryWrapper.style = 'display:block';
					if ( 'display' === signatoryStatus ) {
						signatoryField.style = 'display:block';
					}
				}
			} else {
				pnoFieldLabel.innerHTML = payer_wc_params.b2c_text;
				if ( signatoryWrapper ) {
					signatoryWrapper.style = 'display:none';
					if ( 'display' === signatoryStatus ) {
						signatoryField.style = 'display:none';
					}
				}
			}
		});
	}

	// Add event listener for toggleSignatoryField.
	const toggleSignatoryField = document.getElementById('payer_b2b_signatory');
	const signatoryField = document.getElementById('payer_b2b_signatory_text_field');
	if ( toggleSignatoryField ) {
		toggleSignatoryField.addEventListener('change', (toggleSignatoryField) => {
			if ( toggleSignatoryField.target.checked === true ) {
				signatoryField.style = 'display:block';
				signatoryStatus = 'display';
			} else {
				signatoryField.style = 'display:none';
				signatoryStatus = 'hidden';
			}		
		});
	}
}

/**
 * Callback for MutationObserver.
 * 
 * @param {*} mutationsList 
 * @param {*} observer 
 */
const callback = function(mutationsList, observer) {
    for ( var mutation of mutationsList ) {
        if ( mutation.type == 'childList' ) {
			init();
			break;
        }
    }
};
const observer = new MutationObserver(callback);
const config = { childList: true };
observer.observe( targetNode, config );


jQuery( function($)  {
	var payer_wc = {
		documentReady: function(){
			payer_wc.moveInputFields();
			payer_wc.addGetAddressButton();
		},

		moveInputFields: function() {
            var pno_field           = $('#' + payer_wc_params.pno_name + '_field'),
                post_code           = $('#billing_postcode_field'),
                customer_details    = $('div.woocommerce-billing-fields div'),
                button              = $('#payer_get_address');

            pno_field.addClass('form-row-first');
            post_code.addClass('form-row-last');
            post_code.removeClass('form-row-wide');
            post_code.before('<div id="payer_postcode_placeholder"></div>');
            customer_details.prepend(post_code);     
            customer_details.prepend(pno_field);
            post_code.after(button);      
        },

        addGetAddressButton: function() {
            var post_code   = $('#billing_postcode_field'),
                button      = '<button type="button" class="payer_get_address_button button" id="payer_get_address">' + payer_wc_params.get_address_text + '</button>';

            post_code.after(button);
        },

        getAddress: function() {

            var personalNumber = $('#payer_b2b_pno').val(),
                zipCode = $('#billing_postcode').val()
				button = $('#payer_get_address'),
				country = $('#billing_country_field option:selected').val(),
				response_message_field = $('#payer-get-address-response');

            response_message_field.remove();
            // Add spinner
            button.prop('disabled', true)
			button.addClass('payer_spinner');
			
			//AJAX
            $.ajax({

                type: 'POST',
                url: payer_wc_params.get_address,
                data: {
                    'action': 'get_address',
                    'personal_number': personalNumber,
					'zip_code' : zipCode,
					'country' : country,
					'get_address_nonce' : payer_wc_params.get_address_nonce
                },
                dataType: 'json',
                success: function(data) {
                    var address_data = data.data.address_information,
                        message = data.data.message;
                    if( data.success === false ) { 
                        button.after('<div id="payer-get-address-response" class="woocommerce-error">' + message + '</div>');
                    } else {
                        button.after('<div id="payer-get-address-response" class="woocommerce-message">' + message + '</div>');
                        payer_wc.populateAddressFields( address_data );
                    }
                },
                error: function(data) {
                },
                complete: function(data) {
                    button.prop('disabled', false)
                    // Remove spinner
                    button.removeClass('payer_spinner');
                    $('body').trigger('update_checkout');
                }
			});   
		},

		//Fill the fields with retrieved data.
		populateAddressFields: function( address_data ) {
            // Set fields
            var first_name      = $('#billing_first_name'),
                last_name       = $('#billing_last_name'),
                organisation    = $('#billing_company'),
                city            = $('#billing_city'),
                post_code       = $('#billing_postcode'),
                address_1       = $('#billing_address_1'),
                address_2       = $('#billing_address_2');
            // Populate fields
            first_name.val( ( '' === address_data.organisation ? payer_wc.maskFormField( address_data.firstName ) : address_data.firstName ) );
            last_name.val( ( '' === address_data.organisation ? payer_wc.maskFormField( address_data.lastName ) : address_data.lastName ) );
            organisation.val( ( '' === address_data.organisation ? payer_wc.maskFormField( address_data.coAddress ) : address_data.coAddress ) );
            city.val( ( '' === address_data.organisation ? payer_wc.maskFormField( address_data.city ) : address_data.city ) );
            post_code.val( address_data.zipCode );
            address_1.val( ( '' === address_data.organisation ? payer_wc.maskFormField( address_data.address_1 ) : address_data.streetAddress1 ) );
            address_2.val( ( '' === address_data.organisation ? payer_wc.maskFormField( address_data.address_2 ) : address_data.streetAddress2 ) );
        },

		init: function(){
			$( document ).ready( payer_wc.documentReady );

			$('body').on('click', '#payer_get_address', function() {
				payer_wc.getAddress();
		});
		}
	}
	payer_wc.init();
});