$( function() {
    $( "#sortable" ).sortable();
    $( "#sortable" ).disableSelection();
  } );

  jQuery(document).ready(function($){

    if ($( '.reorder-css-list li' ).length > 2) {
  $( '.reorder-js-list, .reorder-css-list' ).sortable().disableSelection();
    }

  $('#reorder-save').on('click', function(){
        var     data,
           cssorder,
           jsorder,
           post_id;

            
      jsOrder = $('.reorder-js-list').sortable( "toArray" );
      cssorder = $('.reorder-css-list').sortable( "toArray");
      post_id = $('input[name=post_id]').val();
      var data = {
      'action': 'OCSSJS_update_reorder',
      'css_order': cssorder,
      'jsorder': jsOrder,
      'post_id' : post_id,
             };

      $.post(ajax_object.ajax_url, data, function(response) {
           var html = '',
           response = $.parseJSON(response),
           newJSOrder = response.newJSOrder,
       jQueryScripts = response.jQueryScripts;
       $('.OCSSJS-reorder-cont').prepend(response.feedback);
       $('.OCSSJS-feedback').fadeOut(5000, function() { });
      });
  });

});
