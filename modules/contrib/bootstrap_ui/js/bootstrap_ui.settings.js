/**
 * @file
 * Contains definition of the behaviour Bootstrap settings.
 */

(function ($, Drupal, drupalSettings, once) {
  "use strict";

  Drupal.behaviors.bootstrapSettings = {
    attach: function (context, settings) {
      // Get library type if existing.
      const library = $(once('check-edit-library', '#edit-library')).length > 0 ? $('#edit-library').val() : 'bootstrap';

      // The drupalSetSummary method required for this behavior is not available
      // on the Bootstrap settings page, so we need to make sure this
      // behavior is processed only if drupalSetSummary is defined.
      if (typeof $.fn.drupalSetSummary === 'undefined') {
        return;
      }

      $(once('bootstrap-file-types', '[data-drupal-selector="edit-file-types"]'),
      ).drupalSetSummary((context) => {
        const values = [];
        let $js_files = $(context).find('input[name="js"]:checked');
        let $css_files = $(context).find('input[name="css"]');

        if (library === 'bootstrap') {
          if ($js_files.val() == 'none') {
            values.push(
              Drupal.t('JavaScript Restricted'),
            );
          }
          else {
            values.push(
              Drupal.t('JavaScript !javascript', { '!javascript': $js_files.val().charAt(0).toUpperCase() + $js_files.val().slice(1) }),
            );
          }
        }
        else {
          $js_files = $(context).find('input[name="js"]');

          if ($js_files.prop('checked') === false) {
            values.push(Drupal.t('JavaScript Restricted'));
          } else {
            values.push(Drupal.t('JavaScript not restricted'));
          }
        }

        if (!$css_files.prop('checked')) {
          values.push(
            Drupal.t('CSS Restricted'),
          );
        }
        else {
          values.push(
            Drupal.t('CSS not restricted'),
          );
        }

        return values.join(',<br>');
      });

      $(once('bootstrap-theme-groups', '[data-drupal-selector="edit-theme-groups"]'),
      ).drupalSetSummary((context) => {
        const $themes = $(context).find(
          'select[name="themes\[\]"]',
        );
        if (!$themes.length || !$themes[0].value) {
          return Drupal.t('Not restricted');
        }

        return Drupal.t('Restricted to !theme', { '!theme': $themes.val() });
      });

      $(once('bootstrap-request-path', '[data-drupal-selector="edit-request-path"]'),
      ).drupalSetSummary((context) => {
        const $pages = $(context).find(
          'textarea[name="pages"]',
        );
        if (!$pages.length || !$pages[0].value) {
          return Drupal.t('Not restricted');
        }

        return Drupal.t('Restricted to certain pages');
      });

    }
  };

})(jQuery, Drupal, drupalSettings, once);
