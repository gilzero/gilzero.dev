<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Value;

use Drupal\Component\Utility\UrlHelper;
use function explode;
use function implode;
use function json_decode;
use Symfony\Component\HttpFoundation\Request;
use ValueError;

/**
 * Value object wrapping a potential RefreshLess request with page state.
 */
class RequestWithPageState {

  /**
   * Name of the cookie containing page state and already loaded libraries.
   */
  protected const COOKIE_NAME = 'refreshless-turbo-page-state';

  /**
   * Whether the request has RefreshLess Turbo page state data.
   *
   * @var boolean
   */
  protected bool $hasPageState = false;

  /**
   * Libraries already on the page, if found in the page state.
   *
   * @var string[]
   */
  protected array $libraries = [];

  /**
   * A theme token, if one is found in the page state.
   *
   * @var string
   */
  protected string $themeToken = '';

  /**
   * Constructor; parses and builds values.
   */
  public function __construct(
    protected readonly Request $request,
  ) {

    if (!$this->request->cookies->has(self::COOKIE_NAME)) {
      return;
    }

    $cookieValue = $this->request->cookies->get(self::COOKIE_NAME);

    try {

      $cookieParsed = json_decode($cookieValue, true);

    // @todo Don't catch this so it can be detected as a failure state?
    } catch (ValueError $error) {
      return;
    }

    // Only attempt to decompress the libraries value if it's present and not an
    // empty value.
    if (!empty($cookieParsed['libraries'])) {

      $this->libraries = explode(
        ',', UrlHelper::uncompressQueryParameter($cookieParsed['libraries']),
      );

      $this->hasPageState = true;

    }

    if (!empty($cookieParsed['theme_token'])) {
      $this->themeToken = $cookieParsed['theme_token'];
    }

  }

  /**
   * Whether this request has page state data.
   *
   * @return boolean
   */
  public function hasPageState(): bool {
    return $this->hasPageState;
  }

  /**
   * Get any libraries already on the page.
   *
   * @return string[]
   */
  public function getLibraries(): array {
    return $this->libraries;
  }

  /**
   * Set the libraries already on the page.
   *
   * @param string[] $libraries
   */
  public function setLibraries(array $libraries): void {
    $this->libraries = $libraries;
  }

  /**
   * Get the theme token in the request, if any.
   *
   * @return string
   */
  public function getThemeToken(): string {
    return $this->themeToken;
  }

  /**
   * Set the theme token for this request.
   *
   * @param string $themeToken
   */
  public function setThemeToken(string $themeToken): void {
    $this->themeToken = $themeToken;
  }

  /**
   * Format the libraries and theme token suitable for ajax_page_state.
   *
   * Note that this intentionally leaves out everything but the 'libraries'
   * and 'theme_token' keys for two reasons:
   *
   * 1. If we were to include the 'theme' key, this would cause Drupal to
   *    generate the page using the specified theme rather than the theme it
   *    would generate the page with when doing a hard load; in more concrete
   *    terms, this wouldn't switch between the front-end and admin themes as
   *    expected but would keep loading admin pages on the front-end theme or
   *    vice versa, depending on which theme you started on.
   *
   * 2. Security, basically.
   *
   * @return array[]
   */
  public function toAjaxPageState(): array {

    return [
      'libraries'   => implode(',', $this->libraries),
      'theme_token' => $this->themeToken,
    ];

  }

  /**
   * Get the reload reason cookie name.
   *
   * @return string
   */
  public static function getCookieName(): string {
    return self::COOKIE_NAME;
  }

}
