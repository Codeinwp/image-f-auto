jQuery( document ).ready(function() {
    if( state  == "true" ){
      jQuery('#cloudinary_options_form').find('input[name=fauto]').filter('[value=true]').prop('checked', true);
    } else {
      jQuery('#cloudinary_options_form').find('input[name=fauto]').filter('[value=false]').prop('checked', true);
    }
});
