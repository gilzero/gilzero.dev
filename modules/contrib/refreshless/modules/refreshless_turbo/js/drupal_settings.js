((Drupal, drupalSettings, $) => {

  'use strict';

  /**
   * RefreshLess Turbo drupalSettings updater class.
   */
  class DrupalSettings {

    /**
     * The context element to attach to; usually the <html> element.
     *
     * @type {HTMLElement}
     */
    #context;

    /**
     * The most recent drupalSettings JSON <script> element, or undefined.
     *
     * @type {HTMLScriptElement|undefined}
     */
    #element;

    /**
     * The drupalSettings <script> element selector.
     *
     * Note that this doesn't include the leading '>' combinator for XSS
     * hardening.
     *
     * @type {String}
     *
     * @see core/misc/drupalSettingsLoader.js
     *   Explains the need for XSS hardening.
     */
    #selector = 'script[type="application/json"][data-drupal-selector="drupal-settings-json"]';

    constructor(context) {

      this.#context = context;

      this.#bindEventHandlers();

    };

    /**
     * Bind all of our event handlers.
     */
    #bindEventHandlers() {

      $(this.#context).on({
        'refreshless:scripts-merged': (event) => {
          this.#mergedHandler(event);
        },
      });

    }

    /**
     * 'refreshless:scripts-merged' event handler.
     *
     * Note that we could listen to the 'refreshless:before-scripts-merge' event
     * but that would make it more difficult to determine if the <script> is
     * a direct child of the <body> or an attempted XSS attack nested deeper in.
     *
     * @param {jQuery.Event} event
     */
    #mergedHandler(event) {

      // If an element has already been found, don't attempt to find another.
      // Because this event is triggered twice - once for the <head> and then
      // again for the <body>, this replicates the Drupal core behaviour of
      // using the one found in the <head>, only using one found in the <body>
      // if there isn't one in the <head>.
      if (typeof this.#element !== 'undefined') {
        return;
      }

      for (const element of event.detail.new) {

        if (
          // Skip <script> elements that aren't the drupalSettings JSON.
          $(element).is(this.#selector) === false ||
          // XSS hardening: skip any drupalSettings JSON that isn't a direct
          // child of the <head> or <body> as that could indicate a potential
          // <script> element injected into content as part of an attack.
          $(element).parent(event.detail.context).length === 0
        ) {
          continue;
        }

        // Now that we've found a new drupalSettings JSON element, we should
        // remove any previous element.
        $(element).siblings(this.#selector).remove();

        this.#element = element;

        return;

      }

    }

    /**
     * Update the drupalSettings object from the most recent <script> element.
     *
     * This invoked externally rather than on an event to ensure
     *
     * @throws Error
     *   If no settings element was found or if the settings element text could
     *   not be parsed into valid JSON.
     *
     * @return {Promise}
     *   A Promise that fulfills when drupalSettings has been updated.
     *
     * @todo Remove the need to invoke externally and do all this in event
     *   handlers.
     *
     * @todo Should we instead discard the current settings object rather than
     *   merging?
     */
    update() {

      if (typeof this.#element === 'undefined') {

        throw new Error(Drupal.t(
          'Could not find a Drupal settings <script> element!',
        ));

      }

      const $script = $(this.#element);

      /**
       * Clone of the previous drupalSettings before merging.
       *
       * @type {Object}
       */
      const previousSettings = $.extend(true, {}, drupalSettings);

      /**
       * New drupalSettings values or null if they can't be read.
       *
       * @type {Object|null}
       */
      const newSettings = JSON.parse($script.text());

      if (newSettings === null) {

        throw new Error(Drupal.t(
          `Could not parse the drupalSettings JSON from the <script> element. Got: @text`,
            {'@text': $script.text()},
        ));

      }

      $.extend(true, drupalSettings, newSettings);

      const resolvedValues = {
        new:      newSettings,
        previous: previousSettings,
        merged:   drupalSettings,
      }

      const settingsEvent = new CustomEvent(
        'refreshless:drupal-settings-update', {detail: resolvedValues},
      );

      this.#context.dispatchEvent(settingsEvent);

      // Unset the element now that we're done with it. This is necessary for
      // this.#mergedHandler() to replicate core's behaviour correctly.
      this.#element = undefined;

      return Promise.resolve(resolvedValues);

    };

  }

  // Merge Drupal.RefreshLess.classes into the existing Drupal global.
  $.extend(true, Drupal, {RefreshLess: {classes: {
    TurboDrupalSettings: DrupalSettings,
  }}});

})(Drupal, drupalSettings, jQuery);
