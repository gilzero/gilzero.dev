<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Value;

use Symfony\Component\HttpFoundation\Request;

/**
 * Value object wrapping a potential RefreshLess Turbo reload request.
 */
class ReloadRequest {

  /**
   * Name of the cookie passed by our JavaScript indicating a reload.
   */
  protected const RELOAD_REASON_COOKIE_NAME = 'refreshless-turbo-reload-reason';

  /**
   * Any reason for the reload contained in the cookie.
   *
   * @var string
   */
  protected string $reason = '';

  /**
   * Constructor; parses and builds values.
   */
  public function __construct(
    protected readonly Request $request,
  ) {

    if (!$this->request->cookies->has(self::RELOAD_REASON_COOKIE_NAME)) {
      return;
    }

    $this->reason = $this->request->cookies->get(
      self::RELOAD_REASON_COOKIE_NAME,
    );

  }

  /**
   * Whether the wrapped request is a RefreshLess Turbo reload request.
   *
   * @return boolean
   *   True if this is a RefreshLess Turbo reload request; false otherwise.
   */
  public function isReload(): bool {
    return !empty($this->reason);
  }

  /**
   * Get the reason for the reload.
   *
   * @return string
   *   The string provided by Hotwire Turbo as reason for the reload.
   */
  public function getReason(): string {
    return $this->reason;
  }

  /**
   * Get the reload reason cookie name.
   *
   * @return string
   */
  public static function getCookieName(): string {
    return self::RELOAD_REASON_COOKIE_NAME;
  }

}
