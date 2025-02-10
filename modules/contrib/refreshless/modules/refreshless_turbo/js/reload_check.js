/**
 * @file
 * Performs a non-Turbo page reload if evaluated and Turbo <meta> element found.
 *
 * This is specifically intended to handle situations where the browser has
 * unloaded the page and then restores it, only for it to be in a broken state
 * due to partially missing JavaScript. This can occur both with JavaScript
 * aggregation on and off.
 *
 * Note that since this will likely get executed when various libraries are not
 * present on the page, we can't rely on any other JavaScript outside of this;
 * it's in that exact case where they're missing that we have to force a reload,
 * so it's important that this does not fail.
 */
(() => {

  /**
   * Class added to the <head> element as a crude once() analog.
   *
   * This seems to be necessary when JavaScript aggregation is enabled to
   * prevent multiple evaluation of this file.
   *
   * @type {String}
   */
  const checkedClass = 'refreshless-turbo-reload-checked';

  /**
   * The <meta> element indicating whether or not a Turbo response.
   *
   * @type {HTMLMetaElement|null}
   */
  const metaElement = document.querySelector(
    'head > meta[name="refreshless-turbo-response"]',
  );

  if (
    // Crude once() analog.
    document.head.classList.contains(checkedClass) ||
    // Don't do anything if the <meta> element isn't found at all.
    metaElement === null ||
    // Don't do anything if it is present but it indicates that this was not a
    // Turbo response.
    metaElement.getAttribute('content') === 'false'
  ) {

    document.head.classList.add(checkedClass);

    return;

  }

  console.info(
    '%cRefreshLess%c: page may have been restored from being unloaded; forcing a reload to prevent potentially broken state.',
    'font-style: italic', 'font-style: normal',
  );

  window.location.reload();

})();
