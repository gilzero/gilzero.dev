<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless\FunctionalJavascript;

use Behat\Mink\Element\Element;
use Drupal\FunctionalJavascriptTests\WebDriverWebAssert;
use Drupal\Tests\refreshless\FunctionalJavascript\RefreshlessWebDriverAssertInterface;
use function preg_match;
use function preg_quote;
use function preg_replace;

/**
 * WebDriverWebAssert class extended with RefreshLess methods.
 */
abstract class RefreshlessWebDriverAssertBase extends WebDriverWebAssert implements RefreshlessWebDriverAssertInterface {

  /**
   * {@inheritdoc}
   */
  public function waitForElementText(
    string $selector, string|array $locator, string $text, int $timeout = 10000,
  ): bool {

    $element = $this->waitForElement($selector, $locator, $timeout);

    // Note that ElementInterface::waitFor() takes timeout in seconds rather
    // than milliseconds.
    return (bool) $element->waitFor($timeout / 1000, function (
      Element $element,
    ) use ($text) {

      $actual = preg_replace('/\s+/u', ' ', $element->getText());

      $regex = '/' . preg_quote($text, '/') . '/ui';

      return (bool) preg_match($regex, $actual);

    });

  }

}
