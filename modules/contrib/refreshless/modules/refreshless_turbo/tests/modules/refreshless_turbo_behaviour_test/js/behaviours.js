((Drupal, $, once) => {

  'use strict';

  const counterName = 'data-refreshless-turbo-behaviour-test-counter';

  const onceName = 'refreshless-turbo-behaviour-test';

  Drupal.behaviors.refreshlessBehaviourTest = {

    attach(context, settings) {

      $(once(onceName, 'main', context)).each((i, main) => {

        const $html = $(main).closest('html');

        let counter;

        if (typeof $html.attr(counterName) !== 'undefined') {

          counter = Number.parseInt($html.attr(counterName));

        } else {

          counter = 0;

        }

        counter++;

        $html.attr(counterName, counter);

      });

    },

    detach(context, settings, trigger) {

      if (trigger !== 'unload') {
        return;
      }

      // The once needs to be removed so that it gets attached again.
      $(once.remove(onceName, 'main', context));

    },

  };

})(Drupal, jQuery, once);
