jQuery( function( $ ) {
    
    // PRODUCT TYPE SPECIFIC OPTIONS.
    $( 'input#_evoucherwp_voucher' ).change( function() {
        show_and_hide_panels();
    }).change();

    function show_and_hide_panels(){
        var is_voucher      = $( 'input#_evoucherwp_voucher:checked' ).length;

        // Hide/Show all with rules.
        var hide_classes = '.hide_if_not_voucher';
        var show_classes = '.show_if_voucher';

        $( hide_classes ).show();
        $( show_classes ).hide();

        // Shows rules.
        if ( is_voucher ) {
            $( '.show_if_voucher' ).show();
        }
    }
});

jQuery(document).ready( function( $ ){

    jQuery('#_evoucherwp_codestype').change( function( e ){
        if ( jQuery(this).val() == 'single' ){
            jQuery('#_evoucherwp_codelength').parent().addClass('hide');
            jQuery('#_evoucherwp_singlecode').parent().removeClass('hide');
        }
        else{
            jQuery('#_evoucherwp_codelength').parent().removeClass('hide');
            jQuery('#_evoucherwp_singlecode').parent().addClass('hide');
        }
    }).change();
});

jQuery(document).ready( function( $ ){

    jQuery('#_evoucherwp_order_id').change( refresh_products_options );
    jQuery('#_evoucherwp_order_id').change();

    jQuery('.resend-voucher button').click( resend_voucher );
    jQuery('.create-voucher-pdf button').click( create_voucher_pdf );
    
});

function refresh_products_options(){
    var id = this.id;
    var order_id = jQuery(this).val();

    jQuery.ajax({
        type: 'POST',
        dataType: 'json',
        url: url,
        data: {
            'action'    : 'evoucherwp_select_order_id',
            'order_id'  : order_id,
        },
        success: function( data ){
            if ( data.valid ){
                jQuery( '#_evoucherwp_item_id').children().remove();
                if ( data.products.length > 0 ){
                    for (var i = 0, len = data.products.length; i < len; i++) {
                        jQuery( '#_evoucherwp_item_id').append('<option value="' + data.products[i].id + '">' + 
                        data.products[i].name + '</option>');
                    }
                }
            }
            else{
                jQuery(this).parent().prepend('<div class="evoucherwp-message">' + data.message + '</div>');
            }
        },
    });
}

function resend_voucher(){
    var voucherId = $('#post_ID').val();
    jQuery.ajax({
        type: 'GET',
        dataType: 'json',
        url: url,
        data: {
            'action'        : 'evoucherwp_resend_voucher',
            'voucher_id'    : voucherId,
            'security'      : admin_nonce
        },
        success: function( response ){
            if( response.success ){
                alert( response.data.message );
            }
        }
    });
}

function create_voucher_pdf(){
    var voucherId = $('#post_ID').val();
    jQuery.ajax({
        type: 'GET',
        dataType: 'json',
        url: url,        
        data: {
            'action'        : 'evoucherwp_create_voucher_pdf',
            'voucher_id'    : voucherId,
            'security'      : admin_nonce
        },
        success: function( response ){
            if( ! response.success ){
                alert( response.data.message );
            }
            else{
                window.location = response.data.file_url;
            }
        }
    });
}