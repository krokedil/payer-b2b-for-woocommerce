const init = function() {
	const iframeWrapper = document.getElementById('pb2b-modal-wrapper');
	const showInvoiceIframe = document.getElementById('pb2b-show-invoice');
	if ( showInvoiceIframe ) {
		showInvoiceIframe.addEventListener('click', (showInvoiceIframe) => {
			iframeWrapper.classList.add('pb2b-show-iframe');
			iframeWrapper.classList.remove('pb2b-hide-iframe');
		});
	}
	const hideInvoiceIframe = document.getElementById('pb2b-close-modal');
	if ( hideInvoiceIframe ) {
		hideInvoiceIframe.addEventListener('click', (hideInvoiceIframe) => {
			iframeWrapper.classList.remove('pb2b-show-iframe');
			iframeWrapper.classList.add('pb2b-hide-iframe');
		});
	}
}

document.addEventListener('DOMContentLoaded', init, false);