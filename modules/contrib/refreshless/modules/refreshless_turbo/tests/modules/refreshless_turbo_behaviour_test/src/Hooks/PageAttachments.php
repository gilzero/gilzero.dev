<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo_behaviour_test\Hooks;

use Drupal\hux\Attribute\Hook;

/**
 * Page attachment hook implementations.
 */
class PageAttachments {

  #[Hook('page_attachments')]
  /**
   * Attaches our test libraries.
   *
   * @see hook_page_attachments
   */
  public function attachMetaElement(array &$attachments): void {

    $attachments['#attached']['library'][] =
      'refreshless_turbo_behaviour_test/behaviours';

  }

}
