<?php

/**
 * @file
 * Uses best practices to check for pre-launch best practices.
 */

/**
 * Generates and returns section 10 for launch_checklist.
 */
function section_10_theme($checklist_routes) {

  return [
    // Section 10 Theme.
    '#title' => t('Theme'),
    '#description' => t('<p>Theme settings for the Drupal site.</p>'),

    'responsive_design' => [
      '#title' => t('Responsive Design'),
      '#description' => t('For responsive designs, check that the theme is responding properly at all resolutions and orientations, and that there are no visual issues.'),
      'handbook_page' => [
        '#text' => t('Front page of site.'),
        '#url' => $checklist_routes["front_route"],
      ],
    ],
    'custom_js' => [
      '#title' => t('Custom Javascript'),
      '#description' => t('If there are any custom JS files in the theme verify if they should be added to every page.'),
    ],
    'theme_structure' => [
      '#title' => t('Theme Structure'),
      '#description' => t('Make sure the theme is well structured and that there are no unused image/css/js files present.'),
    ],
  ];
}
