((Drupal, $, once) => {

  'use strict';

  /**
   * The attribute name added to the <html> element on behaviour attach.
   *
   * @type {String}
   */
  const attributeName = 'data-refreshless-turbo-behaviours-attached';

  /**
   * The once() name to use.
   *
   * @type {String}
   */
  const onceName = 'refreshless-turbo-behaviour-attached-attribute';

  Drupal.behaviors.refreshlessBehaviourAttachedAttribute = {

    attach(context, settings) {

      $(once(onceName, 'html', context)).each((i, html) => {

        $(html).attr(attributeName, true);

      });

    },

    detach(context, settings, trigger) {

      if (trigger !== 'unload') {
        return;
      }

      $(once.remove(onceName, 'html', context)).each((i, html) => {

        $(html).removeAttr(attributeName);

      });

    },

  };

})(Drupal, jQuery, once);
