<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo_debug\Hooks;

use Drupal\hux\Attribute\Alter;

/**
 * Library hook implementations.
 */
class Library {

  #[Alter('library_info')]
  /**
   * Add the debug library as a dependency of 'refreshless_turbo/refreshless'.
   *
   * @see hook_library_info_alter
   */
  public function addRefreshlessDebug(
    array &$libraries, string $extension,
  ): void {

    if ($extension !== 'refreshless_turbo') {
      return;
    }

    $libraries['refreshless'][
      'dependencies'
    ][] = 'refreshless_turbo_debug/debug';

  }

}
