jQuery( document ).ready(function() {
  var html = '<tr><td class="cloudinary_config_first_td"> Enable f_auto</td><td><input type="radio" name="fauto" value="true"/> Yes<input type="radio" name="fauto" value="false"/> No</td></tr>';
  jQuery('.cloudinary_config_tab tr:first-child').after( html );
  jQuery('#cloudinary_options_form').submit(function() {
    var form = jQuery(this);
    var fauto = jQuery(form).find('input[name=fauto]:checked').val();
    data = {action: "fauto_save", fauto: fauto };
    jQuery.post(ajaxurl, data, function(response) {
      if (response == 'success') {
      }
    });
  });
});
