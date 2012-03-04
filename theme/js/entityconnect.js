(function ($) {
  Drupal.behaviors.entityconnect = {
    'attach': function(context) {
      ref_field_buttons = {};
      $(".entityconnect-add.single-value", context).each(function() {
        $(this).insertAfter($(this).next().find("label"));
      });
      $(".entityconnect-edit.single-value", context).each(function() {
        $(this).insertAfter($(this).next().find("label"));
      });
      $(".entityconnect-edit", context).each(function() {
        edit = $(this).find('input');
        text = $(this).siblings("[type='text']");
        if(text.length == 0) {
          text = $(this).siblings().find("[type='text']");
        }
        text
          .bind('change', function(e) {
            if($(this).val() == '') {
              $(edit).attr('disabled', 'disabled');
            }
            else {
              $(edit).attr('disabled', '');
            }
          })
          .trigger('change');
      });
    },
  };
})(jQuery);
