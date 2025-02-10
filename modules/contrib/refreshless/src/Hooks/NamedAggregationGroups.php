<?php

declare(strict_types=1);

namespace Drupal\refreshless\Hooks;

use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\hux\Attribute\Alter;

/**
 * Named aggregation group hook implementations.
 */
class NamedAggregationGroups {

  #[Alter('js')]
  /**
   * Enable aggregation for our JavaScript if core supports naming groups.
   *
   * If core does not support named aggregation groups, the 'group' value will
   * never be our string but a numeric value that core sets; if it's the numeric
   * value, we don't do anything and allow the preprocess: false in our library
   * definition to be used as a fallback.
   *
   * Note that \hook_library_info_alter() seems to be too early to detect this
   * as it'll always have our string group; core seems to override the group
   * value sometime between invoking that hook and \hook_js_alter().
   *
   * Also note that this should always be done regardless of whether or not this
   * is a RefreshLess request, nor if the kill switch has been triggered.
   *
   * @see \hook_js_alter()
   */
  public function refreshlessGroupPreprocess(
    array &$javascript,
    AttachedAssetsInterface $assets, LanguageInterface $language,
  ): void {

    foreach ($javascript as $path => &$values) {

      if (
        !\is_string($values['group']) ||
        !\str_starts_with($values['group'], 'refreshless')
      ) {
        continue;
      }

      // Named aggregation groups are supported, so enable aggregation for this
      // group.
      $values['preprocess'] = true;

    }

  }

}
