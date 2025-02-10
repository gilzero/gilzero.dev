((html, Drupal, $, once) => { $(once(
  'refreshless-turbo-debug', html,
)).each(() => {

  'use strict';

  /**
   * Our event namespace.
   *
   * @type {String}
   *
   * @see https://learn.jquery.com/events/event-basics/#namespacing-events
   */
  const eventNamespace = 'refreshless-turbo-debug';

  $(html).on(`refreshless:attach.${eventNamespace}`, (event) => {

    console.debug(
      '%cRefreshLess%c: attaching behaviours',
      'font-style: italic', 'font-style: normal',
    );

  }).on(`refreshless:detach.${eventNamespace}`, (event) => {

    console.debug(
      '%cRefreshLess%c: detaching behaviours',
      'font-style: italic', 'font-style: normal',
    );

  }).on(`refreshless:before-prefetch.${eventNamespace}`, (event) => {

    if (event.isDefaultPrevented() === true) {
      return;
    }

    console.debug(
      '%cRefreshLess%c: prefetching %c%s',
      'font-style: italic', 'font-style: normal',
      'font-weight: bold', event.detail.url.pathname,
    );

  }).on(`refreshless:redirect.${eventNamespace}`, (event) => {

    const fromUrl = event.detail.from;

    const toUrl = event.detail.to;

    console.debug(
      '%cRefreshLess%c: redirected %c%s%c → %c%s',
      'font-style: italic', 'font-style: normal',
      'font-weight: bold', fromUrl.pathname, 'font-weight: normal',
      'font-weight: bold', toUrl.pathname,
    );

  }).on(`turbo:submit-start.${eventNamespace}`, (event) => {

    console.debug(
      '%cRefreshLess%c: form submit to %c%s',
      'font-style: italic', 'font-style: normal',
      'font-weight: bold',
      event.detail.formSubmission.location.pathname,
    );

  }).on(`refreshless:navigation-response.${eventNamespace}`, (event) => {

    console.debug(
      '%cRefreshLess%c: navigated to %c%s',
      'font-style: italic', 'font-style: normal',
      'font-weight: bold', event.detail.url.pathname,
    );

  }).on(`refreshless:drupal-settings-update.${eventNamespace}`, (event) => {

    console.debug(
      '%cRefreshLess%c: %cdrupalSettings%c has been updated',
      'font-style: italic', 'font-style: normal',
      'font-weight: bold', 'font-weight: normal',
    );

  }).on(`refreshless:stylesheets-merged.${eventNamespace}`, (event) => {

    const mergedStylesheets = event.detail.mergedStylesheets;

    if (mergedStylesheets.length === 0) {

      console.debug(
        '%cRefreshLess%c: no new stylesheets added',
        'font-style: italic', 'font-style: normal',
      );

      return;

    }

    console.debug(
      `%cRefreshLess%c: ${Drupal.formatPlural(
        mergedStylesheets.length,
        'added %c1%c new stylesheet', 'added %c@count%c new stylesheets',
      )}:`,
      'font-style: italic', 'font-style: normal',
      'font-weight: bold', 'font-weight: normal',
      mergedStylesheets,
    );

  }).on(`refreshless:scripts-merged.${eventNamespace}`, (event) => {

    if (event.detail.new.length === 0) {

      console.debug(
        '%cRefreshLess%c: no new script elements added to the %s',
        'font-style: italic', 'font-style: normal', event.detail.context,
      );

      return;

    }

    console.debug(
      `%cRefreshLess%c: ${Drupal.formatPlural(
        event.detail.new.length,
        'added %c1%c new script element to the @context',
        'added %c@count%c new script elements to the @context',
        {'@context': event.detail.context},
      )}:`,
      'font-style: italic', 'font-style: normal',
      'font-weight: bold', 'font-weight: normal',
      event.detail.new,
    );

  }).on(`refreshless:scripts-loaded.${eventNamespace}`, (event) => {

    if (event.detail.loaded.length === 0) {

      console.debug(
        '%cRefreshLess%c: no new script elements to load',
        'font-style: italic', 'font-style: normal',
      );

      return;

    }

    console.debug(
      `%cRefreshLess%c: ${Drupal.formatPlural(
        event.detail.loaded.length,
        '%c1%c new script element has loaded',
        '%c@count%c new script elements have loaded',
      )}:`,
      'font-style: italic', 'font-style: normal',
      'font-weight: bold', 'font-weight: normal',
      event.detail.loaded,
    );

  });

}); })(document.documentElement, Drupal, jQuery, once);
