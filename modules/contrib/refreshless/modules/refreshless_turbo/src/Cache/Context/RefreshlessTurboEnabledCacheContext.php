<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CalculatedCacheContextInterface;
use Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface;
use function t;

/**
 * Defines the RefreshLess Turbo enabled cache context service.
 *
 * Cache context ID: 'refreshless_turbo_enabled'.
 *
 * This varies based on whether the current request has Turbo enabled.
 */
class RefreshlessTurboEnabledCacheContext implements CalculatedCacheContextInterface {

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface $killSwitch
   *   The RefreshLess Turbo kill switch service.
   */
  public function __construct(
    protected readonly RefreshlessTurboKillSwitchInterface $killSwitch,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('RefreshLess Turbo enabled');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($parameter = null) {

    if ($this->killSwitch->triggered()) {
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
