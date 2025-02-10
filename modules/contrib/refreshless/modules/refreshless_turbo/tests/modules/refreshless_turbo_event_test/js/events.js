(($, once) => { $(once(
  'refreshless-turbo-event-test-events', document.documentElement,
)).each((i, html) => {

  /**
   * List element id attribute.
   *
   * @type {String}
   */
  const id = 'refreshless-turbo-event-test-events';

  /**
   * The list jQuery collection.
   *
   * @type {jQuery}
   */
  const $list = $('<ol></ol>').attr('id', id);

  /**
   * Log an event to the event list.
   *
   * @param {String} type
   *   The event type. Used by tests to determine the event the list item
   *   represents.
   *
   * @param {String|HTMLElement|jQuery} content
   *   Optional content to insert into the list item.
   */
  const logEvent = (type, content) => {
    $list.append($(`<li data-event-type="${type}"></li>`).append(content));
  };

  $(html)
  .on('refreshless:load', (event) => {

    // We want to insert this into the page content both on initial load and
    // after each navigation to ensure it's always attached.
    $(event.target).find('main').append($list);

  }).on('refreshless:attach', (event) => {

    logEvent('attach', 'attaching behaviours');

  }).on('refreshless:detach', (event) => {

    logEvent('detach', 'detaching behaviours');

  }).on('refreshless:navigation-response', (event) => {

    logEvent(
      'navigation-response', `navigated to <code>${
        event.detail.url.pathname
      }</code>`,
    );

  }).on('refreshless:drupal-settings-update', (event) => {

    logEvent(
      'drupal-settings-update', '<code>drupalSettings</code> has been updated',
    );

  });

}); })(jQuery, once);
