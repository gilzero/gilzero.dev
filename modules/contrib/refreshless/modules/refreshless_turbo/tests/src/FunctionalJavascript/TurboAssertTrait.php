<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\FunctionalJavascript;

use Drupal\Tests\refreshless_turbo\FunctionalJavascript\TurboWebDriverAssert;

/**
 * Assert trait for Turbo tests.
 */
trait TurboAssertTrait {

  /**
   * {@inheritdoc}
   */
  public function assertSession($name = null) {
    return new TurboWebDriverAssert($this->getSession($name), $this->baseUrl);
  }

  /**
   * Shortcut for wait on RefreshLess request, assert persist, and HTML output.
   *
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 10000.
   *
   * @param string $message
   *   (optional) A message for exception.
   *
   * @see \Drupal\Tests\refreshless\FunctionalJavascript\RefreshlessWebDriverAssertInterface::assertWaitOnRefreshlessRequest()
   *
   * @see \Drupal\Tests\refreshless\FunctionalJavascript\RefreshlessWebDriverAssertInterface::assertRefreshlessPersisted()
   *
   * @see \Drupal\Tests\refreshless\FunctionalJavascript\RefreshlessHtmlDebugTrait::refreshlessHtmlOutput()
   */
  protected function assertWaitOnRefreshlessRequest(
    int $timeout    = 10000,
    string $message = 'Timed out waiting for RefreshLess request to complete.',
  ): void {

    $this->assertSession()->assertWaitOnRefreshlessRequest($timeout, $message);

    $this->assertSession()->assertRefreshlessPersisted();

    $this->refreshlessHtmlOutput();

  }

  /**
   * Shortcut for wait for RefreshLess page ready, assert persist, HTML output.
   *
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 10000.
   *
   * @param string $message
   *   (optional) A message for exception.
   *
   * @see \Drupal\Tests\refreshless\FunctionalJavascript\RefreshlessWebDriverAssertInterface::assertWaitOnRefreshlessRequest()
   *
   * @see \Drupal\Tests\refreshless\FunctionalJavascript\RefreshlessWebDriverAssertInterface::assertWaitOnRefreshlessPageReady()
   *
   * @see \Drupal\Tests\refreshless\FunctionalJavascript\RefreshlessWebDriverAssertInterface::assertRefreshlessPersisted()
   *
   * @see \Drupal\Tests\refreshless\FunctionalJavascript\RefreshlessHtmlDebugTrait::refreshlessHtmlOutput()
   */
  protected function assertWaitOnRefreshlessRequestAndPageReady(): void {

    $this->assertSession()->assertWaitOnRefreshlessRequest();

    $this->assertSession()->assertWaitOnRefreshlessPageReady();

    $this->assertSession()->assertRefreshlessPersisted();

    $this->refreshlessHtmlOutput();

  }

}
