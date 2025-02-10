<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\refreshless_turbo\Value\RefreshlessRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Request policy to prevent serving page_cache responses for Turbo.
 *
 * This is necessary to ensure the page varies by the libraries that are
 * already loaded on a page for anonymous requests. Under normal conditions,
 * the page_cache module will cache and serve the entire response so whatever
 * state the libraries were in for the first response for a page is what all
 * subsequent responses for that page would be served, leading to an incorrect
 * combination of libraries.
 */
class AdditiveLibrariesRequestPolicy implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {

    if ((new RefreshlessRequest($request))->isTurbo()) {
      return static::DENY;
    }

  }

}
