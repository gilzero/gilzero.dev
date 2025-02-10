((Cookies, html, Drupal, drupalSettings, $, once) => { $(once(
  'refreshless-turbo-page-state', html,
)).each(() => {

  'use strict';

  $(html).on('refreshless:reload', (event) => {

    // Remove the page state cookie before the reload to avoid potentially
    // incorrect libraries on the subsequent page. The back-end should account
    // for this, so this is just in case there's an error or unhandled case in
    // the back-end logic as a second layer to prevent that.
    Cookies.remove(drupalSettings.refreshless.pageStateCookie.name);

  });

  // This adds a session cookie containing the libraries, theme name, and theme
  // token from drupalSettings.ajaxPageState so that they're sent with every
  // Turbo request.
  Drupal.behaviors.refreshlessTurboPageState = {

    attach(context, settings) {

      $(once('refreshless-turbo-page-state-cookie', html, context)).each(() => {

        const ajaxPageState = drupalSettings.ajaxPageState;

        const refreshlessPageState = {
          libraries:    ajaxPageState.libraries,
          theme:        ajaxPageState.theme,
          theme_token:  ajaxPageState.theme_token,
        };

        Cookies.set(
          drupalSettings.refreshless.pageStateCookie.name,
          JSON.stringify(refreshlessPageState),
          drupalSettings.refreshless.pageStateCookie.attributes,
        );

      });

    },

    detach(context, settings, trigger) {

      if (trigger !== 'unload') {
        return;
      }

      // We don't strictly need to remove the cookie, but it has no use after
      // the request has been sent so we might as well clean up after ourselves.
      // We do need to once.remove() however, to ensure we attach again on the
      // next page and thus update the cookie contents.
      $(once.remove(
        'refreshless-turbo-page-state-cookie', html, context,
      )).each(() => {
        Cookies.remove(drupalSettings.refreshless.pageStateCookie.name);
      });

    },

  };

}); })(Cookies, document.documentElement, Drupal, drupalSettings, jQuery, once);
