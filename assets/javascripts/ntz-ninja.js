jQuery(document).ready(function($){
  $('.js-ntz-ninjaForms-connectedSelect').on('change', function(){
    var select = $(this);
    var id = this.id;
    var connectedTo = select.data('connected_to');

    var connectedToThis = $('.js-ntz-ninjaForms-connectedSelect').filter(function( i, el ){
      return $(el).data('connected_to') == id;
    }) ;

    var fetchUrl = select.data('fetch_url');

    if( fetchUrl ){
      $.get( fetchUrl, {

      }, function(data){
        $('option', connectedToThis).filter(function(){
          return $(this).data('default_option') != 1;
        }).remove();

        connectedToThis.append( data );
      });
    }
  });
});