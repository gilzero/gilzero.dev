<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Service;

/**
 * Interface for RefreshLess Turbo kill switch service.
 */
interface RefreshlessTurboKillSwitchInterface {

  /**
   * Trigger the kill switch for this request.
   */
  public function trigger(): void;

  /**
   * Whether the kill switch was triggered at least once during this request.
   *
   * @return bool
   */
  public function triggered(): bool;

}
