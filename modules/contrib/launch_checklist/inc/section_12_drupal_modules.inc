<?php

/**
 * @file
 * Uses best practices to check for pre-launch best practices.
 */

use Drupal\Core\Url;

/**
 * Generates and returns section 12 for launch_checklist.
 */
function section_12_drupal_modules($checklist_routes) {

  // Link attribute new tab.
  $attr = ['attributes' => ['target' => '_blank']];

  return [
  // Section 12 Drupal Modules.
    '#title' => t('Drupal Modules'),
    '#description' => t('<p>Drupal module settings for the Drupal site.</p>'),
    'cust_modules' => [
      '#title' => t('Custom Modules'),
      '#description' => t('Ensure that custom modules are in a "custom" folder.'),
    ],
    'coder_module' => [
      '#title' => t('Coder Module'),
      '#description' => t('Run the Coder module and fix any issues it discovers in the custom modules.'),
      'handbook_page' => [
        '#text' => t('Coder module'),
        '#url' => Url::fromUri('https://www.drupal.org/project/coder', $attr),
      ],
    ],
    'devel_module' => [
      '#title' => t('Devel Module'),
      '#description' => t('Ensure that the Devel module is disabled on the production site.'),
    ],
    'migration_module' => [
      '#title' => t('Migration Modules'),
      '#description' => t('Ensure that any and all migration modules are disabled and removed on the production site.'),
      'handbook_page' => [
        '#text' => t('Module Overview'),
        '#url' => Url::fromRoute('system.modules_list'),
      ],
    ],
  ];
}
