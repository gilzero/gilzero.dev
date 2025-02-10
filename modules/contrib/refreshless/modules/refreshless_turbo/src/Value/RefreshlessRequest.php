<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Value;

use Drupal\refreshless_turbo\Value\RequestWithPageState;

/**
 * Value object wrapping a potential RefreshLess Turbo request.
 */
class RefreshlessRequest extends RequestWithPageState {

  /**
   * Name of the header indicating a RefreshLess Turbo request.
   */
  protected const HEADER_NAME = 'x-refreshless-turbo';

  /**
   * Whether this is a Turbo request.
   *
   * @return boolean
   */
  public function isTurbo(): bool {
    return $this->request->headers->has(self::HEADER_NAME);
  }

  /**
   * Get the HTTP header name identifying a RefreshLess request.
   *
   * @return string
   */
  public static function getHeaderName(): string {
    return self::HEADER_NAME;
  }

}
