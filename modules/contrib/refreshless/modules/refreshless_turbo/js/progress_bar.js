/**
 * @file
 * Implements our progress bar, replacing Turbo's implementation.
 *
 * @see https://www.drupal.org/project/refreshless/issues/3488464
 *   Issue for adapting this out of Turbo.
 */
((ProgressBar, ProgressBarDelay, $, delayAmount) => {

  'use strict';

  /**
   * RefreshLess Turbo progress bar class.
   */
  class TurboProgressBar {

    /**
     * The context element to attach to; usually the <html> element.
     *
     * @type {HTMLElement}
     */
    #context;

    /**
     * Progress bar class instance.
     *
     * @type {ProgressBar}
     */
    #progressBar;

    /**
     * Progress bar delay class instance.
     *
     * @type {ProgressBarDelay}
     */
    #progressBarDelay;

    constructor(context) {

      this.#context = context;

      this.#progressBar = new ProgressBar();

      // @todo Make delay amount configurable.
      this.#progressBarDelay = new ProgressBarDelay(
        delayAmount, this.#progressBar,
      );

      this.#bindEventHandlers();

    }

    /**
     * Bind all of our event handlers.
     */
    #bindEventHandlers() {

      $(this.#context).on({
        'refreshless:before-fetch-request': (event) => {
          this.#beforeFetchRequestHandler(event);
        },
        'refreshless:form-submit-start': (event) => {
          this.#submitStartHandler(event);
        },
      });

    }

    /**
     * 'refreshless:before-fetch-request' event handler.
     *
     * @param {jQuery.Event} event
     *
     * @todo Reduce duplicate code with submit handler.
     */
    #beforeFetchRequestHandler(event) {

      if (
        // Ignore prefetch requests.
        event.detail.isPrefetch === true ||
        // We want to specifically ignore forms as those are handled separately
        // via the 'refreshless:form-submit-start' and
        // 'refreshless:form-submit-response' event handlers.
        event.detail.isFormSubmit === true
      ) {
        return;
      }

      this.#progressBarDelay.showVisitAfterDelay();

      const hide = () => {

        this.#progressBarDelay.hideVisit();

        $(this.#context).off([
          'refreshless:before-render',
          'refreshless:reload',
          'refreshless:fetch-request-error',
        ].join(' '), hide);

      };

      $(this.#context).one([
        // We're using refreshless:before-render rather than refreshless:load
        // because the progress bar is noticeably less smoothly transitioned
        // out when loading some complex/heavy pages on mobile in some
        // browsers, specifically Firefox on Android. The
        // refreshless:before-render is early enough that it should give the
        // progress bar enough time to transition out before the new <body>
        // contents are rendered.
        'refreshless:before-render',
        // If a reload occurs as a result of this navigation, hide the progress
        // bar. Since we're using the before-render event, that would never get
        // triggered during a reload since Turbo isn't about to render
        // anything.
        'refreshless:reload',
        'refreshless:fetch-request-error',
      ].join(' '), hide);

    }

    /**
     * 'refreshless:form-submit-start' event handler.
     *
     * @param {jQuery.Event} event
     *
     * @todo Reduce duplicate code with before fetch request handler.
     */
    #submitStartHandler(event) {

      this.#progressBarDelay.showFormAfterDelay();

      const hide = () => {

        this.#progressBarDelay.hideForm();

        $(this.#context).off([
          'refreshless:form-submit-response',
          'refreshless:fetch-request-error',
        ].join(' '), hide);

      };

      $(this.#context).one([
        'refreshless:form-submit-response',
        'refreshless:fetch-request-error',
      ].join(' '), hide);

    }

  }

  // Merge Drupal.RefreshLess.classes into the existing Drupal global.
  $.extend(true, Drupal, {RefreshLess: {classes: {
    TurboProgressBar: TurboProgressBar,
  }}});

})(
  Drupal.RefreshLess.classes.ProgressBar,
  Drupal.RefreshLess.classes.ProgressBarDelay,
  jQuery,
  Turbo.session.progressBarDelay,
);
