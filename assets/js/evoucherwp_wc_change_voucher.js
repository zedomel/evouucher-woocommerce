jQuery(document).ready(function( $ ) {

	if (typeof _evoucherwp_wc_params === 'undefined') {
  	  return false;
  	}

	$('#cv_form').on( 'submit', function(e){
		e.preventDefault();

		// var email = jQuery('#voucher-email').val();
		var code = jQuery('#voucher-number').val();
		// if ( ( email != '' && email != undefined ) && 
		if ( code != '' && code != undefined) {
			jQuery.ajax({
				type: 'POST',
				dataType: 'json',
				url: _evoucherwp_wc_params.ajax_url,
				data: {
					'action' 	: 'evoucherwp_change_voucher',
					'security'  : _evoucherwp_wc_params.nonce,
					// 'cv_email'  : email, 
					'cv_code' : code
				},
				success: function( data ){
					jQuery('.change-voucher').prepend('<div class="woocommerce-message">' + data.message + '</div>');
				}
			});
		}
	});

	$('.voucher-live input[type="checkbox"]').change( function(e){
  	var voucher_id = $(this).data('voucher-id');
  	$.ajax({
  		url: _evoucherwp_wc_params.ajax_url,
  		type: 'POST',
  		data: {
  			'action': 'evoucherwp_set_live',
  			'security': _evoucherwp_wc_params.nonce,
  			'voucher_id': voucher_id,
  			'voucher_live': $(this).prop('checked')
  		}
  	});
  });

});