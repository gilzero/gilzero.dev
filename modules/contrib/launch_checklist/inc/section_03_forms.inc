<?php

/**
 * @file
 * Uses best practices to check for pre-launch best practices.
 */

/**
 * Generates and returns section 3 for launch_checklist.
 */
function section_03_forms($checklist_routes) {

  return [
    // Section 3 Forms.
    '#title' => t('Forms'),
    '#description' => t('<p>Verify forms work as expected.</p>'),
    'email' => [
      '#title' => t('Form emails'),
      '#description' => t('Check all webforms for recipient email and test.'),
      'handbook_page' => [
        '#text' => t('Webform'),
        '#url' => $checklist_routes["webform"],
      ],
    ],
    'confirmation' => [
      '#title' => t('Form confirmation'),
      '#description' => t('Check all webforms for thank you message.'),
      'handbook_page' => [
        '#text' => t('Webform'),
        '#url' => $checklist_routes["webform"],
      ],
    ],
    'spam' => [
      '#title' => t('Spam protection'),
      '#description' => t('Ensure Spam prevention measures are in place.'),
    ],
  ];
}
