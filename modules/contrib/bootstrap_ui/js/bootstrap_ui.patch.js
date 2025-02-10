/**
 * @file
 * Contains definition of the behaviour Bootstrap UI.
 */

(function ($, Drupal, drupalSettings, once) {
  "use strict";

  Drupal.behaviors.bootstrapUI = {
    attach: function (context, settings) {

      const spacing = '[class*="ml-"], [class*="mr-"], [class*="pl-"], [class*="pr-"]';
      let el = $(spacing);

      if (once('bootstrap-UI', el).length !== 0) {
        $.each(el, function (index) {
          let currentTarget = $(this)
            , allClasses = $(this).attr('class').split(' ')
            , newClass;

          $.each(allClasses, function (index, className) {
            if (className.startsWith("ml-")) {
              newClass = className.replace('ml-', 'mr-');
              currentTarget.removeClass(className);
              currentTarget.addClass(newClass);
            }
            if (className.startsWith("mr-")) {
              newClass = className.replace('mr-', 'ml-');
              currentTarget.removeClass(className);
              currentTarget.addClass(newClass);
            }
            if (className.startsWith("pr-")) {
              newClass = className.replace('pr-', 'pl-');
              currentTarget.removeClass(className);
              currentTarget.addClass(newClass);
            }
            if (className.startsWith("pl-")) {
              newClass = className.replace('pl-', 'pr-');
              currentTarget.removeClass(className);
              currentTarget.addClass(newClass);
            }
          });

        });
      }

    }
  };

})(jQuery, Drupal, drupalSettings, once);
