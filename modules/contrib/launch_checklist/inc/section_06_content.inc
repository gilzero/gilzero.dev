<?php

/**
 * @file
 * Uses best practices to check for pre-launch best practices.
 */

use Drupal\Core\Url;

/**
 * Generates and returns section 6 for launch_checklist.
 */
function section_06_content($checklist_routes) {

  return [
    // Section 6 Content.
    '#title' => t('Content'),
    '#description' => t('<p>Content settings for the Drupal site.</p>'),
    'content_redirects' => [
      '#title' => t('Content Redirects'),
      '#description' => t('Ensure that content redirects are in place.'),
      'handbook_page' => [
        '#text' => t('Redirects.'),
        '#url' => $checklist_routes["redirect"],
      ],
    ],
    'manage_content' => [
      '#title' => t('Manager Role and Content'),
      '#description' => t('Ensure managers have the ability to manage content.'),
      'handbook_page' => [
        '#text' => t('Site User Roles'),
        '#url' => Url::fromRoute('user.admin_permissions'),
      ],
    ],
    'user_roles_content' => [
      '#title' => t('User Roles and Content Creation'),
      '#description' => t('Sign in as each user role and check that they are able to create the content that they need to.'),
      'handbook_page' => [
        '#text' => t('Site User Roles'),
        '#url' => Url::fromRoute('user.logout'),
      ],
    ],
    'user_content_entry' => [
      '#title' => t('User Roles and Content Types'),
      '#description' => t('Test creating each content type as each user role and ensure that all available buttons on the WYSIWYG editor work (eg. make sure inserted images show or that when using the quote button the correct result is seen when saved).'),
      'handbook_page' => [
        '#text' => t('All Content'),
        '#url' => Url::fromRoute('view.content.page_1'),
      ],
    ],
    'input_formats' => [
      '#title' => t('Input Formats'),
      '#description' => t('Check the input formats and ensure all user roles have the relevant format.'),
      'handbook_page' => [
        '#text' => t('Text Filters'),
        '#url' => Url::fromRoute('filter.admin_overview'),
      ],
    ],
    'anonymous_users' => [
      '#title' => t('Anonymous User Content'),
      '#description' => t('Ensure that anonymous users are only allowed to use the plain-text input formats (if relevant to this site).'),
      'handbook_page' => [
        '#text' => t('Site User Roles'),
        '#url' => Url::fromRoute('filter.admin_overview'),
      ],
    ],
    'wysiwyg_profiles' => [
      '#title' => t('WYSIWYG Profiles'),
      '#description' => t('Check all of the WYSIWYG profiles.'),
      'handbook_page' => [
        '#text' => t('Text Filters'),
        '#url' => Url::fromRoute('filter.admin_overview'),
      ],
    ],
    'wysiwyg_security' => [
      '#title' => t('WYSIWYG Security Settings'),
      '#description' => t('Ensure that the security settings are appropriate and coincide with the settings for the selected input format(s).'),
      'handbook_page' => [
        '#text' => t('Text Filters'),
        '#url' => Url::fromRoute('filter.admin_overview'),
      ],
    ],
    'wysiwyg_toolbar' => [
      '#title' => t('WYSIWYG Toolbar'),
      '#description' => t('Ensure that enabled toolbar buttons coincide with the list of allowed HTML tags in the selected input format(s).'),
      'handbook_page' => [
        '#text' => t('Text Filters'),
        '#url' => Url::fromRoute('filter.admin_overview'),
      ],
    ],
    'wysiwyg_plain_text' => [
      '#title' => t('WYSIWYG Plain Text'),
      '#description' => t('Ensure that forced pasting as plain text is enabled.'),
      'handbook_page' => [
        '#text' => t('Text Filters'),
        '#url' => Url::fromRoute('filter.admin_overview'),
      ],
    ],
    'search_replace' => [
      '#title' => t('Search and Replace Testbed URLs'),
      '#description' => t('Do a global search of the database for any testbed URLs in the content (eg. dev.<domain-name>.com) and replace with the real domain name.'),
    ],
  ];
}
