<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Service;

use Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface;

/**
 * Service to disable RefreshLess and Turbo for a request.
 *
 * This functions similarly to Drupal core's page cache kill switch: it can only
 * be triggered by one or more calls and cannot be un-triggered once done so.
 *
 * This is best triggered as early as possible during a request so that all
 * RefreshLess alterations to the response are prevented. A recommended place
 * to do this is in a custom HTTP middleware based on request headers, cookies,
 * etc.
 *
 * @see \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
 */
class RefreshlessTurboKillSwitch implements RefreshlessTurboKillSwitchInterface {

  /**
   * Whether the kill switch was triggered at least once during this request.
   *
   * @var boolean
   */
  protected bool $triggered = false;

  /**
   * {@inheritdoc}
   */
  public function trigger(): void {
    $this->triggered = true;
  }

  /**
   * {@inheritdoc}
   */
  public function triggered(): bool {
    return $this->triggered;
  }

}
