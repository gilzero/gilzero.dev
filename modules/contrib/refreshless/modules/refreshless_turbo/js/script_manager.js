((Drupal, $) => {

  'use strict';

  /**
   * RefreshLess Turbo script manager class.
   */
  class ScriptManager {

    /**
     * The context element to attach to; usually the <html> element.
     *
     * @type {HTMLElement}
     */
    #context;

    /**
     * Object of arrays of <script> element loading Promises.
     *
     * These are split into <head> and <body> contexts. They start a page load
     * cycle as null to indicate that that context has not yet had a chance to
     * track any <script> elements yet, to differentiate it from an empty array
     * that will occur if the context has had a chance to track new <script>
     * elements but none were found.
     *
     * @type {Object}
     *
     * @todo Can we instead set each context to a Promise that's pending and
     *   then when <script> elements have been found for that context, wrap the
     *   context's Promises in its own Promise.allSettled()?
     */
    #scriptPromises = {head: null, body: null};

    constructor(context) {

      this.#context = context;

      this.#bindEventHandlers();

    }

    /**
     * Bind all of our event handlers.
     */
    #bindEventHandlers() {

      $(this.#context).on({
        'turbo:before-scripts-merge': (event) => {
          this.#beforeMergeHandler(event);
        },
        'turbo:scripts-merged': (event) => {
          this.#mergedHandler(event);
        },
      });

    }

    /**
     * 'turbo:before-scripts-merge' event handler.
     *
     * @param {jQuery.Event} event
     */
    #beforeMergeHandler(event) {

      const beforeMergeEvent = new CustomEvent(
        'refreshless:before-scripts-merge', {
          detail: event.detail,
        },
      );

      this.#context.dispatchEvent(beforeMergeEvent);

      // Now that event handlers have had a chance to alter the <script>
      // elements to be merged, create Promises for each so that we can resolve
      // once all of them have loaded in order to trigger the
      // refreshless:scripts-loaded event.

      // If the context is not an array at this point, set it to an empty array
      // both to indicate that this context has checked in and to allow pushing
      // new Promises onto it.
      if (Array.isArray(this.#scriptPromises[event.detail.context]) === false) {
        this.#scriptPromises[event.detail.context] = [];
      }

      for (const element of beforeMergeEvent.detail.new) {

        // Ignore any <script> elements that don't have a 'src' attribute so
        // that we don't wait for something that won't ever load.
        //
        // Note that this also ignores the drupalSettings JSON element so we
        // don't need a separate check for that.
        if (typeof $(element).attr('src') === 'undefined') {
          continue;
        }

        this.#createScriptPromise(element, event.detail.context);

      }

    }

    /**
     * 'turbo:scripts-merged' event handler.
     *
     * @param {jQuery.Event} event
     */
    #mergedHandler(event) {

      const mergeEvent = new CustomEvent(
        'refreshless:scripts-merged', {
          detail: event.detail,
        },
      );

      this.#context.dispatchEvent(mergeEvent);

      // If both contexts have checked in, this will trigger the load event once
      // all their Promises have resolved. Note that there's probably no reason
      // to use await with this here.
      this.#awaitBothContextLoads();

    }

    /**
     * Create a Promise that resolves or rejects when a <script> loads/errors.
     *
     * This creates a Promise for the <script> element with the resolve() and
     * reject() callbacks assigned as the load and error event handlers,
     * respectively, and pushes that Promise onto this.#scriptPromises.
     *
     * @param {HTMLScriptElement} element
     *   A <script> element add a load/error Promise for.
     */
    #createScriptPromise(element, context) {

      this.#scriptPromises[context].push(new Promise((resolve, reject) => {

        $(element).one({'load': resolve, 'error': reject});

      }));

    }

    /**
     * Trigger 'refreshless:scripts-loaded' when all new scripts have loaded.
     *
     * Note that this is expected to be called more than once, and will only
     * trigger if both <head> and <body> script Promises are defined, after
     * which it will await them all and unset them before triggering the event.
     */
    async #awaitBothContextLoads() {

      // Only proceed once both contexts have checked in.
      if (
        this.#scriptPromises.head === null ||
        this.#scriptPromises.body === null
        ) {
        return;
      }

      // Only when all of the Promises have settled - that is they've either
      // resolved or rejected, i.e. loaded or failed to load - do we trigger
      // the load event.
      const promises = await Promise.allSettled(
        this.#scriptPromises.head.concat(this.#scriptPromises.body),
      );

      // Remove the existing Promises and set them back to the starting values
      // of null now that they've all settled.
      this.#scriptPromises.head = null;
      this.#scriptPromises.body = null;

      const scripts = promises.map((item) => item.value.target);

      const loadedEvent = new CustomEvent(
        'refreshless:scripts-loaded', {
          detail: {loaded: scripts},
        },
      );

      this.#context.dispatchEvent(loadedEvent);

    }

  }

  // Merge Drupal.RefreshLess.classes into the existing Drupal global.
  $.extend(true, Drupal, {RefreshLess: {classes: {
    TurboScriptManager: ScriptManager,
  }}});

})(Drupal, jQuery);
