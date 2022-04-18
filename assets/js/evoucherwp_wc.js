jQuery(document).ready( function( $ ){

	jQuery('#_evoucherwp_gift_checkbox').change( function(e) {
		if ( this.checked ){
			jQuery('#_evoucherwp_gift_email_field').removeClass('hide');
			jQuery('#_evoucherwp_gift_message_field').removeClass('hide');
		}
		else{
			jQuery('#_evoucherwp_gift_email_field').addClass('hide');
			jQuery('#_evoucherwp_gift_message_field').addClass('hide');
		}
	});
	// Fire event on reload
	jQuery('#_evoucherwp_gift_checkbox').change();
});