<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless\FunctionalJavascript;

/**
 * Interface for RefreshLess functional JavaScript assert objects.
 */
interface RefreshlessWebDriverAssertInterface {

  /**
   * Assert that RefreshLess is present on the page.
   */
  public function assertRefreshlessIsPresent(): void;

  /**
   * Waits for a RefreshLess request to complete.
   *
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 10000.
   *
   * @param string $message
   *   (optional) A message for exception.
   */
  public function assertWaitOnRefreshlessRequest(
    int $timeout    = 10000,
    string $message = 'Timed out waiting for RefreshLess request to complete.',
  ): void;

  /**
   * Waits for the page to become ready after a RefreshLess request.
   *
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 10000.
   *
   * @param string $message
   *   (optional) A message for exception.
   */
  public function assertWaitOnRefreshlessPageReady(
    int $timeout    = 10000,
    string $message = 'Timed out waiting for the page to be ready.',
  ): void;

  /**
   * Start tracking whether the document persists after RefreshLess navigation.
   *
   * @see $this->assertRefreshlessPersisted()
   *
   * @see $this->assertRefreshlessNotPersisted()
   */
  public function startRefreshlessPersist(): void;

  /**
   * Assert that the document has persisted after RefreshLess navigation.
   *
   * @see $this->startRefreshlessPersist()
   *   Must be called to start tracking persist state before asserting.
   *
   * @see $this->assertRefreshlessNotPersisted()
   *   Inverse of this method.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   If it doesn't appear the page has persisted, i.e. a full page load has
   *   occurred.
   */
  public function assertRefreshlessPersisted(): void;

  /**
   * Assert that the document has not persisted due to RefreshLess navigation.
   *
   * @see $this->startRefreshlessPersist()
   *   Must be called to start tracking persist state before asserting.
   *
   * @see $this->assertRefreshlessPersisted()
   *   Inverse of this method.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   If it appears the page has persisted, i.e. a full page load has not
   *   occurred.
   */
  public function assertRefreshlessNotPersisted(): void;

  /**
   * Wait for text to become available in an element.
   *
   * Neither Drupal's JSWebAssert nor Mink's WebAssert have a method
   * specifically for waiting for an element's text to become a value. Drupal
   * has JSWebAssert::waitForText() but that's page-wide without the ability to
   * limit to certain elements. JSWebAssert::waitForHelper() is also a private
   * method so we have to re-implement both.
   *
   * @param string $selector
   *   The selector engine name. See ElementInterface::findAll() for the
   *   supported selectors.
   *
   * @param string|array $locator
   *   The selector locator.
   *
   * @param string $text
   *   The text to wait for.
   *
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 10000.
   *
   * @return bool
   *   True if found within the timeout, false otherwise.
   *
   * @see \Drupal\FunctionalJavascriptTests\JSWebAssert::waitForText()
   *
   * @see \Drupal\FunctionalJavascriptTests\JSWebAssert::waitForHelper()
   *
   * @see \Behat\Mink\WebAssert
   *
   * @todo Contribute upstream to Drupal core?
   */
  public function waitForElementText(
    string $selector, string|array $locator, string $text, int $timeout = 10000,
  ): bool;

}
