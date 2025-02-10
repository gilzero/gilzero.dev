<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Hooks;

use Drupal\Core\Asset\AssetQueryStringInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\PermissionsHashGeneratorInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\hux\Attribute\Hook;
use Drupal\refreshless_turbo\Service\RefreshlessTurboContextInterface;
use Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface;

/**
 * Page attachments hook implementations.
 */
class PageAttachments {

  /**
   * Hook constructor; saves dependencies.
   *
   * @param \Drupal\Core\Asset\AssetQueryStringInterface $assetQueryString
   *   The Drupal asset query string service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration object factory service.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user account proxy service.
   *
   * @param \Drupal\refreshless_turbo\Service\RefreshlessTurboContextInterface $refreshlessContext
   *   The RefreshLess Turbo context service service.
   *
   * @param \Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface $killSwitch
   *   The RefreshLess Turbo kill switch service.
   *
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The Drupal theme manager.
   *
   * @param \Drupal\Core\Session\PermissionsHashGeneratorInterface $permissionsHashGenerator
   *   The permissions hash generator service.
   */
  public function __construct(
    protected readonly AssetQueryStringInterface  $assetQueryString,
    protected readonly ConfigFactoryInterface     $configFactory,
    protected readonly AccountProxyInterface      $currentUser,
    protected readonly RefreshlessTurboContextInterface   $refreshlessContext,
    protected readonly RefreshlessTurboKillSwitchInterface $killSwitch,
    protected readonly ThemeManagerInterface              $themeManager,
    protected readonly PermissionsHashGeneratorInterface  $permissionsHashGenerator,
  ) {}

  #[Hook('page_attachments')]
  /**
   * Attaches RefreshLess to the page.
   *
   * @see \hook_page_attachments()
   */
  public function attachLibraries(array &$attachments): void {

    // Vary based on whether Turbo is enabled for a request. Note that this must
    // be applied regardless of whether or not Turbo is enabled.
    (CacheableMetadata::createFromRenderArray($attachments))->addCacheContexts([
      'refreshless_turbo_enabled',
    ])->applyTo($attachments);

    // Only attach if the kill switch was not triggered.
    if ($this->killSwitch->triggered()) {
      return;
    }

    $attachments['#attached']['library'][] = 'refreshless_turbo/refreshless';

  }

  #[Hook('page_attachments')]
  /**
   * Attaches <meta> elements for Turbo.
   *
   * @see \hook_page_attachments()
   *
   * @todo Split this into several methods, grouped by purpose:
   *   1. Theme <meta> element.
   *   2. Asset <meta> elements.
   *   3. turbo-cache-control <meta> element.
   */
  public function attachMetaElements(array &$attachments): void {

    // Vary based on whether Turbo is enabled for a request. Note that this must
    // be applied regardless of whether or not Turbo is enabled.
    (CacheableMetadata::createFromRenderArray($attachments))->addCacheContexts([
      'refreshless_turbo_enabled',
    ])->applyTo($attachments);

    // Don't attach any of our <meta> elements if the kill switch was triggered.
    if ($this->killSwitch->triggered()) {
      return;
    }

    /** @var \Drupal\Core\Config\ImmutableConfig */
    $performanceConfig = $this->configFactory->get('system.performance');

    /** @var \Drupal\Core\Config\ImmutableConfig */
    $themeConfig = $this->configFactory->get('system.theme');

    // Important note: don't add cache metadata to the items in
    // $attachments['#attached']['html_head'] because Drupal doesn't seem to
    // bubble that up to the page and thus the response, meaning that none of
    // the cache metadata will take effect.
    /** @var \Drupal\Core\Cache\RefinableCacheableDependencyInterface Cache metadata object containing existing metadata found in $attachments, if any. */
    $cacheMetadata = CacheableMetadata::createFromRenderArray($attachments);

    // This outputs the machine name of the current theme along with the
    // data-turbo-track="reload" attribute which causes Turbo to automagically
    // do a full page load rather than a fetch when this element changes on
    // navigating to a new page.
    //
    // @see https://turbo.hotwired.dev/reference/attributes
    $attachments['#attached']['html_head'][] = [[
      '#type'       => 'html_tag',
      '#tag'        => 'meta',
      '#attributes' => [
        'name'              => 'refreshless-turbo-theme',
        'content'           => $this->themeManager->getActiveTheme()->getName(),
        'data-turbo-track'  => 'reload',
      ],
    ], 'refreshless_turbo_theme'];

    // Add the cache metadata for the system theme configuration.
    $cacheMetadata->addCacheableDependency($themeConfig);

    foreach (['css', 'js'] as $type) {

      $attachments['#attached']['html_head'][] = [[
        '#type'       => 'html_tag',
        '#tag'        => 'meta',
        '#attributes' => [
          'name'    => 'refreshless-turbo-' . $type . '-aggregation',
          'content' => $performanceConfig->get($type . '.preprocess') ?
            'enabled' : 'disabled',
          'data-turbo-track'  => 'reload',
        ],
      ], 'refreshless_turbo_' . $type . '_aggregation'];

    }

    // Add the cache metadata for the performance configuration.
    $cacheMetadata->addCacheableDependency($performanceConfig);

    $attachments['#attached']['html_head'][] = [[
      '#type'       => 'html_tag',
      '#tag'        => 'meta',
      '#attributes' => [
        'name'              => 'refreshless-turbo-asset-query-string',
        'content'           => $this->assetQueryString->get(),
        'data-turbo-track'  => 'reload',
      ],
    ], 'refreshless_turbo_asset_query_string'];

    // This gets invalidated and rebuilt when the 'library_info' cache tag is
    // invalidated. Note that there doesn't seem to be a more specific cache
    // tag at the time of writing for the asset query string.
    //
    // @see \Drupal\Core\Asset\AssetResolver::getCssAssets()
    //   Caches with the 'library_info' tag.
    //
    // @see \Drupal\Core\Asset\AssetResolver::getJsAssets()
    //   Caches with the 'library_info' tag.
    //
    // @see \Drupal\Core\Asset\AssetQueryString::reset()
    //   Gets reset here but does not invalidate a cache tag or other
    //   mechanism.
    //
    // @see https://www.drupal.org/project/drupal/issues/3258064
    //   Core issue to add cache tags for state values.
    $cacheMetadata->addCacheTags(['library_info']);

    // This currently opts out of caching until we can reliably detach
    // behaviours before the <body> is cached by Turbo.
    //
    // @see https://turbo.hotwired.dev/handbook/building#opting-out-of-caching
    //
    // @see https://www.drupal.org/project/refreshless/issues/3411449
    $attachments['#attached']['html_head'][] = [[
      '#type'       => 'html_tag',
      '#tag'        => 'meta',
      '#attributes' => [
        'name'        => 'turbo-cache-control',
        'content'     => 'no-cache',
      ],
    ], 'refreshless_turbo_cache'];

    // Varies based on whether this is a Turbo request or not.
    $cacheMetadata->addCacheContexts(['refreshless_turbo_request']);

    // Indicates that this response was the result of a Turbo request.
    $attachments['#attached']['html_head'][] = [[
      '#type'       => 'html_tag',
      '#tag'        => 'meta',
      '#attributes' => [
        'name'        => 'refreshless-turbo-response',
        'content'     => $this->refreshlessContext->isRefreshlessRequest() ?
          'true' : 'false',
        // Instruct Turbo to remove this if a response does not contain it. This
        // prevents situations where two instances of this element can be
        // present, one with content="true" and one with content="false",
        // resulting in false positives.
        'data-turbo-track' => 'dynamic',
      ],
    ], 'refreshless_turbo_response'];

    $attachments['#attached']['html_head'][] = [[
      '#type'       => 'html_tag',
      '#tag'        => 'meta',
      '#attributes' => [
        'name'              => 'refreshless-turbo-permissions-hash',
        'content'           => $this->permissionsHashGenerator->generate(
          $this->currentUser->getAccount(),
        ),
        'data-turbo-track'  => 'reload',
      ],
    ], 'refreshless_turbo_permissions_hash'];

    // Merge in the permissions hash cache metadata.
    $cacheMetadata = $cacheMetadata->merge(
      $this->permissionsHashGenerator->getCacheableMetadata(
        $this->currentUser->getAccount(),
      ),
    );

    // Apply all the accumulated cache metadata back to $attachments right at
    // the end.
    $cacheMetadata->applyTo($attachments);

  }

}
