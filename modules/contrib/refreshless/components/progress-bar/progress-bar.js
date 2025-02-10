/**
 * @file
 * RefreshLess progress bar.
 *
 * @see https://www.drupal.org/project/refreshless/issues/3488464
 *   Issue for adapting this out of Turbo.
 *
 * @see https://github.com/hotwired/turbo/blob/ea54ae5ad4b6b28cb62ccd62951352641ed08293/src/core/drive/progress_bar.js
 *   Originally adapted from the Turbo progress bar.
 */
((Drupal, $) => {

  'use strict';

  Drupal.theme.refreshlessProgressBar = (
    elementType   = 'div',
    elementClass  = 'refreshless-document-progress-bar',
  ) => {

    const id = 'refreshless-document-progress-bar';

    const container = document.createElement(elementType);

    container.classList.add(elementClass);

    // This provides the ProgressBar JavaScript class the configured class name
    // to build modifier classes from.
    container.refreshlessProgressBarClass = elementClass;

    const progress = document.createElement('progress');

    // There are still (as of 2024 (!!!)) various issues trying to style and
    // transition the <progress> element's pseudo-elements across all browsers,
    // so the <progress> exists primarily for accessibility reasons. The
    // container is used as the visual progress bar, while the <progress>
    // element is set as visually hidden.
    progress.classList.add(`${elementClass}__progress`, 'visually-hidden');
    // The ID is intentionally hard-coded since it's only intended to be used
    // for the aria-describedby we set on the <html> element when the progress
    // bar is installed.
    progress.setAttribute('id', 'refreshless-document-progress-bar');
    progress.setAttribute('max', '100');
    progress.setAttribute('aria-label', Drupal.t('Page is loading...'));

    container.appendChild(progress);

    return container;

  }

  /**
   * CSS custom property base name.
   *
   * @type {String}
   */
  const customPropertyBase = '--refreshless-progress-bar';

  /**
   * Name of the CSS custom property containing the transition duration.
   *
   * The value will be a time in ms.
   *
   * @type {String}
   */
  const durationCustomProperty = `${customPropertyBase}-transition-duration`;

  /**
   * Name of the CSS custom property containing the current progress bar value.
   *
   * The value will be float between 0 and 1, inclusive.
   *
   * @type {String}
   */
  const valueCustomProperty = `${customPropertyBase}-value`;

  /**
   * The <html> element.
   *
   * @type {HTMLHtmlElement}
   */
  const html = document.documentElement;

  /**
   * Represents a progress bar.
   */
  class ProgressBar {

    /**
     * The progress bar container HTML element.
     *
     * @type {HTMLElement}
     */
    #containerElement;

    /**
     * The progress bar <progress> HTML element.
     *
     * @type {HTMLProgressElement}
     */
    #progressElement;

    /**
     * Whether the progress bar is currently in the processing of hiding.
     *
     * @type {Boolean}
     */
    #hiding = false;

    /**
     * The current value of the progress bar, from 0 to 1, inclusive.
     *
     * @type {Number}
     */
    #value = 0;

    /**
     * Whether the progress bar is currently visible.
     *
     * @type {Boolean}
     */
    #visible = false;

    /**
     * The trickle interval ID, or null if one is not active.
     *
     * @type {Number|null}
     */
    #trickleInterval = null;

    /**
     * The progress bar transition duration, in milliseconds.
     *
     * @type {Number}
     */
    #transitionDuration = 300;

    constructor() {

      this.#containerElement = Drupal.theme('refreshlessProgressBar');

      this.#progressElement = this.#containerElement.querySelector('progress');

      this.setValue(0, false);

    }

    /**
     * Show the progress bar if not already visible.
     *
     * @return {Promise}
     *   A Promise that resolves if the progress bar was show, or if the
     *   progress bar is already visible. The Promise is rejected if the
     *   progress bar was not shown, usually due to the
     *   'refreshless:progress-bar-before-show' event being cancelled by a
     *   handler.
     *
     * @todo Only resolve 'shown' when progress bar has finished transitioning
     *   in for consistency with hide().
     */
    show() {

      if (this.#visible === true) {
        return Promise.resolve('already-visible');
      }

      const beforeShowEvent = new CustomEvent(
        'refreshless:progress-bar-before-show',
      );

      html.dispatchEvent(beforeShowEvent);

      if (beforeShowEvent.defaultPrevented === true) {
        return Promise.reject('cancelled');
      }

      this.#visible = true;
      this.install();
      this.startTrickling();

      html.dispatchEvent(new CustomEvent('refreshless:progress-bar-shown'));

      return Promise.resolve('shown');

    }

    /**
     * Hide the progress bar if visible and not already hiding.
     *
     * @return {Promise}
     *   A Promise that resolves when the progress bar has finished
     *   transitioning out, or if the progress bar is already hidden or in the
     *   process of hiding. The Promise is rejected if the progress bar was not
     *   hidden, usually due to the 'refreshless:progress-bar-before-hide' event
     *   being cancelled by a handler.
     */
    hide() {

      if (this.#visible === false) {
        return Promise.resolve('already-hidden');
      }

      if (this.#hiding === true) {
        return Promise.resolve('already-hiding');
      }

      const beforeHideEvent = new CustomEvent(
        'refreshless:progress-bar-before-hide',
      );

      html.dispatchEvent(beforeHideEvent);

      if (beforeHideEvent.defaultPrevented === true) {
        return Promise.reject('cancelled');
      }

      this.#hiding = true;

      this.stopTrickling();

      return new Promise(async (resolve, reject) => {

        this.#setInactive();

        await new Promise((resolve, reject) => {

          window.setTimeout(resolve, this.#transitionDuration * 1.5);

        });

        this.uninstall();
        this.#visible = false;
        this.#hiding = false;

        html.dispatchEvent(new CustomEvent('refreshless:progress-bar-hidden'));

        resolve('hidden');

      });

    }

    /**
     * Set the progress bar as active, causing CSS to transition it in.
     *
     * @param {Boolean} dispatch
     */
    #setActive(dispatch = true) {

      this.#containerElement.classList.add(
        `${this.#containerElement.refreshlessProgressBarClass}--active`,
      );

      if (dispatch === false) {
        return;
      }

      html.dispatchEvent(new CustomEvent('refreshless:progress-bar-active'));

    }

    /**
     * Set the progress bar as inactive, causing CSS to transition it out.
     *
     * @param {Boolean} dispatch
     *   Whether to dispatch an event. Defaults to true.
     */
    #setInactive(dispatch = true) {

      this.#containerElement.classList.remove(
        `${this.#containerElement.refreshlessProgressBarClass}--active`,
      );

      if (dispatch === false) {
        return;
      }

      html.dispatchEvent(new CustomEvent('refreshless:progress-bar-inactive'));

    }

    /**
     * Explicitly set the progress bar to a value.
     *
     * @param {Number} value
     *   A number between 0 and 1, inclusive.
     *
     * @param {Boolean} dispatch
     *   Whether to dispatch an event. Defaults to true.
     *
     * @throws If value is NaN, or if the value is less than 0 or greater than
     *   1.
     */
    setValue(value, dispatch = true) {

      if (Number.isNaN(value)) {
        throw `Progress bar value must be a number! Got: "${typeof value}"`;
      }

      if (value < 0 || value > 1) {
        throw `Progress bar value must be an integer or float between 0 and 1! Got: "${value}"`;
      }

      const valueChanged = (this.#value !== value);

      const oldValue = this.#value;

      this.#value = value;

      return this.refresh().then(() => {

        if (dispatch === false || valueChanged === false) {
          return;
        }

        html.dispatchEvent(new CustomEvent(
          'refreshless:progress-bar-value-changed', {detail: {
            value:    this.#value,
            oldValue: oldValue,
          }}
        ));

      });

    }

    /**
     * Install the progress bar in the document and set various properties.
     */
    install() {

      html.style.setProperty(
        durationCustomProperty, `${this.#transitionDuration}ms`,
      );

      html.style.setProperty(valueCustomProperty, 0);

      this.#setInactive(false);

      document.body.insertAdjacentElement(
        'beforebegin', this.#containerElement,
      );

      // The progress bar is technically describing the current state of the
      // document so mark it as such. This may be helpful to assistive software
      // such as screen readers.
      html.setAttribute(
        'aria-describedby', this.#progressElement.getAttribute('id'),
      );

      this.refresh().then(async () => {

        await new Promise(requestAnimationFrame);

        this.#setActive();

      });

    }

    /**
     * Uninstall the progress bar from the document and remove properties.
     */
    uninstall() {

      if (this.#containerElement.parentNode) {
        this.#containerElement.remove();
      }

      html.style.removeProperty(durationCustomProperty);
      html.style.removeProperty(valueCustomProperty);

      if (html.getAttribute(
        'aria-describedby',
      ) === this.#progressElement.getAttribute('id')) {
        html.removeAttribute('aria-describedby');
      }

    }

    /**
     * Start the trickling animation.
     */
    startTrickling() {

      if (this.#trickleInterval !== null) {
        return;
      }

      this.#trickleInterval = window.setInterval(
        this.trickle, this.#transitionDuration,
      );

    }

    /**
     * Stop the trickling animation.
     */
    stopTrickling() {

      window.clearInterval(this.#trickleInterval);

      this.#trickleInterval = null;

    }

    /**
     * Trickle animation interval callback.
     *
     * This generates a random value to give the trickle the irregular movement.
     */
    trickle = () => {
      this.setValue(Math.min(1, this.#value + Math.random() / 100));
    }

    /**
     * Update the progress bar element's value with the current value.
     */
    async refresh() {

      // Don't write the custom property if not visible or if currently hiding.
      if (!(this.#visible === true && this.#hiding === false)) {
        return;
      }

      if (this.#value === 1) {
        this.stopTrickling();
      }

      await new Promise(requestAnimationFrame);

      html.style.setProperty(valueCustomProperty, this.#value);

      this.#progressElement.setAttribute('value', this.#value * 100);

      this.#progressElement.innerText = `${Math.round(this.#value * 100)}%`;

    }

    /**
     * Finish/complete the progress bar by setting to 100% and start hiding it.
     *
     * @return {Promise}
     */
    finish() {

      html.dispatchEvent(new CustomEvent('refreshless:progress-bar-finish'));

      return this.setValue(1).then(() => this.hide());

    }

    /**
     * Get the current value of the progress bar.
     *
     * @return {Number}
     */
    get value() {
      return this.#value;
    }

    /**
     * Get the progress bar container HTML element.
     *
     * @return {HTMLElement}
     */
    get element() {
      return this.#containerElement;
    }

    /**
     * Get the progress bar transition value.
     *
     * @return {Number}
     */
    get transitionDuration() {
      return this.#transitionDuration;
    }

  }

  // Merge Drupal.RefreshLess.classes into the existing Drupal global.
  $.extend(true, Drupal, {RefreshLess: {classes: {
    ProgressBar: ProgressBar,
  }}});

})(Drupal, jQuery);
