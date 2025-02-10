/**
 * @file
 * RefreshLess announcements for assistive software.
 *
 * @see https://github.com/hotwired/turbo/issues/774
 *   Turbo issue with a lot of good discussion and ideas.
 *
 * @see https://www.drupal.org/project/refreshless/issues/2695777
 *   8.x-1.x for this same functionality.
 */
((html, Drupal, $, once) => { $(once(
  'refreshless-turbo-announce', html,
)).each(() => {

  'use strict';

  $(html).on('refreshless:load', (event) => {

    // Don't announce on the initial load.
    if (event.detail.initial === true) {
      return;
    }

    Drupal.announce(Drupal.t('New page loaded: !title', {
      '!title': document.title,
    }), 'assertive');

  });

}); })(document.documentElement, Drupal, jQuery, once);
