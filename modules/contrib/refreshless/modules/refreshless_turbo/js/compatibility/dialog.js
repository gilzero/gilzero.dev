/**
 * @file
 * Drupal dialog compatibility for RefreshLess.
 */
(($, once) => {

  'use strict';

  /**
   * Our event namespace.
   *
   * @type {String}
   *
   * @see https://learn.jquery.com/events/event-basics/#namespacing-events
   */
  const eventNamespace = 'refreshless-turbo-dialog-compatibility';

  /**
   * The once() identifier for our behaviour.
   *
   * @type {String}
   */
  const onceName = 'refreshless-turbo-dialog-compatibility';

  /**
   * Disable Turbo within Drupal dialogs.
   */
  Drupal.behaviors.refreshlessTurboDialogCompatibility = {

    attach(context, settings) {

      $(once(onceName, 'body', context)).each((i, body) => {

        $(body)
        .on(`dialog:aftercreate.${eventNamespace}`, (event) => {

          $(event.target).attr('data-turbo', false);

        })
        .on(`dialog:afterclose.${eventNamespace}`, (event) => {

          $(event.target).removeAttr('data-turbo');

        });

      });

    },
    detach(context, settings, trigger) {

      if (trigger !== 'unload') {
        return;
      }

      $(once.remove(onceName, 'body', context)).off(`.${eventNamespace}`);

    },

  };

})(jQuery, once);
