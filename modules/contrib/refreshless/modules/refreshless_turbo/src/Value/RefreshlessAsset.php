<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Value;

/**
 * Object containing RefreshLess asset values.
 */
class RefreshlessAsset {

  /**
   * The order attribute name added to stylesheet <link> elements.
   */
  protected const STYLESHEET_ORDER_ATTRIBUTE_NAME =
    'data-refreshless-turbo-order';

  /**
   * Get the order attribute name added to stylesheet <link> elements.
   *
   * @return string
   */
  public static function getStylesheetOrderAttributeName(): string {
    return self::STYLESHEET_ORDER_ATTRIBUTE_NAME;
  }

}
