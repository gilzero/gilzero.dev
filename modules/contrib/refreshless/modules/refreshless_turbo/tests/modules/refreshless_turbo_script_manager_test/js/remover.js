((html, $, once) => { $(once(
  'refreshless-turbo-reload-reason', html,
)).each(() => {

  'use strict';

  /**
   * The name of the attribute to find <script> elements to remove by.
   *
   * @type {String}
   */
  const removeAttribute = 'data-refreshless-turbo-script-manager-test-remove';

  $(html).on('refreshless:before-scripts-merge', (event) => {

    // This should prevent merging of any <script> elements designated with our
    // attribute.
    event.detail.new = $(event.detail.new).not(
      `[${removeAttribute}]`,
    ).toArray();

  });

}); })(document.documentElement, jQuery, once);
