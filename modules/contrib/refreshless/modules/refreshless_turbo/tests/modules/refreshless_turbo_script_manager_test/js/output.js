((Drupal, $, once) => {

  'use strict';

  /**
   * The once() identifier for our behaviour.
   *
   * @type {String}
   */
  const onceName = 'refreshless-turbo-script-manager-test-output';

  /**
   * The 'id' attribute value to identify the the inserted element by.
   *
   * @type {String}
   */
  const identifier = onceName;

  Drupal.behaviors.refreshlessTurboScriptManagerOutputTest = {

    attach(context, settings) {

      $(once(onceName, 'main', context)).append(
        `<div id="${identifier}">Some output.</div>`,
      );

    },
    detach(context, settings, trigger) {

      if (trigger !== 'unload') {
        return;
      }

      $(once.remove(onceName, 'main', context)).find(`#${identifier}`).remove();

    },

  };

})(Drupal, jQuery, once);
