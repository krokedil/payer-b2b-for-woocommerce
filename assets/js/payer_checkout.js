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