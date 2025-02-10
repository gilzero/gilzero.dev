((Drupal, $, once) => {

  'use strict';

  /**
   * The once() identifier for our behaviour.
   *
   * @type {String}
   */
  const onceName = 'drupal-refreshless-turbo-ajax';

  /**
   * Disable Turbo for elements handled by Drupal core's Ajax framework.
   *
   * @see Drupal.behaviors.AJAX
   *   We copy some of the logic used in core to apply to elements passed from
   *   the back-end via drupalSettings.
   */
  Drupal.behaviors.refreshlessTurboAjaxCompatibility = {

    attach(context, settings) {

      // This applies to Ajax elements passed via drupalSettings.
      Object.keys(settings.ajax || {}).forEach((key) => {

        const elementSettings = settings.ajax[key];

        if (typeof elementSettings.selector === 'undefined') {
          elementSettings.selector = `#${key}`;
        }

        $(once(
          onceName, elementSettings.selector, context,
        )).attr('data-turbo', false);

      });

      // This applies to any elements that have the 'use-ajax' and
      // 'use-ajax-submit' classes, which core also supports.
      $(once(
        onceName, '.use-ajax, .use-ajax-submit', context,
      )).attr('data-turbo', false);

    },
    detach(context, settings, trigger) {

      if (trigger !== 'unload') {
        return;
      }

      Object.keys(settings.ajax || {}).forEach((key) => {

        const elementSettings = settings.ajax[key];

        if (typeof elementSettings.selector === 'undefined') {
          elementSettings.selector = `#${key}`;
        }

        $(once.remove(
          onceName, elementSettings.selector, context,
        )).removeAttr('data-turbo');

      });

      $(once.remove(
        onceName, '.use-ajax, .use-ajax-submit', context,
      )).removeAttr('data-turbo');

    },

  };

})(Drupal, jQuery, once);
