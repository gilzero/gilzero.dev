<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Service;

use Drupal\refreshless_turbo\Service\RefreshlessTurboContextInterface;
use Drupal\refreshless_turbo\Value\RefreshlessRequest;
use Drupal\refreshless_turbo\Value\ReloadRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The RefreshLess Turbo context service.
 */
class RefreshlessTurboContext implements RefreshlessTurboContextInterface {

  /**
   * Constructor; saves dependencies.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(
    protected readonly RequestStack $requestStack,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function isRefreshlessRequest(?Request $request = null): bool {

    if ($request === null) {
      $request = $this->requestStack->getCurrentRequest();
    }

    return (new RefreshlessRequest($request))->isTurbo();

  }

  /**
   * {@inheritdoc}
   */
  public function isReloadRequest(?Request $request = null): bool {

    if ($request === null) {
      $request = $this->requestStack->getCurrentRequest();
    }

    return (new ReloadRequest($request))->isReload();

  }

}
