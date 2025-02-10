<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\FunctionalJavascript;

use Behat\Mink\Exception\ElementHtmlException;
use Behat\Mink\Exception\ExpectationException;
use Drupal\Tests\refreshless\FunctionalJavascript\RefreshlessWebDriverAssertBase;
use RuntimeException;

/**
 * WebDriverWebAssert class extended with Turbo assertion methods.
 *
 * @see \Drupal\Tests\refreshless_turbo\FunctionalJavascript\TurboAssertTrait::assertSession()
 */
class TurboWebDriverAssert extends RefreshlessWebDriverAssertBase {

  /**
   * Name of the attribute set on the <html> element to track Turbo persisting.
   */
  protected const TURBO_PERSIST_ATTR = 'data-refreshless-turbo-test';

  /**
   * Name of the attribute added to <html> when behaviours are attached.
   */
  protected const BEHAVIOURS_ATTACHED_ATTR = 'data-refreshless-turbo-behaviours-attached';

  /**
   * {@inheritdoc}
   */
  public function assertRefreshlessIsPresent(): void {

    $result = $this->session->getDriver()->wait(10000, <<<JS
      'Turbo' in window;
    JS);

    $this->assert(
      $result === true, 'Turbo is not present in the window object.',
    );

  }

  /**
   * {@inheritdoc}
   *
   * @throws \RuntimeException
   *   When the request is not completed within the specified timeoput. If left
   *   blank, a default message will be displayed.
   *
   * @see \Drupal\FunctionalJavascriptTests\JSWebAssert::assertWaitOnAjaxRequest()
   *   Loosely based on this core assert method.
   */
  public function assertWaitOnRefreshlessRequest(
    int $timeout    = 10000,
    string $message = 'Timed out waiting for RefreshLess request to complete.',
  ): void {

    // Wait for a very short time to allow page state to update after clicking.
    \usleep(5000);

    $condition = <<<JS
      (function() {

        return typeof jQuery('html').attr('aria-busy') === 'undefined';

      }());
    JS;

    $result = $this->session->wait($timeout, $condition);

    if ($result !== true) {
      throw new RuntimeException($message);
    }

  }

  /**
   * {@inheritdoc}
   *
   * @throws \RuntimeException
   *   When the page does not become ready within the specified timeout. If left
   *   blank, a default message will be displayed.
   *
   * @todo Rework this to use the 'refreshless:load' event to signal the page
   *   being ready?
   */
  public function assertWaitOnRefreshlessPageReady(
    int $timeout    = 10000,
    string $message = 'Timed out waiting for the page to be ready.',
  ): void {

    if (\Drupal::service('module_handler')->moduleExists(
      'refreshless_turbo_test_tools',
    ) === false) {

      $method = __METHOD__;

      throw new RuntimeException(
        "You must install the \"refreshless_turbo_test_tools\" module to use \"$method()\"!",
      );

    }

    // Wait for a very short time to allow page state to update after clicking.
    \usleep(5000);

    $attrName = self::BEHAVIOURS_ATTACHED_ATTR;

    $condition = <<<JS
      (function() {

        return typeof jQuery('html').attr('{$attrName}') !== 'undefined';

      }());
    JS;

    $result = $this->session->wait($timeout, $condition);

    if ($result !== true) {
      throw new RuntimeException($message);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function startRefreshlessPersist(): void {

    $attributeName = self::TURBO_PERSIST_ATTR;

    $this->session->evaluateScript(<<<JS
      jQuery('html').attr('{$attributeName}', true);
    JS);

  }

  /**
   * {@inheritdoc}
   */
  public function assertRefreshlessPersisted(): void {

    try {

      $this->elementAttributeExists('css', 'html', self::TURBO_PERSIST_ATTR);

    } catch (ElementHtmlException $exception) {

      throw new ExpectationException(
        'Either a full, non-Turbo page load has occurred, or TurboWebDriverAssert::startRefreshlessPersist() was not called beforehand.',
        $this->session->getDriver(),
      );

    }

  }

  /**
   * {@inheritdoc}
   */
  public function assertRefreshlessNotPersisted(): void {

    try {

      $this->elementAttributeNotExists('css', 'html', self::TURBO_PERSIST_ATTR);

    } catch (ElementHtmlException $exception) {

      throw new ExpectationException(
        'Either a full page load has not occurred, or TurboWebDriverAssert::startRefreshlessPersist() was called since the last full page load.',
        $this->session->getDriver(),
      );

    }

  }

}
