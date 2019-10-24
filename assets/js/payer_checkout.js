const init = function() {
	const pnoFieldLabel = document.getElementById('payer_b2b_pno_label');
	const signatoryWrapper = document.getElementById('signatory_wrapper');
	const maybeSetB2B = document.getElementById('payer_b2b_set_b2b');
	let signatoryStatus = 'hidden';
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

	const signatoryField = document.getElementById('payer_b2b_signatory_text_field');
	const toggleSignatoryField = document.getElementById('payer_b2b_signatory');
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

const targetNode = document.getElementById('order_review');

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