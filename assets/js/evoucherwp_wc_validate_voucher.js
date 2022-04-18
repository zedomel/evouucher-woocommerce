jQuery(document).ready(function( $ ) {
	$('#validate_voucher_form').on( 'submit', function(e){
		e.preventDefault();

		var code = jQuery('#voucher-number').val();
		if ( code != '' && code != undefined) {
			jQuery.ajax({
				type: 'POST',
				dataType: 'json',
				url: validate_endpoint_url,
				data: {
					'security'  : ajax_nonce,
					'validate-voucher' : code
				},
				success: function( data ){
					jQuery('.change-voucher').prepend('<div class="woocommerce-message">' + data.message + '</div>');
				}
			});
		}
	});
});