jQuery(document).ready(function( $ ){
	jQuery('#evoucherwp_change_enabled').change(function(){
		jQuery('#evoucherwp_days_to_change').prop('disabled', !this.checked );
	});
	jQuery('#evoucherwp_change_enabled').change();
});