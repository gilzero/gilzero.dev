/**
 * @file
 * Manage core behaviors.
 */

(drupalSettings => {

  window.dsfr = {
    production: drupalSettings.dsfr4drupal.production,
    verbose: drupalSettings.dsfr4drupal.verbose,
    level: drupalSettings.dsfr4drupal.level,
  };

})(drupalSettings);
