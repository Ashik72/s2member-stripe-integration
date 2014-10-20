

 jQuery(document).ready(function(){

 /* jQuery.ajax({
  'url':ajaxurl+'?action=get_stripe_products',
  'type':'POST',
  'success':function(data){
   jQuery('#stripe_products').html(data);
  }
  });
  */

  jQuery('body').on('click','#insert_stripe_product',function(){
    if(jQuery('#stripe_class').val().length > 1 ){
      var c = jQuery('#stripe_class').val();
    } else {
      var c = 'stripebutton';
    }


    stripe_code = '[stripe_s2 role="'+jQuery('#stripe_role option:selected').val()+'" custom_class="'+c+'"]';
    tinyMCE.execCommand('mceInsertContent', true, stripe_code);
    tb_remove();
  });
  });