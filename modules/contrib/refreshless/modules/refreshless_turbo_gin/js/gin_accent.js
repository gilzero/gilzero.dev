(function (Drupal, $, once) {

  'use strict';

  /**
   * The once() identifier for our behaviour.
   *
   * @type {String}
   */
  const onceName = 'drupal-refreshless-turbo-gin-accent';

  /**
   * The Gin accent colour attribute name.
   *
   * @type {String}
   */
  const accentAttrName = 'data-gin-accent';

  /**
   * Gin accent colour attribute MutationObserver.
   *
   * @type {MutationObserver}
   */
  const attrObserver = new MutationObserver(function(mutations) {

    for (let i = 0; i < mutations.length; i++) {

      $(mutations[i].target).parent('html').attr(
        accentAttrName, $(mutations[i].target).attr(accentAttrName),
      );

    }

  });

  /**
   * Use the Gin accent colour for the Turbo progress bar.
   *
   * Turbo inserts its progress bar as a direct child of the <html> element
   * outside of the <body>, but Gin sets its accent colour attribute on the
   * <body>, making its custom properties inaccessible to the Turbo progress
   * bar. Interestingly, Gin's CSS also supports the accent colour attribute
   * being set on the <html> element, so we copy it from the <body> to <html>
   * both on attach and update it with our MutationObserver for cases such as
   * the Gin theme settings form where it can get updated dynamically.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.refreshlessTurboGinAccent = {

    attach(context, settings) {

      // Only apply this to a document or an <html> element. Ignore attaching to
      // any other context, such as a subset of the page.
      if (
        !(context instanceof HTMLDocument) &&
        !$(context).is('html')
      ) {
        return;
      }

      /**
       * The <html> element.
       *
       * @type {HTMLHtmlElement}
       */
      let htmlElement;

      if (context instanceof HTMLDocument) {
        htmlElement = $(context).find('> html')[0];
      } else {
        htmlElement = context;
      }

      once(onceName, htmlElement).forEach(function(element) {

        /**
         * The value of the <body> attribute or undefined if not present.
         *
         * @type {String|undefined}
         */
        const bodyAttrValue = $(element).find('body').attr(accentAttrName);

        if (typeof bodyAttrValue !== 'string') {
          return;
        }

        $(element).attr(accentAttrName, bodyAttrValue);

        attrObserver.observe($(element).find('body')[0], {
          attributeFilter: [accentAttrName],
        });

      });

    },
    detach(context, settings, trigger) {

      if (trigger !== 'unload' || !$(context).is('html')) {
        return;
      }

      once.remove(onceName, context).forEach(function(element) {

        attrObserver.disconnect();

        // Don't remove the attribute here since it can sometimes cause the
        // default progress bar colour to briefly be displayed in some edge
        // cases when behaviours are detached. It's unlikely leaving this
        // attribute on the <html> element is going to be a problem.

      });

    },

  };

})(Drupal, jQuery, once);
