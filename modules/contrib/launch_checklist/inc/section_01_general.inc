<?php

/**
 * @file
 * Uses best practices to check for pre-launch best practices.
 */

use Drupal\Core\Url;

/**
 * Generates and returns section 1 for launch_checklist.
 */
function section_01_general($checklist_routes) {

  return [
    // Section 1 General.
    '#title' => t('General'),
    '#description' => t('<p>General settings for the Drupal site.</p>'),
    'site_information_email' => [
      '#title' => t('Verify the site information uses client email'),
      '#description' => t('Ensure that the Drupal site email address is the correct client or site manager email address.'),
      'handbook_page' => [
        '#text' => t('Site Information Email'),
        '#url' => Url::fromRoute('system.site_information_settings'),
      ],
    ],
    'site_information_name' => [
      '#title' => t('Verify the site title tag is correct'),
      '#description' => t('Make sure the Site name field is correct.'),
      'handbook_page' => [
        '#text' => t('Site Information Name'),
        '#url' => Url::fromRoute('system.site_information_settings'),
      ],
    ],
    'favicon' => [
      '#title' => t('Favicon'),
      '#description' => t('Verify Favicon is correct.'),
      'handbook_page' => [
        '#text' => t('Front page of site.'),
        '#url' => $checklist_routes["front_route"],
      ],
    ],
  ];
}
