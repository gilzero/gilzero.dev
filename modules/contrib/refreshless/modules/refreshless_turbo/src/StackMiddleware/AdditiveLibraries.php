<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\StackMiddleware;

use Drupal\refreshless_turbo\Value\RefreshlessRequest;
use function array_search;
use function array_splice;
use function is_int;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Additive libraries middleware.
 *
 * This loads the RefreshLess page state if found in the request, removes the
 * 'refreshless_turbo/refreshless' library from the already loaded list, and
 * then converts it to 'ajax_page_state' value, which it sets back on the
 * request.
 *
 * @see \Drupal\Core\StackMiddleware\AjaxPageState
 *   Must run after core's Ajax page state middleware; if we were to run before
 *   it, we would have to re-compress the libraries only for core to decompress
 *   it again which is a bit inefficient and unnecessary.
 */
class AdditiveLibraries implements HttpKernelInterface {

  /**
   * Constructor; saves dependencies.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The wrapped HTTP kernel.
   */
  public function __construct(
    protected readonly HttpKernelInterface $httpKernel,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function handle(
    Request $request, int $type = self::MAIN_REQUEST, bool $catch = true,
  ): Response {

    if ($type !== self::MAIN_REQUEST) {
      return $this->httpKernel->handle($request, $type, $catch);
    }

    $refreshlessRequest = new RefreshlessRequest($request);

    if (
      !$refreshlessRequest->isTurbo() ||
      !$refreshlessRequest->hasPageState()
    ) {
      return $this->httpKernel->handle($request, $type, $catch);
    }

    $libraries = $refreshlessRequest->getLibraries();

    /** @var int|false */
    $index = array_search('refreshless_turbo/refreshless', $libraries);

    if (is_int($index)) {

      array_splice($libraries, $index, 1);

      $refreshlessRequest->setLibraries($libraries);

    }

    if ($request->getMethod() === 'GET') {

      $request->query->set(
        'ajax_page_state', $refreshlessRequest->toAjaxPageState(),
      );

    } else {

      $request->request->set(
        'ajax_page_state', $refreshlessRequest->toAjaxPageState(),
      );

    }

    return $this->httpKernel->handle($request, $type, $catch);

  }

}
