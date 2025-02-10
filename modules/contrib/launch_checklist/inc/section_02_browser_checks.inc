<?php

/**
 * @file
 * Uses best practices to check for pre-launch best practices.
 */

use Drupal\Core\Url;

/**
 * Generates and returns section 2 for launch_checklist.
 */
function section_02_browser_checks($checklist_routes) {

  // Link attribute new tab.
  $attr = ['attributes' => ['target' => '_blank']];

  return [
    // Section 2 Browser Checks.
    '#title' => t('Browser Checks'),
    '#description' => t('<p>Verify the look and functionality across devices.</p>'),
    'desktop' => [
      '#title' => t('Verify the site looks and behaves as expected on Desktop'),
      '#description' => t('Test on the latest two versions of browsers, including Google Chrome, Mozilla Firefox, Safari, Internet Explorer, Edge and Opera'),
      'handbook_page' => [
        '#text' => t('Use a Cross Browser Testing Tool like Browserstack'),
        '#url' => Url::fromUri('https://www.browserstack.com/', $attr),
      ],
    ],
    'mobile' => [
      '#title' => t('Verify the site looks and behaves as expected on mobile'),
      '#description' => t('Testing should also be completed on various mobile devices and mobile viewport sizes.'),
      'handbook_page' => [
        '#text' => t('Use a Cross Browser Testing Tool like Browserstack'),
        '#url' => Url::fromUri('https://www.browserstack.com/', $attr),
      ],
    ],
  ];
}
