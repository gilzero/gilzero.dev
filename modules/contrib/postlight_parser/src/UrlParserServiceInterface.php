<?php

namespace Drupal\postlight_parser;

/**
 * Url parser service interface.
 */
interface UrlParserServiceInterface {

  /**
   * {@inheritDoc}
   */
  public function parser(array $settings): array;

}
