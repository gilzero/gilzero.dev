<?php

declare(strict_types=1);

namespace Drupal\refreshless_ajax\Theme;

use Drupal\Core\Render\Element;

/**
 * Preprocess region class.
 */
class PreprocessRegion {

  /**
   * Implements hook_preprocess_HOOK() for region templates.
   *
   * This adds a 'data-refreshless-region' attribute to each region with the
   * name of the region.
   *
   * Additionally, this adds the 'hidden' attribute to regions that contain
   * only the RefreshLess placeholder element to avoid layout issues resulting
   * from this module forcing all regions to render.
   */
  public function preprocess(array &$variables): void {

    $variables['attributes']['data-refreshless-region'] = $variables['region'];

    /** @var string[] All child element keys minus the RefreshLess placeholder. */
    $childKeys = \array_diff(
      Element::children($variables['elements']),
      ['refreshless_trigger_region_wrapper_markup']
    );

    /** @var string[] All child element keys that have rendered markup. */
    $childKeysWithMarkup = \array_filter(
      $childKeys, function($name) use ($variables) {
        // Only include elements that actually contain markup. Blocks that
        // implement lazy builder callbacks - such as the help block - will
        // still result in an entry here but will have a '#markup' containing
        // an empty string if the lazy builder did not output anything for this
        // invocation.
        return !empty($variables['elements'][$name]['#markup']);
      }
    );

    // Add the 'hidden' attribute to this region if the only rendered element is
    // the RefreshLess placeholder.
    if (count($childKeysWithMarkup) === 0) {
      $variables['attributes']['hidden'] = true;
    }

  }

}
