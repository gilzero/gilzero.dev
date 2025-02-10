((Drupal, drupalSettings, $) => {

  'use strict';

  /**
   * RefreshLess Turbo behaviours attach and detach class.
   */
  class Behaviours {

    /**
     * The context element to attach to; usually the <html> element.
     *
     * @type {HTMLElement}
     */
    #context;

    /**
     * A Promise that resolves when all newly merged scripts have loaded.
     *
     * @type {Promise|null}
     */
    #loadedPromise = null;

    constructor(context) {

      this.#context = context;

      this.#bindEventHandlers();

    };

    /**
     * Bind all of our event handlers.
     */
    #bindEventHandlers() {

      $(this.#context).on({
        'refreshless:before-scripts-merge': (event) => {
          this.#beforeMergeHandler(event);
        },
      });

    }

    /**
     * 'refreshless:before-scripts-merge' event handler.
     *
     * @param {jQuery.Event} event
     */
    #beforeMergeHandler(event) {

      // Since this event handler is triggered twice, avoid overwriting the
      // Promise if it exists.
      if (this.#loadedPromise !== null) {
        return;
      }

      // This needs to be registered before merging so that it's as early as
      // possible in the page load cycle.
      this.#loadedPromise = new Promise((resolve, reject) => {

        $(this.#context).one({'refreshless:scripts-loaded': resolve});

      });

    }

    /**
     * Attach behaviours.
     *
     * @return {Promise}
     *   A Promise that fulfills once Drupal.attachBehaviors() has been called.
     */
    async attach() {

      // Delay attaching until all script elements have loaded.
      await this.#loadedPromise;

      // Set the Promise back to null so our before merge handler knows to
      // create a new Promise on the next load.
      this.#loadedPromise = null;

      Drupal.attachBehaviors(this.#context, drupalSettings);

      const attachEvent = new CustomEvent('refreshless:attach', {
        // Since we're dispatching on an abitrary context element.
        bubbles: true,
      });

      this.#context.dispatchEvent(attachEvent);

      return Promise.resolve();

    };

    /**
     * Detach behaviours.
     *
     * @return {Promise}
     *   A Promise that fulfills when Drupal.detachBehaviors() has been called.
     */
    detach() {

      Drupal.detachBehaviors(this.#context, drupalSettings, 'unload');

      const detachEvent = new CustomEvent('refreshless:detach', {
        // Since we're dispatching on an abitrary context element.
        bubbles: true,
      });

      this.#context.dispatchEvent(detachEvent);

      return Promise.resolve();

    };

  }

  // Merge Drupal.RefreshLess.classes into the existing Drupal global.
  $.extend(true, Drupal, {RefreshLess: {classes: {
    TurboBehaviours: Behaviours,
  }}});

})(Drupal, drupalSettings, jQuery);
