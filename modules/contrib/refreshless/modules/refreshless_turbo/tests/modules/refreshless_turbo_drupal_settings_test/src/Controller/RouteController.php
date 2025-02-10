<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo_drupal_settings_test\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;

/**
 * Route controller for RefreshLess Turbo drupalSettings tests.
 */
class RouteController {

  /**
   * Route callback.
   *
   * @param string $linkToRoute
   *   Optional route name to render a link to.
   *
   * @param bool $xssAttempt
   *   If true, will output a drupalSettings <script> element in the content
   *   to simulate compromised content.
   *
   * @return array
   *   A render array.
   */
  public function route(string $linkToRoute, bool $xssAttempt): array {

    $renderArray = [

      'content' => [
        '#type'   => 'html_tag',
        '#tag'    => 'p',
        '#value'  => 'Hello there.',
      ],

      'link' => [
        '#type'   => 'link',
        '#title'  => 'Link',
        '#url'    => Url::fromRoute($linkToRoute),
      ],

      // We don't want this route to be cached. This shouldn't needed during
      // tests, but useful if installing this module in a dev environment where
      // caching is enabled to replicate production as much as possible.
      '#cache' => ['max-age' => 0],

    ];

    if ($xssAttempt === true) {

      // Note that this itself would not be a cross-site scripting attempt
      // because setting the type to 'application/json' makes this
      // non-executable, but could be used as part of such an attack to weaken
      // values in drupalSettings to enable such an attack.
      $renderArray['xss'] = [
        '#type'       => 'html_tag',
        '#tag'        => 'script',
        '#attributes' => [
          'type'                  => 'application/json',
          'data-drupal-selector'  => 'drupal-settings-json',
        ],
        '#value' => Json::encode([
          'path'          => ['baseUrl' => '/some/sneaky/value'],
          'verySusValue'  => true,
        ]),
      ];

    }

    return $renderArray;

  }

}
