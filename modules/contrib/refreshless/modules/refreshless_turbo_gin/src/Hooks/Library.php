<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo_gin\Hooks;

use Drupal\hux\Attribute\Alter;

/**
 * Library hook implementations.
 */
class Library {

  #[Alter('library_info')]
  /**
   * Alter library definitions to add our Gin integration to that theme.
   *
   * @see \hook_library_info_alter()
   */
  public function alter(array &$libraries, string $extension): void {

    if ($extension !== 'gin') {
      return;
    }

    $libraries['gin_accent'][
      'dependencies'
    ][] = 'refreshless_turbo_gin/gin_accent';

    $libraries['navigation'][
      'dependencies'
    ][] = 'refreshless_turbo_gin/gin_navigation';
    $libraries['core_navigation'][
      'dependencies'
    ][] = 'refreshless_turbo_gin/gin_navigation';

  }

}
