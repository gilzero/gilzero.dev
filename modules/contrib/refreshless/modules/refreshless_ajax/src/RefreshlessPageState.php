<?php

declare(strict_types=1);

namespace Drupal\refreshless_ajax;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\refreshless_ajax\RefreshlessPageStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The RefreshLess page state service.
 */
class RefreshlessPageState implements RefreshlessPageStateInterface {

  /**
   * Constructs a new RefreshlessPageState instance.
   *
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $cacheContextsManager
   *   The cache contexts manager.
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrfTokenGenerator
   *   The CSRF token generator.
   */
  public function __construct(
    protected readonly CacheContextsManager $cacheContextsManager,
    protected readonly CsrfTokenGenerator   $csrfTokenGenerator,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function build(CacheableMetadata $cacheability): array {
    // For now, the RefreshLess page state only consists of the context hashes.
    return $this->getSensitiveContextHashes($cacheability->getCacheContexts());
  }

  /**
   * Reads the RefreshLess page state from the current request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   The RefreshLess page state.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   When the RefreshLess page state is missing.
   */
  protected function read(Request $request): array {
    if (!$request->query->has('refreshless_page_state')) {
      throw new HttpException(500, 'RefreshLess page state is missing.');
    }

    // \Symfony\Component\HttpFoundation\InputBag::get() does not support
    // fetching an array as of Symfony 6.0 and will throw an error stating
    // that 'refreshless_page_state' is not a scalar value.
    //
    // @see https://github.com/symfony/symfony/issues/44432#issuecomment-1033609421
    $refreshless_page_state = $request->query->all()[
      'refreshless_page_state'
    ];
    return [
      // @see build()
      'cache_contexts' => $refreshless_page_state,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function hasChanged(
    CacheableMetadata $cacheability, Request $request,
  ): bool {
    $current_context_hashes = $this->getSensitiveContextHashes($cacheability->getCacheContexts());
    $previous_context_hashes = $this->read($request)['cache_contexts'];

    return $current_context_hashes != array_intersect_key($previous_context_hashes, $current_context_hashes);
  }

  /**
   * Indicates whether RefreshLess is sensitive to changes in cache context.
   *
   * RefreshLess is sensitive to only the 'url' (and 'url.*') cache context and
   * the 'route' (and 'route.*') cache context.
   *
   * @todo can be simplified to just 'url' (and 'url.*') once https://www.drupal.org/node/2453835 lands.
   *
   * @param string $context_token
   *   The cache context token to check.
   *
   * @return bool
   *   Whether the given cache context token is
   */
  protected function isSensitiveContext(string $context_token): bool {
    return strpos($context_token, 'url') === 0 || strpos($context_token, 'route') === 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getSensitiveContextHashes(array $context_tokens): array {
    $context_hashes = [];
    foreach ($context_tokens as $context_token) {
      if ($this->isSensitiveContext($context_token)) {
        $context_value = $this->cacheContextsManager->convertTokensToKeys([$context_token])->getKeys()[0];
        $context_hashes[$context_token] = $this->csrfTokenGenerator->get($context_value);
      }
    }
    return $context_hashes;
  }

}
