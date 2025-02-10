<?php

/**
 * @file
 * Uses best practices to check for pre-launch best practices.
 */

/**
 * Generates and returns section 14 for launch_checklist.
 */
function section_14_printability($checklist_routes) {

  return [
    // Section 14 Printability.
    '#title' => t('Printability'),
    '#description' => t('<p>Printability settings for the Drupal site.</p>'),
    'hide_clutter' => [
      '#title' => t('Hide clutter'),
      '#description' => t('We probably don’t want to show the main menu, search box, footer, etc. You can simply choose to hide it.'),
    ],
    'hide_restrict_images' => [
      '#title' => t('Hide or restrict images'),
      '#description' => t('You can hide all images, set a max-width with a dimension, or 100% which will stretch to the width of the page.'),
    ],
    'hide_irrelevant' => [
      '#title' => t('Hide irrelevant objects'),
      '#description' => t('Hide things that make no sense on paper. E.g.: @video, @audio, @object and @embed.',
        [
          '@video' => '<video>',
          '@audio' => '<audio>',
          '@object' => '<object>',
          '@embed' => '<embed>',
        ]),
    ],
    'adjust_page_size' => [
      '#title' => t('Adjusting to the right size'),
      '#description' => t('To define page margins, you can use @page rule to simply apply a margin all the way around the page.'),
    ],
    'typography' => [
      '#title' => t('Verify Typography (fonts, headings lists, etc) look reasonable'),
      '#description' => t('Use common sense defaults for fonts. Make sure that there is not an excess of white space/line-height between paragraph items.'),
    ],
    'header_brand' => [
      '#title' => t('Verify Header brand is visually pleasing'),
      '#description' => t('Make sure that the logo takes up an appropriate amount of space on the page and does not overwhelm the print content. If possible a black and white logo should display to prevent ink wastage.'),
    ],
    'link_color' => [
      '#title' => t('Set link colors to default text color'),
      '#description' => t('Links should be set to the default text color or black.'),
    ],
    'link_underline' => [
      '#title' => t('Set links to display underlines'),
      '#description' => t('To provide visible contrast between non-linked content and linked content a url should be underlined.'),
    ],
    'link_hide_unnecessary' => [
      '#title' => t('Hide unnecessary links'),
      '#description' => t('Where we have links that do not provide context in print (I.e. Anchor links) the link should be hidden.'),
    ],
    'print_url' => [
      '#title' => t('Print the URL'),
      '#description' => t('The URL for a link needs to be visible on the printed page.'),
    ],
  ];
}
