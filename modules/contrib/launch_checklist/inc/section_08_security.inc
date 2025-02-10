<?php

/**
 * @file
 * Uses best practices to check for pre-launch best practices.
 */

use Drupal\Core\Url;

/**
 * Generates and returns section 8 for launch_checklist.
 */
function section_08_security($checklist_routes) {

  // Link attribute new tab.
  $attr = ['attributes' => ['target' => '_blank']];

  return [
    // Section 8 Performance and Security.
    '#title' => t('Performance and Security'),
    '#description' => t('<p>Performance and Security settings for the Drupal site.</p>'),
    'page_caching' => [
      '#title' => t('Page Caching'),
      '#description' => t('Ensure that page caching is enabled.'),
      'handbook_page' => [
        '#text' => t('Site Performance Settings'),
        '#url'  => Url::fromRoute('system.performance_settings'),
      ],
    ],
    'css_aggregation' => [
      '#title' => t('CSS Aggregation'),
      '#description' => t('Ensure that CSS aggregation is enabled.'),
      'handbook_page' => [
        '#text' => t('Site Performance Settings'),
        '#url' => Url::fromRoute('system.performance_settings'),
      ],
    ],
    'js_aggregation' => [
      '#title' => t('JS Aggregation'),
      '#description' => t('Ensure that JS aggregation is enabled.'),
      'handbook_page' => [
        '#text' => t('Site Performance Settings'),
        '#url' => Url::fromRoute('system.performance_settings'),
      ],
    ],
    'visibility' => [
      '#title' => t('Block Visibility'),
      '#description' => t('Check the visibility setting for every enabled block and make sure that the visibility (pages/content types/roles/users) is restricted appropriately.'),
      'handbook_page' => [
        '#text' => t('Block layout'),
        '#url' => Url::fromRoute('block.admin_display'),
      ],
    ],
    'php_filter' => [
      '#title' => t('PHP Filter'),
      '#description' => t('Ensure that the PHP filter is disabled.'),
      'handbook_page' => [
        '#text' => t('Modules'),
        '#url' => Url::fromRoute('system.modules_list'),
      ],
    ],
    'block_caching' => [
      '#title' => t('Block Caching'),
      '#description' => t('Ensure that block caching is turned on, if possible.'),
      'handbook_page' => [
        '#text' => t('Views'),
        '#url' => $checklist_routes['views_ui'],
      ],
    ],
    'min_max_cache' => [
      '#title' => t('Min/Max Cache Lifetime'),
      '#description' => t('Ensure that the minimum/maximum cache lifetime are set if/as necessary.'),
      'handbook_page' => [
        '#text' => t('Views'),
        '#url' => $checklist_routes['views_ui'],
      ],
    ],
    'unused_modules' => [
      '#title' => t('Unused Modules'),
      '#description' => t('Ensure that all modules not in use are disabled and uninstalled.'),
      'handbook_page' => [
        '#text' => t('Unused modules'),
        '#url' => $checklist_routes['unused_modules'],
      ],
    ],
    'dev_modules' => [
      '#title' => t('Development Modules'),
      '#description' => t('Ensure that development modules (eg. Views/UI/Rules UI) are disabled. The UI modules should only be enabled when they are actually being used and disabled again afterwards.'),
      'handbook_page' => [
        '#text' => t('Modules'),
        '#url' => Url::fromRoute('system.modules_list'),
      ],
    ],
    'tracking_modules' => [
      '#title' => t('Tracking Modules'),
      '#description' => t('Ensure modules such as Statistics and Tracker (and any other logging modules) are disabled (unless absolutely necessary or as requested by a client) to reduce overhead on every page load.'),
      'handbook_page' => [
        '#text' => t('Modules'),
        '#url' => Url::fromRoute('system.modules_list'),
      ],
    ],
    'upload_fields' => [
      '#title' => t('Audit Upload Fields'),
      '#description' => t('Audit fields that allow for upload and make sure they do not allow the following unsafe extensions: flv, swf, exe, html, htm, php, phtml, py, js, vb, vbe, vbs.'),
      'handbook_page' => [
        '#text' => t('Fields List if Field UI is enabled.'),
        '#url' => $checklist_routes['field_ui'],
      ],
    ],
    'base_url' => [
      '#title' => t('Base URL'),
      '#description' => t('Ensure that the settings.php file includes a defined base URL.'),
    ],
    'security_kit' => [
      '#title' => t('Security Kit Module'),
      '#description' => t('Install the security kit module and configure appropriately for security hardening.'),
      'handbook_page' => [
        '#text' => t('Security Kit'),
        '#url' => $checklist_routes['seckit'],
      ],
    ],
    'security_review' => [
      '#title' => t('Security Review Module'),
      '#description' => t('Evaluate the site using the security review module.'),
      'handbook_page' => [
        '#text' => t('Security Review'),
        '#url' => $checklist_routes['security_review'],
      ],
    ],
    'views_permissions' => [
      '#title' => t('Views Permissions'),
      '#description' => t('Ensure that views have defined permissions.'),
      'handbook_page' => [
        '#text' => t('Views'),
        '#url' => $checklist_routes['views_ui'],
      ],
    ],
    'linux_permissions' => [
      '#title' => t('Linux Permissions'),
      '#description' => t('Ensure that linux permissions on files and private-files directories are correct.'),
    ],
    'settings_php' => [
      '#title' => t('Permissions in settings.php'),
      '#description' => t('Ensure permissions in the settings.php files are correct.'),
    ],
    'hsts_policy' => [
      '#title' => t('HSTS/HTTPS Policy'),
      '#description' => t('Ensure the site is utilizing an HSTS policy. The site should only accept HTTPS and refuse HTTP connections.'),
      'handbook_page_1' => [
        '#text' => t('Drupal - Enabling HTTP Secure (HTTPS)'),
        '#url' => Url::fromUri('https://www.drupal.org/https-information', $attr),
      ],
      'handbook_page_2' => [
        '#text' => t('Pantheon - Enforce HTTPS + HSTS'),
        '#url' => Url::fromUri('https://pantheon.io/docs/pantheon-yml#enforce-https--hsts', $attr),
      ],
      'handbook_page_3' => [
        '#text' => t('Acquia - How to enable HSTS for your Drupal site'),
        '#url' => Url::fromUri('https://support-acquia.force.com/s/article/360004119254-How-To-enable-HSTS-for-your-Drupal-site', $attr),
      ],
    ],
  ];
}
