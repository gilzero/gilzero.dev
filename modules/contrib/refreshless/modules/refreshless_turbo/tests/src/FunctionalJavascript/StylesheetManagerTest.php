<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\Tests\refreshless_turbo\FunctionalJavascript\TurboWebDriverTestBase;

/**
 * Stylesheet manager tests.
 *
 * @group refreshless
 *
 * @group refreshless_turbo
 */
class StylesheetManagerTest extends TurboWebDriverTestBase {

  /**
   * The route name for the route one.
   */
  protected const ROUTE_ONE_NAME = 'refreshless_turbo_stylesheet_test.one';

  /**
   * The route name for the route two.
   */
  protected const ROUTE_TWO_NAME = 'refreshless_turbo_stylesheet_test.two';

  /**
   * The route name for the route three.
   */
  protected const ROUTE_THREE_NAME = 'refreshless_turbo_stylesheet_test.three';

  /**
   * The route name for the route four.
   */
  protected const ROUTE_FOUR_NAME = 'refreshless_turbo_stylesheet_test.four';

  /**
   * The route name for the route five.
   */
  protected const ROUTE_FIVE_NAME = 'refreshless_turbo_stylesheet_test.five';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'refreshless_turbo_stylesheet_test', 'system',
  ];

  /**
   * Assert that a CSS custom property equals an expected value.
   *
   * @param string $propertyName
   *   The CSS custom property name, including the leading '--'.
   *
   * @param string $expectedValue
   *   The expected value of the custom property.
   *
   * @param string $selector
   *   A selector to find the element by. Defaults to 'html'.
   *
   * @see https://developer.mozilla.org/en-US/docs/Web/API/Window/getComputedStyle
   *
   * @see https://developer.mozilla.org/en-US/docs/Web/API/CSSStyleDeclaration/getPropertyValue
   */
  protected function assertCssCustomPropertyEquals(
    string $propertyName, string $expectedValue, string $selector = 'html',
  ): void {

    $actualValue = $this->getSession()->evaluateScript(<<<JS
      return window.getComputedStyle(document.querySelector(
        '{$selector}',
      )).getPropertyValue('{$propertyName}').trim();
    JS);

    $this->assertEquals(
      $expectedValue, $actualValue,
      "CSS custom property \"$propertyName\" was expected to equal \"$expectedValue\" but got \"$actualValue\"!",
    );

  }

  /**
   * Test stylesheet merging as we navigate across pages.
   */
  public function testStylesheetMerging(): void {

    $routeOneUrl = Url::fromRoute(self::ROUTE_ONE_NAME);

    $routeTwoUrl = Url::fromRoute(self::ROUTE_TWO_NAME);

    $routeThreeUrl = Url::fromRoute(self::ROUTE_THREE_NAME);

    $routeFourUrl = Url::fromRoute(self::ROUTE_FOUR_NAME);

    $routeFiveUrl = Url::fromRoute(self::ROUTE_FIVE_NAME);

    $this->drupalGet($routeOneUrl);

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-one', 'true',
    );

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-specificity', 'one',
    );

    // These should not be present yet so they'll be empty strings.
    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-two', '',
    );
    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-three', '',
    );

    $this->click('main a[href="' . $routeTwoUrl->setAbsolute(
      false,
    )->toString() . '"]');

    $this->assertWaitOnRefreshlessRequest();

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-one', 'true',
    );

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-two', 'true',
    );

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-specificity', 'two',
    );

    // Should not be present yet.
    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-three', '',
    );

    $this->click('main a[href="' . $routeThreeUrl->setAbsolute(
      false,
    )->toString() . '"]');

    $this->assertWaitOnRefreshlessRequest();

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-one', 'true',
    );

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-two', 'true',
    );

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-three', 'true',
    );

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-specificity', 'three',
    );

    // Now we need to do a full page load to test that a dependency gets
    // inserted before an existing library (correct) rather than after
    // (incorrect).
    $this->drupalGet($routeFiveUrl);

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-five', 'true',
    );

    // This should not be present yet so it'll be an empty string.
    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-four', '',
    );

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-specificity', 'five',
    );

    $this->click('main a[href="' . $routeFourUrl->setAbsolute(
      false,
    )->toString() . '"]');

    $this->assertWaitOnRefreshlessRequest();

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-four', 'true',
    );

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-five', 'true',
    );

    $this->assertCssCustomPropertyEquals(
      '--refreshless-turbo-test-specificity', 'four',
    );

  }

}
