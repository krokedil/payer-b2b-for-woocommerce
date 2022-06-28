jQuery( function($)  {
	var payer_wc = {
        onBoardingSuccess: function() {
            console.log("onBoardingSuccess");
        },

        onBoardingFailed: function() {
            console.log("onBoardingFailed");
        },

        onWidgetLoad: function() {
            console.log("onWidgetLoad");
        },

		documentReady: function(){
			payer_wc.moveInputFields();
			payer_wc.addGetAddressButton();
		},

		moveInputFields: function() {
            if ( payer_wc_params.get_address_enabled ) {
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
            }
        },

        addGetAddressButton: function() {
            if ( payer_wc_params.get_address_enabled ) {
                var post_code   = $('#billing_postcode_field'),
                    button      = '<button type="button" class="payer_get_address_button button" style="display:block; clear:both" id="payer_get_address">' + payer_wc_params.get_address_text + '</button>';

                post_code.after(button);
            }
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
                        button.after('<div id="payer-get-address-response" style="margin-top:10px" class="woocommerce-error">' + message + '</div>');
                    } else {
                        button.after('<div id="payer-get-address-response" style="margin-top:10px" class="woocommerce-message">' + message + '</div>');
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
            // Set fields.
            var first_name      = $('#billing_first_name'),
                last_name       = $('#billing_last_name'),
                organisation    = $('#billing_company'),
                city            = $('#billing_city'),
                post_code       = $('#billing_postcode'),
                address_1       = $('#billing_address_1'),
                address_2       = $('#billing_address_2');
            // Populate fields.
            first_name.val( ( null === address_data.companyName ? payer_wc.maskFormField( address_data.firstName ) : address_data.firstName ) );
            last_name.val( ( null === address_data.companyName ? payer_wc.maskFormField( address_data.lastName ) : address_data.lastName ) );
            organisation.val( ( null === address_data.companyName ? payer_wc.maskFormField( address_data.coAddress ) : address_data.coAddress ) );
            city.val( ( null === address_data.companyName ? payer_wc.maskFormField( address_data.city ) : address_data.city ) );
            post_code.val( address_data.zipCode );
            address_1.val( ( null === address_data.companyName ? payer_wc.maskFormField( address_data.streetAddress1) : address_data.streetAddress1 ) );
            address_2.val( ( null === address_data.companyName ? payer_wc.maskFormField( address_data.streetAddress2 ) : address_data.streetAddress2 ) );
        },

        //Hide characters on checkout page input fields.
        maskFormField: function( field ) {
            if ( field !== null && typeof field !== 'undefined') {
                var field_split = field.split( ' ' );
                var field_masked = new Array();
    
                $.each(field_split, function ( i, val ) {
                    if ( isNaN( val ) ) {
                        field_masked.push( val.charAt( 0 ) + Array( val.length ).join( '*' ) );
                    } else {
                        field_masked.push( '**' + val.substr( val.length - 3 ) );
                    }
                });
    
                return field_masked.join( ' ' );
            }
        },

		maybeSetB2B: function( e ) {
			let target = e.target;
			let pnoLabel = $( '#payer_b2b_pno_field' ).find('label');

			if( $(target).is(":checked") ) {
				pnoLabel.html( payer_wc_params.b2b_text + ' <abbr class="required" title="required">*</abbr>' )
			} else {
				pnoLabel.html( payer_wc_params.b2c_text + ' <abbr class="required" title="required">*</abbr>' )
			}
		},

		toggleSignatoryField: function( e ) {
			let target = e.target;
			let p = $( target ).parent().parent().siblings( 'p.payer_b2b_signatory_text_field' );
			if( $(target).is(":checked") ) {
				p.css('display', 'block');
			} else {
				p.css('display', 'none');
			}
		},

		init: function(){
			$( document ).ready( payer_wc.documentReady );
			$('body').on('click', '#payer_get_address', function() { payer_wc.getAddress(); });
			$('body').on('click', '.payer_b2b_set_b2b', function( e ) { payer_wc.maybeSetB2B( e ) });
			$('body').on('click', '.payer_b2b_signatory', function( e ) { payer_wc.toggleSignatoryField( e ) });
		}
	}
	payer_wc.init();
});

if( payer_wc_params.signup_enabled ) {
    const onSuccess = (data) => {
		console.log(data);
		setCreditDecision(data);
    };

    const onFailure = (error) => {
        console.error('Signup failed', error);
    };

	const setCreditDecision = (data) => {
		console.log("setCreditDecision");
		jQuery.ajax({
			type: 'POST',
			url: payer_wc_params.set_credit_decision,
			data: {
				'action': 'set_credit_decision',
				'status' : data.status,
				'credit_decision' : data.creditDecision,
				'set_credit_decision_nonce' : payer_wc_params.set_credit_decision_nonce,
			},
			dataType: 'json',
			complete: function(res) {
				console.log(res);
				jQuery( '#' + payer_wc_params.pno_name).val(data.company.regNumber)
				jQuery('#billing_first_name').val(data.customer.firstName);
				jQuery('#billing_last_name').val(data.customer.lastName);
				jQuery('#billing_company').val(data.invoiceAddress.name);
				jQuery('#billing_country').val(data.company.countryCode);
				jQuery('#billing_address_1').val(data.invoiceAddress.streetAddress1);
				jQuery('#billing_address_2').val(data.invoiceAddress.streetAddress2);
				jQuery('#billing_city').val(data.invoiceAddress.city);
				jQuery('#billing_postcode').val(data.invoiceAddress.zipCode);
				jQuery('#billing_phone').val(data.customer.phoneNumber);
				jQuery('#billing_email').val(data.customer.email);
		
				jQuery('#ship-to-different-address-checkbox').prop( 'checked', true);
				jQuery('#shipping_first_name').val(data.customer.firstName);
				jQuery('#shipping_last_name').val(data.customer.lastName);
				jQuery('#shipping_company').val(data.deliveryAddress.name);
				jQuery('#shipping_country').val(data.company.countryCode);
				jQuery('#shipping_address_1').val(data.deliveryAddress.streetAddress1);
				jQuery('#shipping_address_2').val(data.deliveryAddress.streetAddress2);
				jQuery('#shipping_city').val(data.deliveryAddress.city);
				jQuery('#shipping_postcode').val(data.deliveryAddress.zipCode);
				jQuery('body').trigger('update_checkout');
				jQuery('#pb2b-payment-wrapper').show();
			}
		});   
	}

    window.payerAsyncCallback = function () {
        PayerSignup.init({
            clientToken: payer_wc_params.client_token,
            containerId: "payer-signup-container",
            callbacks: {
                onOnboardingSucceeded: onSuccess,
                onOnboardingFailed: onFailure,
            }
        });
    };
}