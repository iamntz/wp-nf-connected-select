jQuery(document).ready(function($){
  $('#ninja-forms-viewport').on('change', '.js-ntz-ninjaForms-connectedSelect', function(){
    var parent = $(this).closest('.menu-item-settings');
    var store = $('.ninja-forms-connected_select-connect_to', parent);
    store.val( $(this).val() );
  });

  $('.ninja-forms-connected_select-connect_to').each(function(){
    var parent = $(this).closest('.menu-item-settings');
    var currentValue = this.value;
    $(this).closest('.description').hide();
    $('.js-ntz-ninjaForms-connectedSelect', parent).val( currentValue ).trigger('change');
  });
});