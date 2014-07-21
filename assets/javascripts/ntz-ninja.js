(function( $, document ){
  if (!Object.create) { Object.create = function (o) { if (arguments.length > 1) { throw new Error('Object.create implementation only accepts the first parameter.'); } function F() {} F.prototype = o; return new F(); }; }
  var NtzNinjaConnectedSelects = {
    init: function( el ){
      this.el        = $( el );
      this.id        = this.el.attr('id');
      this.fetch_url = this.el.data('fetch_url');
      this.child     = this.getChild();

      this.el.on( 'change', $.proxy( this.onChange, this ) );
      this.el.on( 'init', $.proxy( this.onInit, this ) );
      this.child.on( 'ntz-ninja/data-populated', $.proxy( this.dataPopulatedHook, this ) );

      if( 'undefined' == typeof this.el.data('connected_to') ){
        this.el.trigger('init');
      }
    } // init


    ,onChange: function( event ){
      var child = this.getChild( event.currentTarget.id );
      this.getRemote( child );
    }//onChange


    ,onInit: function( event ){
      this.getRemote( event.currentTarget );
    }//onInit


    ,getRemote: function( select ){
      var parent = $(select).data('connected_to');
      var options = {
        action      : 'ntz_ninja_get_connected',
        nonce       : ntz_ninja.nonce,
        parent      : parent,
        parent_value: this.el.val(),
        name        : select.name,
        param       : $(select).data('extra_param')
      };

      $(select).addClass('loading');
      $.get( this.fetch_url, options, $.proxy( this.populateData, this, select ) );
    }//getRemote


    ,populateData: function( select, data ){
      $(select).find('option:not([data-default_option="1"])').remove();
      $(select).append( data );
      this.setValue( this.el );
      $(select).trigger('ntz-ninja/data-populated').removeClass('loading');
    }//populateData


    ,dataPopulatedHook: function( event ){
      this.setValue( event.currentTarget );
    }//dataPopulatedHook


    ,setValue: function( select ){
      select = $(select);
      var value = select.data('value');
      if( value && !select.data('value-is-set') && value != '-null-' && select.val() != value ){
        $('option[value="' + value + '"]', select).attr('selected', true);
        select.trigger('change');
        select.data('value-is-set', true);
      }
    }//setValue



    ,getChild: function(){
      var id = this.id;

      return $('select[data-connected_to]').filter(function( i, el ){
        return $(el).data('connected_to') == id;
      });
    }//getChild
  };


  $.fn.ntzNinjaConnectedSelects = function() {
    return this.each(function(){
      var obj = Object.create( NtzNinjaConnectedSelects );
      obj.init( this );
    });
  };
})( jQuery, document );

jQuery(document).ready(function($){
  $('.js-ntz-ninjaForms-connectedSelect[data-fetch_url]').ntzNinjaConnectedSelects();

});