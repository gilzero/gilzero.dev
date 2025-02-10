<?php

/**
 * @file
 * Uses best practices to check for pre-launch best practices.
 */

use Drupal\Core\Url;

/**
 * Generates and returns section 11 for launch_checklist.
 */
function section_11_accessibility($checklist_routes) {

  // Link attribute new tab.
  $attr = ['attributes' => ['target' => '_blank']];

  return [
    // Section 11 Accessibility.
    '#title' => t('Accessibility'),
    '#description' => t('<p>Accessibility settings for the Drupal site.</p>'),
    'lighthouse' => [
      '#title' => t('Lighthouse Audit'),
      '#description' => t('Running a Google Lighthouse test on your homepage and primary templates will help complete the other sections of this list.'),
      'handbook_page' => [
        '#text' => t('Google Lighthouse Docs'),
        '#url' => Url::fromUri('https://developers.google.com/web/tools/lighthouse', $attr),
      ],
    ],
    'screaming_frog' => [
      '#title' => t('Screaming Frog Audit'),
      '#description' => t('Running a Screaming Frog Audit on your site will help complete the other sections of this list.'),
      'handbook_page' => [
        '#text' => t('Screaming Frog SEO Spider'),
        '#url' => Url::fromUri('https://www.screamingfrog.co.uk/seo-spider/', $attr),
      ],
    ],
    'accessibility_checker' => [
      '#title' => t('Accessibility Checker'),
      '#description' => t('Run an accessibility checker, such as WAVE or Axe, to check for any outstanding accessibility issues.'),
      'handbook_page_1' => [
        '#text' => t('WAVE Web Accessibility Evaluation Tool'),
        '#url' => Url::fromUri('https://wave.webaim.org/', $attr),
      ],
      'handbook_page_2' => [
        '#text' => t('Axe'),
        '#url' => Url::fromUri('https://www.deque.com/axe/', $attr),
      ],
    ],
    'alt_tags' => [
      '#title' => t('Image Alt Tags'),
      '#description' => t('Ensure that all images have alt tags.'),
    ],
    'color_contrast' => [
      '#title' => t('Color Contrast'),
      '#description' => t('Ensure that colors have the proper contrast to meet accessibility requirements.'),
    ],
    'links' => [
      '#title' => t('Links'),
      '#description' => t('Make sure links are specific to what will happen when clicked (ie: "Read More" and "Click Here" are too vague).'),
    ],
    'headings' => [
      '#title' => t('Headings'),
      '#description' => t('Ensure headings are properly assigned.'),
    ],
    'accessibility_checklist' => [
      '#title' => t('Accessibility Checklist'),
      '#description' => t('For a more comprehensive accessibility check, visit the A11y Project Checklist.'),
      'handbook_page' => [
        '#text' => t('The A11y Project Checklist'),
        '#url' => Url::fromUri('https://www.a11yproject.com/checklist/', $attr),
      ],
    ],
  ];
}
