<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Hooks;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\hux\Attribute\Alter;
use Drupal\hux\Attribute\Hook;
use Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface;
use Drupal\refreshless_turbo\Value\RefreshlessAsset;
use Drupal\refreshless_turbo\Value\RefreshlessRequest;
use Drupal\refreshless_turbo\Value\ReloadRequest;
use Drupal\refreshless_turbo\Value\RequestWithPageState;
use function in_array;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * JavaScript hook implementations.
 */
class Javascript {

  /**
   * Hook constructor; saves dependencies.
   *
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrfTokenGenerator
   *   The CSRF token generator.
   *
   * @param \Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface $killSwitch
   *   The RefreshLess Turbo kill switch service.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   *   The theme handler.
   *
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager.
   */
  public function __construct(
    protected readonly CsrfTokenGenerator     $csrfTokenGenerator,
    protected readonly RefreshlessTurboKillSwitchInterface $killSwitch,
    protected readonly RequestStack           $requestStack,
    protected readonly ThemeHandlerInterface  $themeHandler,
    protected readonly ThemeManagerInterface  $themeManager,
  ) {}

  #[Alter('js')]
  /**
   * Move all JavaScript to the <head> as recommended for Hotwire Turbo.
   *
   * JavaScript found in the <body> will be executed each time that page is
   * loaded whereas JavaScript linked in the <head> will only be executed on a
   * full page load. Since all Drupal JavaScript should be using behaviours,
   * they should all correctly re-attach on every Turbo visit.
   *
   * @see \hook_js_alter()
   */
  public function alter(
    array &$javascript,
    AttachedAssetsInterface $assets, LanguageInterface $language,
  ): void {

    if ($this->killSwitch->triggered()) {
      return;
    }

    foreach ($javascript as $filePath => &$settings) {

      // Skip items that are already set to the header as they may need to be
      // render blocking rather than deferred.
      if ($settings['scope'] === 'header') {
        continue;
      }

      $settings['scope'] = 'header';

      if (!isset($settings['attributes']['defer'])) {
        $settings['attributes']['defer'] = true;
      }

    }

  }

  #[Hook('js_settings_build')]
  /**
   * Output 'ajaxPageState' to enable additive JavaScript aggregation.
   *
   * @see https://www.drupal.org/project/refreshless/issues/3414538
   *   Details regarding additive JavaScript aggregation and why it's needed.
   */
  public function outputPageState(
    array &$settings, AttachedAssetsInterface $assets,
  ): void {

    // Don't do anything if ajaxPageState is already present or the kill switch
    // has been triggered.
    if (
      isset($settings['ajaxPageState']) ||
      $this->killSwitch->triggered()
    ) {
      return;
    }

    /** @var string The active theme's machine name. */
    $activeThemeKey = $this->themeManager->getActiveTheme()->getName();

    $settings['ajaxPageState'] = [
      // \system_js_settings_build() builds 'libraries' for us if
      // 'ajaxPageState' is present.
      'theme' => $activeThemeKey,
    ];

    // Only output the theme token if the active theme is different from the
    // default theme.
    //
    // @see \system_js_settings_alter()
    if ($activeThemeKey !== $this->themeHandler->getDefault()) {

      $settings['ajaxPageState'][
        'theme_token'
      ] = $this->csrfTokenGenerator->get($activeThemeKey);

    }

  }

  #[Hook('js_settings_build')]
  /**
   * Output reload reason cookie settings.
   */
  public function outputReloadReasonCookieSettings(
    array &$settings, AttachedAssetsInterface $assets,
  ): void {

    if (!isset($settings['refreshless']['reloadReasonCookie'])) {
      return;
    }

    $cookie = &$settings['refreshless']['reloadReasonCookie'];

    $cookie['name'] = ReloadRequest::getCookieName();

    // If the path was set to anything other than null, don't auto fill it.
    if ($cookie['attributes']['path'] !== null) {
      return;
    }

    // The same thing that system_js_settings_alter() does for
    // drupalSettings.path.baseUrl.
    $cookie['attributes']['path'] = $this->requestStack->getMainRequest()
      ->getBaseUrl() . '/';

  }

  #[Hook('js_settings_build')]
  /**
   * Output page state cookie settings.
   */
  public function outputPageStateCookieSettings(
    array &$settings, AttachedAssetsInterface $assets,
  ): void {

    if (!isset($settings['refreshless']['pageStateCookie'])) {
      return;
    }

    $cookie = &$settings['refreshless']['pageStateCookie'];

    $cookie['name'] = RequestWithPageState::getCookieName();

    // If the path was set to anything other than null, don't auto fill it.
    if ($cookie['attributes']['path'] !== null) {
      return;
    }

    // The same thing that system_js_settings_alter() does for
    // drupalSettings.path.baseUrl.
    $cookie['attributes']['path'] = $this->requestStack->getMainRequest()
      ->getBaseUrl() . '/';

  }

  #[Hook('js_settings_build')]
  /**
   * Output header settings.
   */
  public function outputHeaderSettings(
    array &$settings, AttachedAssetsInterface $assets,
  ): void {

    if (
      isset($settings['refreshless']['headerName']) &&
      $settings['refreshless']['headerName'] !== null
    ) {
      return;
    }

    $settings['refreshless'][
      'headerName'
    ] = RefreshlessRequest::getHeaderName();

  }

  #[Hook('js_settings_build')]
  /**
   * Output stylesheet settings.
   */
  public function outputStylesheetSettings(
    array &$settings, AttachedAssetsInterface $assets,
  ): void {

    if (
      isset($settings['refreshless']['stylesheetOrderAttributeName']) &&
      $settings['refreshless']['stylesheetOrderAttributeName'] !== null
    ) {
      return;
    }

    $settings['refreshless'][
      'stylesheetOrderAttributeName'
    ] = RefreshlessAsset::getStylesheetOrderAttributeName();

  }

}
