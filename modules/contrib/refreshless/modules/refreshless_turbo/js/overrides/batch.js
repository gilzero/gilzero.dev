/**
 * @file
 * Drupal batch API progress behaviour override for RefreshLess.
 *
 * This improves upon core's behaviour with the following:
 *
 * - If Turbo is available and Turbo Drive isn't disabled, Turbo.visit() will be
 *   used once the batch completes to redirect to the summary; this prevents the
 *   otherwise full page load core's batch.js would do. This falls back to
 *   setting window.location if Turbo isn't available or Turbo Drive is
 *   disabled.
 *
 * - Provides a detach that stops the progress bar if it's still running so that
 *   the redirect to the summary doesn't occur if the user navigated to another
 *   page before it had a chance to complete. Without this, the redirect would
 *   still occur even after navigating to another page when the batch completes,
 *   which would be confusing and unexpected. Core doesn't have to worry about
 *   this because it assumes there's a full page load on the redirect.
 *
 * - Uses the context parameter to scope the behaviour correctly.
 *
 * - Cleans up most of the code, reduces indent hell, and adds more comments and
 *   docblocks.
 *
 * @see core/misc/batch.js
 *
 * @see https://turbo.hotwired.dev/reference/drive
 */
((Drupal, RefreshLess, $, once) => {

  /**
   * Attaches the batch behavior to progress bars.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.batch = {

    attach(context, settings) {

      /**
       * Batch settings from the back-end.
       *
       * @type {Object}
       */
      const batch = settings.batch;

      /**
       * Zero or more progress bar element(s) wrapped in a jQuery collection.
       *
       * @type {jQuery}
       */
      const $progress = $(once('batch', '[data-drupal-progress]', context));

      /**
       * Progress bar update callback.
       *
       * @param {Number} percentage
       *   The progress percentage.
       *
       * @param {String} message
       *   The message to show the user.
       *
       * @param {Drupal~ProgressBar} progressBar
       *   The progress bar instance.
       */
      const updateCallback = (percentage, message, progressBar) => {

        if (percentage !== '100') {
          return;
        }

        progressBar.stopMonitoring();

        RefreshLess.visit(`${batch.uri}&op=finished`);

      };

      /**
       * Progress bar error callback. Shows error and hides the '#wait' element.
       *
       * @param {Drupal~ProgressBar} progressBar
       *   The progress bar instance.
       */
      const errorCallback = (progressBar) => {

        $progress.prepend($('<p class="error"></p>').html(batch.errorMessage));
        $('#wait').hide();

      };

      if ($progress.length === 0) {
        return;
      }

      /**
       * Progress bar instance.
       *
       * @type {Drupal~ProgressBar}
       */
      const progressBar = new Drupal.ProgressBar(
        'updateprogress',
        updateCallback,
        'POST',
        errorCallback,
      );

      progressBar.setProgress(-1, batch.initMessage);
      progressBar.startMonitoring(`${batch.uri}&op=do`, 10);

      // Remove HTML from no-js progress bar.
      $progress.empty();

      // Append the JS progressbar element.
      $progress.append(progressBar.element);

      $progress.data('drupalProgressBar', progressBar);

    },

    detach(context, settings, trigger) {

      if (trigger !== 'unload') {
        return;
      }

      /**
       * Zero or more progress bar element(s) wrapped in a jQuery collection.
       *
       * @type {jQuery}
       */
      const $progress = $(once.remove(
        'batch', '[data-drupal-progress]', context,
      ));

      for (let i = 0; i < $progress.length; i++) {

        /**
         * Progress bar instance or undefined if not set by attach().
         *
         * @type {Drupal~ProgressBar|undefined}
         */
        const progressBar = $progress.eq(i).data('drupalProgressBar');

        if (
          typeof progressBar !== 'object' ||
          !('stopMonitoring' in progressBar)
        ) {
          continue;
        }

        progressBar.stopMonitoring();

      }

    },

  };

})(Drupal, Drupal.RefreshLess, jQuery, once);
