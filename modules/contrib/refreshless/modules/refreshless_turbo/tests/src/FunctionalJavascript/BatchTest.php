<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\Tests\refreshless_turbo\FunctionalJavascript\TurboWebDriverTestBase;

/**
 * Batch UI tests.
 *
 * @group refreshless
 *
 * @group refreshless_turbo
 */
class BatchTest extends TurboWebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'refreshless_turbo',
    'refreshless_turbo_test_tools',
    'system',
    'update',
  ];

  /**
   * Test running check for updates batch via the UI.
   *
   * This ensures both that the batch UI continues to function as expected with
   * RefreshLess active and that our replacement of core's batch.js performs a
   * RefreshLess visit when the batch completes rather than the full page load
   * core's JavaScript forces.
   */
  public function testCheckForUpdates(): void {

    $this->drupalPlaceBlock('local_tasks_block', [
      'region' => 'content', 'id' => 'local-tasks-block',
    ]);

    $adminUser = $this->drupalCreateUser([], null, true);

    $this->drupalLogin($adminUser);

    $updateStatusUrl = Url::fromRoute('update.status');

    $updateReportUrl = Url::fromRoute('update.report_update');

    $this->drupalGet($updateStatusUrl);

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    $this->click('main a[href="' . $updateReportUrl->setAbsolute(
      false,
    )->toString() . '"]');

    $this->assertWaitOnRefreshlessRequestAndPageReady();

    $this->clickLink('Check manually');

    $this->assertWaitOnRefreshlessRequestAndPageReady();

    $this->assertSession()->assertVisibleInViewport('css', '#updateprogress');

    $this->assertSession()->waitForElementText(
      'css', '#updateprogress .progress__percentage', '100%',
    );

    $this->refreshlessHtmlOutput();

    $this->assertSession()->waitForElementRemoved('css', '#updateprogress');

    $this->assertWaitOnRefreshlessRequestAndPageReady();

    $this->assertSession()->statusMessageContains(
      'Checked available update data for', 'status',
    );

  }

}
