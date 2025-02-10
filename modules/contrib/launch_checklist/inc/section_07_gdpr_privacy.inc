<?php

/**
 * @file
 * Uses best practices to check for pre-launch best practices.
 */

/**
 * Generates and returns section 7 for launch_checklist.
 */
function section_07_gdpr_privacy($checklist_routes) {

  return [
    // Section 7 GDPR and Privacy.
    '#title' => t('GDPR and Privacy'),
    '#description' => t('<p>GDPR and Privacy settings for the Drupal site.</p>'),
    'approved_language' => [
      '#title' => t('Approved Language Notification'),
      '#description' => t('Include the approved language in the notification window.'),
      'handbook_page' => [
        '#text' => t('EU Cookie Compliance'),
        '#url' => $checklist_routes["eu_cookie_compliance"],
      ],
    ],
    'privacy_policy' => [
      '#title' => t('Privacy Policy'),
      '#description' => t('Make sure a Privacy Policy exists and that there is a link to it.'),
      'handbook_page' => [
        '#text' => t('Front page of site.'),
        '#url' => $checklist_routes["front_route"],
      ],
    ],
    'privacy_policy_cookies' => [
      '#title' => t('Privacy Policy Cookies'),
      '#description' => t('Ensure the privacy policy accurately specifies the cookies that the site serves.'),
      'handbook_page' => [
        '#text' => t('Front page of site.'),
        '#url' => $checklist_routes["front_route"],
      ],
    ],
    'gdpr_testing' => [
      '#title' => t('GDPR Testing'),
      '#description' => t('Perform GDPR testing on the final hosting solution.'),
      'handbook_page' => [
        '#text' => t('Front page of site.'),
        '#url' => $checklist_routes["front_route"],
      ],
    ],
  ];
}
