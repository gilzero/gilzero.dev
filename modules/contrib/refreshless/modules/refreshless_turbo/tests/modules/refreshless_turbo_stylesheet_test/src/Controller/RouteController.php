<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo_stylesheet_test\Controller;

use Drupal\Core\Url;

/**
 * Route controller for RefreshLess Turbo stylesheet tests.
 */
class RouteController {

  /**
   * Route callback.
   *
   * @param string $library
   *   The name of the library to attach.
   *
   * @param string $linkToRoute
   *   Optional route name to render a link to.
   *
   * @return array
   *   A render array.
   */
  public function route(string $library, string $linkToRoute = ''): array {

    $renderArray = [

      'content' => [
        '#type'   => 'html_tag',
        '#tag'    => 'p',
        '#value'  => 'Hello there.',
      ],

      '#attached' => [
        'library' => ["refreshless_turbo_stylesheet_test/$library"],
      ],

      // We don't want this route to be cached. This shouldn't needed during
      // tests, but useful if installing this module in a dev environment where
      // caching is enabled to replicate production as much as possible.
      '#cache' => ['max-age' => 0],

    ];

    if (!empty($linkToRoute)) {

      $renderArray['link'] = [
        '#type'   => 'link',
        '#title'  => 'Link',
        '#url'    => Url::fromRoute($linkToRoute),
      ];

    }

    return $renderArray;

  }

}
