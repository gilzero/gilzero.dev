((Cookies, html, drupalSettings, $, once) => { $(once(
  'refreshless-turbo-reload-reason', html,
)).each(() => {

  'use strict';

  // Remove the reload reason cookie if it exists. This should only be evaluated
  // after a reload (full page load).
  Cookies.remove(drupalSettings.refreshless.reloadReasonCookie.name);

  $(html).on('refreshless:reload', (event) => {

    // Set a cookie containing the reason for the reload so that the back-end
    // can identify this as a Turbo reload. Turbo initiates the reload after
    // triggering this event, so this cookie will be included in the request.
    Cookies.set(
      drupalSettings.refreshless.reloadReasonCookie.name,
      event.detail.reason,
      drupalSettings.refreshless.reloadReasonCookie.attributes,
    );

    console.info(
      '%cRefreshLess%c: performed a full page load; reason: %c%s%c',
      'font-style: italic', 'font-style: normal',
      'font-family: monospace', event.detail.reason, 'font-family: revert',
    );

  });

// Note that we're intentionally referencing just drupalSettings and not
// drupalSettings.refreshless.reloadReasonCookie just in case different values
// get merged in from the back-end after one or more RefreshLess navigations.
}); })(Cookies, document.documentElement, drupalSettings, jQuery, once);
