<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\StackMiddleware;

use Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * RefreshLess kill switch cookie middleware.
 */
class RefreshlessKillSwitch implements HttpKernelInterface {

  /**
   * Name of the cookie that triggers the RefreshLess kill switch if present.
   *
   * Note that the value doesn't matter, just the presence of the cookie.
   */
  protected const COOKIE_NAME = 'refreshless-disable';

  /**
   * Constructor; saves dependencies.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The wrapped HTTP kernel.
   *
   * @param \Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface $killSwitch
   *   The RefreshLess Turbo kill switch service.
   */
  public function __construct(
    protected readonly HttpKernelInterface $httpKernel,
    protected readonly RefreshlessTurboKillSwitchInterface $killSwitch,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function handle(
    Request $request, int $type = self::MAIN_REQUEST, bool $catch = true,
  ): Response {

    if (
      $type === self::MAIN_REQUEST &&
      $request->cookies->has(self::COOKIE_NAME)
    ) {

      $this->killSwitch->trigger();

    }

    return $this->httpKernel->handle($request, $type, $catch);

  }

}
