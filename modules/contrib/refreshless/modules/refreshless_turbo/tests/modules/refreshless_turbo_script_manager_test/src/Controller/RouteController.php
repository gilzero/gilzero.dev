<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo_script_manager_test\Controller;

use Drupal\Core\Url;

/**
 * Route controller for RefreshLess Turbo script manager tests.
 */
class RouteController {

  /**
   * Route callback.
   *
   * @param string[] $libraries
   *   One or more libraries to attach.
   *
   * @param string $linkToRoute
   *   Optional route name to render a link to.
   *
   * @return array
   *   A render array.
   */
  public function route(array $libraries, string $linkToRoute = ''): array {

    $renderArray = [

      'content' => [
        '#type'   => 'html_tag',
        '#tag'    => 'p',
        '#value'  => 'Hello there.',
      ],

      // We don't want this route to be cached. This shouldn't needed during
      // tests, but useful if installing this module in a dev environment where
      // caching is enabled to replicate production as much as possible.
      '#cache' => ['max-age' => 0],

    ];

    foreach ($libraries as $library) {

      $renderArray['#attached'][
        'library'
      ][] = "refreshless_turbo_script_manager_test/$library";

    }

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
