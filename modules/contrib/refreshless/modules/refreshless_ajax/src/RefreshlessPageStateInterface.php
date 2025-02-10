<?php

declare(strict_types=1);

namespace Drupal\refreshless_ajax;

use Drupal\Core\Cache\CacheableMetadata;
use Symfony\Component\HttpFoundation\Request;

/**
 * The RefreshLess page state service interface.
 */
interface RefreshlessPageStateInterface {

  /**
   * Builds the RefreshLess page state for the given response cacheability.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheability
   *   Response cacheability metadata (or at least the cacheability metadata of
   *   all rendered parts of the page that are eligible for RefreshLess-based
   *   updating).
   *
   * @return array
   *   The RefreshLess page state to be used in #attached[drupalSettings].
   */
  public function build(CacheableMetadata $cacheability): array;

  /**
   * Check whether a region (or block) has changed from the previous page.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheability
   *   The cacheability of a region (or block), to check whether it would
   *   have changed compared to the previous page.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request, to check whether.
   *
   * @return bool
   *   Whether this region (or block) has changed.
   */
  public function hasChanged(
    CacheableMetadata $cacheability, Request $request,
  ): bool;

  /**
   * Gets the context hashes that RefreshLess is sensitive to.
   *
   * @param string[] $context_tokens
   *   A set of cache context tokens.
   *
   * @return string[]
   *   The context hashes of sensitive context tokens, keyed by the context
   *   tokens.
   */
  public function getSensitiveContextHashes(array $context_tokens): array;

}
