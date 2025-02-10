<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo_messenger_test\Hooks;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\hux\Attribute\Hook;

/**
 * Page attachment hook implementations.
 */
class PageAttachments {

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $currentRouteMatch
   *   The current route match service.
   */
  public function __construct(
    protected readonly RouteMatchInterface $currentRouteMatch,
  ) {}

  #[Hook('page_attachments')]
  /**
   * Attaches the <meta> element for Turbo.
   *
   * @see \hook_page_attachments()
   */
  public function attachMetaElement(array &$attachments): void {

    // In anything else, this would ideally be an enum, but it's not worth the
    // effort here unless this gets repurposed to a more reusable/dynamic set
    // of routes for testing other things.
    $value = match ($this->currentRouteMatch->getRouteName()) {
      'refreshless_turbo_messenger_test.one' => 'one',
      'refreshless_turbo_messenger_test.two' => 'two',
      default => 'nope',
    };

    // We need to tell Turbo to do a reload (full page load) to test that our
    // decorated messenger service re-emits messages that would otherwise be
    // lost and not seen by the user.
    $attachments['#attached']['html_head'][] = [[
      '#type'       => 'html_tag',
      '#tag'        => 'meta',
      '#attributes' => [
        'name'              => 'refreshless-turbo-messenger-test',
        'content'           => $value,
        'data-turbo-track'  => 'reload',
      ],
    ], 'refreshless_turbo_messenger_test'];

    (CacheableMetadata::createFromRenderArray($attachments))->addCacheContexts([
      'url.path',
    ])->applyTo($attachments);

  }

}
