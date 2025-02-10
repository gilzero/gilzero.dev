<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo_messenger_test\Hooks;

use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\hux\Attribute\Alter;

/**
 * JavaScript hook implementations.
 */
class Javascript {

  #[Alter('js_settings')]
  /**
   * Alter reload reason cookie settings.
   *
   * We'll fail to detect the reload using the default cookie attribute of
   * secure: true because tests are usually run using HTTP and not HTTPS,
   * which means the cookie would not be set at all since it requires a secure
   * connection. Unsetting that here in a test module means we can leave it as
   * secure: true in non-test environments.
   *
   * @see https://github.com/ddev/ddev-selenium-standalone-chrome/pull/34
   *   Abandoned attempt (at time of writing) to set HTTPS by default.
   */
  public function alterReloadReasonCookieSettings(
    array &$settings, AttachedAssetsInterface $assets,
  ): void {

    if (!isset($settings['refreshless']['reloadReasonCookie']['attributes'])) {
      return;
    }

    unset($settings['refreshless']['reloadReasonCookie']['attributes'][
      'secure'
    ]);

  }

}
