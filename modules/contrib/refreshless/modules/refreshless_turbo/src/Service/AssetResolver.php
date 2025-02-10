<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Service;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface;
use Drupal\refreshless_turbo\Value\RefreshlessAsset;
use function array_keys;
use function array_search;
use function array_splice;
use function is_int;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

/**
 * Decorated asset resolver service.
 *
 * @see \Drupal\Core\Asset\AssetResolver
 *   Drupal core implementation.
 *
 * @see \Drupal\Core\Asset\AssetResolverInterface
 *
 * @see https://www.drupal.org/project/refreshless/issues/3397370
 *   Explains the rationale for including drupalSettings in the <body>.
 */
class AssetResolver implements AssetResolverInterface {

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\Core\Asset\AssetResolverInterface $decorated
   *   The asset resolver service that we decorate.
   *
   * @param \Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface $killSwitch
   *   The RefreshLess Turbo kill switch service.
   */
  public function __construct(
    #[AutowireDecorated]
    protected readonly AssetResolverInterface $decorated,
    protected readonly RefreshlessTurboKillSwitchInterface $killSwitch,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getCssAssets(
    AttachedAssetsInterface $assets,
    $optimize,
    LanguageInterface $language = null,
  ) {

    if ($this->killSwitch->triggered()) {
      return $this->decorated->getCssAssets($assets, $optimize, $language);
    }

    // Save already loaded libraries to restore after building CSS.
    $alreadyLoaded = $assets->getAlreadyLoadedLibraries();

    // Force building of CSS assets as if no libraries have already been marked
    // as loaded. This is necessary to work around our additive JavaScript
    // aggregation which will mark libraries as already loaded, which can then
    // result in cases where a library that contains both JavaScript and CSS
    // attaching neither; unfortunately, this means we wouldn't have access to
    // the full list of ordered CSS files to generate the weight attributes,
    // resulting in incorrect specificity issues during Turbo navigation if CSS
    // aggregation is disabled.
    //
    // @todo Can we handle this instead in a custom attachments processor to
    //   avoid doing this?
    $assets->setAlreadyLoadedLibraries([]);

    $css = $this->decorated->getCssAssets($assets, $optimize, $language);

    // Now restore the already loaded libraries to avoid unintended problems.
    $assets->setAlreadyLoadedLibraries($alreadyLoaded);

    // Output a data attribute for the front-end to sort the stylesheets into
    // the correct order as Turbo currently only appends new stylesheets
    // without preserving their source order.
    //
    // @see https://www.drupal.org/project/refreshless/issues/3399314
    foreach (array_keys($css) as $index => $key) {

      $css[$key]['attributes'][
        RefreshlessAsset::getStylesheetOrderAttributeName()
      ] = $index;

    }

    return $css;

  }

  /**
   * {@inheritdoc}
   */
  public function getJsAssets(
    AttachedAssetsInterface $assets,
    $optimize,
    LanguageInterface $language = null,
  ) {

    if ($this->killSwitch->triggered()) {
      return $this->decorated->getJsAssets($assets, $optimize, $language);
    }

    $alreadyLoaded = $assets->getAlreadyLoadedLibraries();

    /** @var int|false */
    $index = array_search('refreshless_turbo/refreshless', $alreadyLoaded);

    if (is_int($index)) {

      // Remove the 'refreshless_turbo/refreshless' library from the already
      // loaded array to ensure it remains attached.
      array_splice($alreadyLoaded, $index, 1);

      // Mark Turbo itself as already loaded. While Turbo is currently set to
      // preprocess: false and thus should not get evaluated more than once,
      // not marking it as already loaded seems to introduce some multiple
      // evaluation of our own JavaScript when aggregation is enabled in some
      // cases.
      //
      // @todo Remove when we've resolved the above issue.
      $alreadyLoaded[] = 'refreshless_turbo/turbo';

      $assets->setAlreadyLoadedLibraries($alreadyLoaded);

    }

    return $this->decorated->getJsAssets($assets, $optimize, $language);

  }

}
