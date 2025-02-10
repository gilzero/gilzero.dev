<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless\FunctionalJavascript;

use function implode;

/**
 * Provides debug methods for logging HTML after RefreshLess navigation.
 */
trait RefreshlessHtmlDebugTrait {

  /**
   * Log HTML output for the current URL, usually after RefreshLess navigation.
   *
   * For full page loads using UiHelperTrait::submitForm() and
   * UiHelperTrait::drupalGet(), those methods will log HTML output; since
   * RefreshLess circumvents those and doesn't do a full page load, we have to
   * log the HTML ourselves.
   *
   * @see \Drupal\Tests\UiHelperTrait::submitForm()
   * @see \Drupal\Tests\UiHelperTrait::drupalGet()
   * @see \Drupal\Tests\UiHelperTrait::click()
   * @see \Drupal\Tests\BrowserHtmlDebugTrait::htmlOutput()
   */
  public function refreshlessHtmlOutput(): void {

    // This copies core's logic in various methods that output HTML. The second
    // check for Guzzle is probably not going to ever return true since we only
    // intend this trait to run via WebDriverTestBase but is kept in just in
    // case.
    if (!(
      $this->htmlOutputEnabled === true &&
      $this->isTestUsingGuzzleClient() === false
    )) {
      return;
    }

    $output = implode('', [
      'RefreshLess output for: ',
      $this->getSession()->getCurrentUrl(),
      '<hr />',
      $this->getSession()->getPage()->getContent(),
      $this->getHtmlOutputHeaders(),
    ]);

    $this->htmlOutput($output);

  }

}
