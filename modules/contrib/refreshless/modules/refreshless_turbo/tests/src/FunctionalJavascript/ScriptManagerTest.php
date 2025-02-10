<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\Tests\refreshless_turbo\FunctionalJavascript\TurboWebDriverTestBase;

/**
 * Script manager tests.
 *
 * @group refreshless
 *
 * @group refreshless_turbo
 */
class ScriptManagerTest extends TurboWebDriverTestBase {

  /**
   * The route name for output library only attached.
   */
  protected const ROUTE_OUTPUT_ONLY =
    'refreshless_turbo_script_manager_test.output_only';

  /**
   * The route name for remover library only attached.
   */
  protected const ROUTE_REMOVER_ONLY =
    'refreshless_turbo_script_manager_test.remover_only';

  /**
   * The route name for both libraries attached.
   */
  protected const ROUTE_BOTH = 'refreshless_turbo_script_manager_test.both';

  /**
   * The 'id' attribute value to find output by.
   */
  protected const OUTPUT_IDENTIFIER =
    'refreshless-turbo-script-manager-test-output';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'refreshless_turbo_script_manager_test', 'system',
  ];

  /**
   * Test script merge events.
   */
  public function testMergeEvents(): void {

    $routeRemoverOnlyUrl = Url::fromRoute(self::ROUTE_REMOVER_ONLY);

    $routeOutputOnlyUrl = Url::fromRoute(self::ROUTE_OUTPUT_ONLY);

    $routeBothUrl = Url::fromRoute(self::ROUTE_BOTH);

    $this->drupalGet($routeOutputOnlyUrl);

    $this->assertSession()->elementExists(
      'css', 'main #' . self::OUTPUT_IDENTIFIER,
    );

    $this->drupalGet($routeRemoverOnlyUrl);

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    $this->click('main a[href="' . $routeBothUrl->setAbsolute(
      false,
    )->toString() . '"]');

    $this->assertWaitOnRefreshlessRequest();

    $this->assertSession()->elementNotExists(
      'css', 'main #' . self::OUTPUT_IDENTIFIER,
    );

  }

}
