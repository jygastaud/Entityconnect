(function ($) {
  Drupal.behaviors.entityconnect = {
    'attach': function(context) {
      ref_field_buttons = {};

      // Treatments for each widget type.
      // Autocomplete widget.
      $(".entityconnect-add.single-value", context).each(function() {
        $(this).insertAfter($(this).next().find("label"));
      });
      $(".entityconnect-edit.single-value", context).each(function() {
        $(this).insertAfter($(this).next().find("label"));
      });

      // Select widget.
      $(".entityconnect-add.select", context).each(function() {
        $(this).insertAfter($(this).next().find("label"));
      });
      // Radios widget.
      $(".entityconnect-add.radios", context).each(function() {
        $(this).insertBefore($(this).siblings("div.form-type-radios").find("label").first());
      });
      // Checkboxes widget.
      $(".entityconnect-add.checkboxes", context).each(function() {
        $(this).insertBefore($(this).siblings("div.form-type-checkboxes").find("label").first());
      });

      // Edit button control.
      $(".entityconnect-edit input").click(function() {

        var wrapper = $(this).parents(".entityconnect-edit");

        text = $(wrapper).siblings("[type='text']");

        if(text.length == 0) {
          text = $(wrapper).siblings().find("[type='text']");
        }

        if($.trim($(text).val()) == '') {
          return false;
        }
        return true;
      });
    }
  };
})(jQuery);
