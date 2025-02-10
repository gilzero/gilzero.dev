<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Service;

use Symfony\Component\HttpFoundation\Request;

/**
 * The RefreshLess Turbo context service interface.
 */
interface RefreshlessTurboContextInterface {

  /**
   * Determine if the request is a RefreshLess request.
   *
   * @param \Symfony\Component\HttpFoundation\Request|null $request
   *   A request object to check; if not provided, will use the current request.
   *
   * @return boolean
   *   True if this is a RefreshLess request; false otherwise.
   */
  public function isRefreshlessRequest(?Request $request = null): bool;

  /**
   * Determine if the request is a RefreshLess Turbo reload request.
   *
   * @param Request|null $request
   *   A request object to check; if not provided, will use the current request.
   *
   * @return boolean
   *   True if this is a RefreshLess Turbo reload request; false otherwise.
   */
  public function isReloadRequest(?Request $request = null): bool;

}
