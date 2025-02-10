<?php

declare(strict_types=1);

namespace Drupal\refreshless_ajax\EventSubscriber;

use Drupal\Core\Render\PageDisplayVariantSelectionEvent;
use Drupal\Core\Render\RenderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Helper function for block display subscriber.
 *
 * @see \Drupal\refreshless_ajax\Plugin\DisplayVariant\RefreshlessBlockPageVariant
 */
class BlockPageDisplayVariantSubscriber implements EventSubscriberInterface {

  /**
   * Selects the RefreshLess override of the block page display variant.
   *
   * @param \Drupal\Core\Render\PageDisplayVariantSelectionEvent $event
   *   The event to process.
   */
  public function onBlockPageDisplayVariantSelected(
    PageDisplayVariantSelectionEvent $event,
  ): void {
    if ($event->getPluginId() === 'block_page') {
      $event->setPluginId('refreshless_block_page');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Set a very low priority, so that it runs last.
    $events[RenderEvents::SELECT_PAGE_DISPLAY_VARIANT][] = [
      'onBlockPageDisplayVariantSelected',
      -1000,
    ];
    return $events;
  }

}
