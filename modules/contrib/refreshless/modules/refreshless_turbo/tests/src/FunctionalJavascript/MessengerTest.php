<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\Tests\refreshless_turbo\FunctionalJavascript\TurboWebDriverTestBase;

/**
 * Decorated messenger service tests.
 *
 * @group refreshless
 *
 * @group refreshless_turbo
 */
class MessengerTest extends TurboWebDriverTestBase {

  /**
   * The expected message when Turbo reloads route one.
   */
  protected const ROUTE_ONE_MESSAGE = 'RefreshLess test message for route one.';

  /**
   * The expected message when Turbo reloads route two.
   */
  protected const ROUTE_TWO_MESSAGE = 'RefreshLess test message for route two.';

  /**
   * The route name for the route one.
   */
  protected const ROUTE_ONE_NAME = 'refreshless_turbo_messenger_test.one';

  /**
   * The route name for the route two.
   */
  protected const ROUTE_TWO_NAME = 'refreshless_turbo_messenger_test.two';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'refreshless_turbo_messenger_test', 'refreshless_turbo', 'system',
  ];

  /**
   * Test that messages are not lost when Turbo does a reload (full page load).
   */
  public function testMessengerReload(): void {

    $routeOneUrl = Url::fromRoute(self::ROUTE_ONE_NAME);

    $routeTwoUrl = Url::fromRoute(self::ROUTE_TWO_NAME);

    $this->drupalGet($routeOneUrl);

    // Messages should not be present on a full page load that wasn't the
    // result of a Turbo reload.
    $this->assertSession()->statusMessageNotContains(
      self::ROUTE_ONE_MESSAGE,
      'status',
    );
    $this->assertSession()->statusMessageNotContains(
      self::ROUTE_ONE_MESSAGE,
      'warning',
    );
    $this->assertSession()->statusMessageNotContains(
      self::ROUTE_ONE_MESSAGE,
      'error',
    );

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    $this->click('main a[href="' . $routeTwoUrl->setAbsolute(
      false,
    )->toString() . '"]');

    // We still need to wait for Turbo to send the request, as it only performs
    // the reload after it gets the response that triggers it.
    //
    // Note that this should not use $this->assertWaitOnRefreshlessRequest() as
    // that asserts RefreshLess persisted which is not the expected case here.
    $this->assertSession()->assertWaitOnRefreshlessRequest();

    $this->assertSession()->addressEquals($routeTwoUrl);

    $this->assertSession()->assertRefreshlessIsPresent();

    // This checks that a full load wiped out the persist check as expected.
    $this->assertSession()->assertRefreshlessNotPersisted();

    // The route two messages should be present.
    $this->assertSession()->statusMessageContains(
      self::ROUTE_TWO_MESSAGE,
      'status',
    );
    $this->assertSession()->statusMessageContains(
      self::ROUTE_TWO_MESSAGE,
      'warning',
    );
    $this->assertSession()->statusMessageContains(
      self::ROUTE_TWO_MESSAGE,
      'error',
    );

    // The route one messages should not be present here.
    $this->assertSession()->statusMessageNotContains(
      self::ROUTE_ONE_MESSAGE,
      'status',
    );
    $this->assertSession()->statusMessageNotContains(
      self::ROUTE_ONE_MESSAGE,
      'warning',
    );
    $this->assertSession()->statusMessageNotContains(
      self::ROUTE_ONE_MESSAGE,
      'error',
    );

    $this->refreshlessHtmlOutput();

    $this->assertSession()->startRefreshlessPersist();

    // Now back to route one by clicking the link which should trigger Turbo to
    // reload.
    $this->click('main a[href="' . $routeOneUrl->setAbsolute(
      false,
    )->toString() . '"]');

    // We still need to wait for Turbo to send the request, as it only performs
    // the reload after it gets the response that triggers it.
    //
    // Note that this should not use $this->assertWaitOnRefreshlessRequest() as
    // that asserts RefreshLess persisted which is not the expected case here.
    $this->assertSession()->assertWaitOnRefreshlessRequest();

    $this->assertSession()->addressEquals($routeOneUrl);

    $this->assertSession()->assertRefreshlessIsPresent();

    // This checks that a full load wiped out the persist check as expected.
    $this->assertSession()->assertRefreshlessNotPersisted();

    // The route one messages should be present.
    $this->assertSession()->statusMessageContains(
      self::ROUTE_ONE_MESSAGE,
      'status',
    );
    $this->assertSession()->statusMessageContains(
      self::ROUTE_ONE_MESSAGE,
      'warning',
    );
    $this->assertSession()->statusMessageContains(
      self::ROUTE_ONE_MESSAGE,
      'error',
    );

    // The route two messages should not be present here.
    $this->assertSession()->statusMessageNotContains(
      self::ROUTE_TWO_MESSAGE,
      'status',
    );
    $this->assertSession()->statusMessageNotContains(
      self::ROUTE_TWO_MESSAGE,
      'warning',
    );
    $this->assertSession()->statusMessageNotContains(
      self::ROUTE_TWO_MESSAGE,
      'error',
    );

    $this->refreshlessHtmlOutput();

  }

}
