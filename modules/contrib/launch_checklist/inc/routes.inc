<?php

/**
 * @file
 * Uses best practices to check for pre-launch best practices.
 */

use Drupal\Core\Url;

/**
 * Module routes and URL fallbacks.
 */
function routes() {

  // Link attribute new tab.
  $attr = ['attributes' => ['target' => '_blank']];

  // Array that holds all of the generated routes.
  $checklist_routes = [];

  // Home page route.
  $checklist_routes["front_route"] = Url::fromRoute('<front>');

  // Check if module exists.
  $moduleHandler = \Drupal::service('module_handler');

  // EU Cookie Compliance.
  $checklist_routes["eu_cookie_compliance"] = Url::fromUri('https://www.drupal.org/project/eu_cookie_compliance', $attr);
  if ($moduleHandler->moduleExists('eu_cookie_compliance')) {
    $checklist_routes["eu_cookie_compliance"] = Url::fromRoute('eu_cookie_compliance.settings', $attr);
  }
  // Fields UI.
  $checklist_routes["field_ui"] = Url::fromUri('https://www.drupal.org/docs/8/core/modules/field-ui', $attr);
  if ($moduleHandler->moduleExists('field_ui')) {
    $checklist_routes["field_ui"] = Url::fromRoute('entity.field_storage_config.collection');
  }
  // Google Analytics.
  $checklist_routes["google_analytics"] = Url::fromUri('https://www.drupal.org/project/google_analytics', $attr);
  if ($moduleHandler->moduleExists('google_analytics')) {
    $checklist_routes["google_analytics"] = Url::fromRoute('google_analytics.admin_settings_form');
  }
  // Google Tag Manager.
  $checklist_routes["google_tag"] = Url::fromUri('https://www.drupal.org/project/google_tag', $attr);
  if ($moduleHandler->moduleExists('google_tag')) {
    $checklist_routes["google_tag"] = Url::fromRoute('google_tag.settings_form');
  }
  // Metatag.
  $checklist_routes["metatag"] = Url::fromUri('https://www.drupal.org/project/metatag', $attr);
  if ($moduleHandler->moduleExists('metatag')) {
    $checklist_routes["metatag"] = Url::fromRoute('entity.metatag_defaults.collection');
  }
  // Real AES.
  $checklist_routes['real_aes'] = Url::fromUri('https://www.drupal.org/project/real_aes', $attr);
  if ($moduleHandler->moduleExists('real_aes')) {
    $checklist_routes['manage_keys'] = Url::fromRoute('entity.key.collection');
  }
  // Redirect.
  $checklist_routes["redirect"] = Url::fromUri('https://www.drupal.org/project/redirect', $attr);
  if ($moduleHandler->moduleExists('redirect')) {
    $checklist_routes["redirect"] = Url::fromRoute('redirect.list');
  }
  // Schema.org Metatag.
  $checklist_routes["schema_metatag"] = Url::fromUri('https://www.drupal.org/project/schema_metatag', $attr);
  if ($moduleHandler->moduleExists('schema_metatag')) {
    $checklist_routes["schema_metatag"] = Url::fromRoute('entity.metatag_defaults.collection');
  }
  // Security Kit.
  $checklist_routes["seckit"] = Url::fromUri('https://www.drupal.org/project/seckit', $attr);
  if ($moduleHandler->moduleExists('seckit')) {
    $checklist_routes["seckit"] = Url::fromRoute('seckit.settings');
  }
  // Security Review.
  $checklist_routes["security_review"] = Url::fromUri('https://www.drupal.org/project/security_review', $attr);
  if ($moduleHandler->moduleExists('security_review')) {
    $checklist_routes["security_review"] = Url::fromRoute('security_review.settings');
  }
  // Simple XML sitemap.
  $checklist_routes["simple_sitemap"] = Url::fromUri('https://www.drupal.org/project/simple_sitemap', $attr);
  if ($moduleHandler->moduleExists('simple_sitemap')) {
    $checklist_routes["simple_sitemap"] = Url::fromRoute('simple_sitemap.sitemap_default');
  }
  // Unused modules.
  $checklist_routes["unused_modules"] = Url::fromUri('https://www.drupal.org/project/unused_modules', $attr);
  if ($moduleHandler->moduleExists('unused_modules')) {
    $checklist_routes["unused_modules"] = Url::fromRoute('unused_modules.overview.projects.all');
  }
  // Views UI.
  $checklist_routes["views_ui"] = Url::fromUri('https://www.drupal.org/docs/8/core/modules/views-ui', $attr);
  if ($moduleHandler->moduleExists('views_ui')) {
    $checklist_routes["views_ui"] = Url::fromRoute('entity.view.collection');
  }
  // Webform.
  $checklist_routes["webform"] = Url::fromUri('https://www.drupal.org/project/webform', $attr);
  if ($moduleHandler->moduleExists('webform')) {
    $checklist_routes["webform"] = Url::fromRoute('entity.webform.collection');
  }
  // XML Sitemap.
  $checklist_routes["xml_sitemap"] = Url::fromUri('https://www.drupal.org/project/xmlsitemap', $attr);
  if ($moduleHandler->moduleExists('xmlsitemap')) {
    $checklist_routes["xml_sitemap"] = Url::fromRoute('xmlsitemap.admin_search');
  }

  return $checklist_routes;

}
