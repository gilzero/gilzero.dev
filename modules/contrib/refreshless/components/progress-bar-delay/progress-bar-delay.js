/**
 * @file
 * RefreshLess progress bar delay.
 *
 * @see https://www.drupal.org/project/refreshless/issues/3488464
 *   Issue for adapting this out of Turbo.
 *
 * @see https://github.com/hotwired/turbo/blob/ea54ae5ad4b6b28cb62ccd62951352641ed08293/src/core/drive/progress_bar.js
 *   Originally adapted from the Turbo progress bar.
 */
((Drupal, $) => {

  'use strict';

  /**
   * CSS custom property base name.
   *
   * @type {String}
   */
  const customPropertyBase = '--refreshless-progress-bar';

  /**
   * Name of the CSS custom property containing the progress bar delay.
   *
   * The value will be a time in ms.
   *
   * @type {String}
   */
  const delayCustomProperty = `${customPropertyBase}-delay`;

  /**
   * The <html> element.
   *
   * @type {HTMLHtmlElement}
   */
  const html = document.documentElement;

  /**
   * Represents a progress bar delay wrapper.
   */
  class ProgressBarDelay {

    /**
     * Time in milliseconds before the progress bar is shown.
     *
     * @type {Number}
     */
    #delay = 0;

    /**
     * The visit timeout ID, if any.
     *
     * @type {Number|null}
     */
    #visitTimeout = null;

    /**
     * The form submit timeout ID, if any.
     *
     * @type {Number|null}
     */
    #formTimeout = null;

    /**
     * The progress bar instance that we're wrapping.
     *
     * @type {ProgressBar}
     */
    #progressBar;

    constructor(delay, progressBar) {

      this.#delay = delay;

      this.#progressBar = progressBar;

    }

    /**
     * Install the progress bar in the document and set various properties.
     */
    install() {

      html.style.setProperty(
        delayCustomProperty, `${this.#delay}ms`,
      );

    }

    /**
     * Uninstall the progress bar from the document and remove properties.
     */
    uninstall() {

      html.style.removeProperty(delayCustomProperty);

    }

    /**
     * Start the timeout to show the progress bar.
     *
     * @param {Number|null} timeoutId
     *   An existing timeout ID, if any.
     *
     * @return {Number}
     *   A new timeout ID.
     */
    showAfterDelay(timeoutId) {

      if (timeoutId !== null) {
        window.clearTimeout(timeoutId);
      }

      this.install();

      return window.setTimeout(async () => {

        await this.#progressBar.setValue(0);

        this.#progressBar.show();

      }, this.#delay);

    }

    /**
     * Hide the progress bar and cancel an existing timeout, if any.
     *
     * @param {Number|null} timeoutId
     *
     * @return {null}
     */
    hideAndCancelDelay(timeoutId) {

      if (timeoutId === null) {
        return timeoutId;
      }

      window.clearTimeout(timeoutId);

      timeoutId = null;

      this.#progressBar.finish().then(() => this.uninstall());

      return timeoutId;

    }

    /**
     * Show the progress bar after a delay for non-form submission visits.
     */
    showVisitAfterDelay() {

      this.#visitTimeout = this.showAfterDelay(this.#visitTimeout);

    }

    /**
     * Hide the progress bar for non-form submission visits.
     */
    hideVisit() {

      this.#visitTimeout = this.hideAndCancelDelay(this.#visitTimeout);

    }

    /**
     * Show the progress bar after a delay for form submissions.
     */
    showFormAfterDelay() {

      // Unlike the visit progress bar, we prefer to not replace an existing
      // form submission progress bar.
      //
      // @todo Is this necessary? Don't browsers by default treat both types the
      //   same when showing progress?
      if (this.#formTimeout !== null) {
        return;
      }

      this.#formTimeout = this.showAfterDelay(this.#formTimeout);

    }

    /**
     * Hide the progress bar for form submissions.
     */
    hideForm() {

      this.#formTimeout = this.hideAndCancelDelay(this.#formTimeout);

    }

    /**
     * Get the progress bar instance we're wrapping.
     *
     * @return {ProgressBar}
     */
    get progressBar() {
      return this.#progressBar;
    }

  }

  // Merge Drupal.RefreshLess.classes into the existing Drupal global.
  $.extend(true, Drupal, {RefreshLess: {classes: {
    ProgressBarDelay: ProgressBarDelay,
  }}});

})(Drupal, jQuery);
