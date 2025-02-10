<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\refreshless_turbo\Service\RefreshlessTurboContextInterface;
use function t;

/**
 * Defines the RefreshLess Turbo request cache context service.
 *
 * Cache context ID: 'refreshless_turbo_request'.
 *
 * This varies based on whether the current request was performed through Turbo
 * or via non-Turbo means.
 */
class RefreshlessTurboRequestCacheContext implements CalculatedCacheContextInterface {

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\refreshless_turbo\Service\RefreshlessTurboContextInterface $refreshlessContext
   *   The RefreshLess Turbo context service.
   */
  public function __construct(
    protected readonly RefreshlessTurboContextInterface $refreshlessContext,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('RefreshLess Turbo request');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($parameter = null) {

    if ($this->refreshlessContext->isRefreshlessRequest()) {
      return 'true';
    }

    return 'false';

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($parameter = null) {
    return new CacheableMetadata();
  }

}
