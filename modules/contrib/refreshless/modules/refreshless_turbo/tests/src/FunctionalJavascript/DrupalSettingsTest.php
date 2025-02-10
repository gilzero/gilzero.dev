<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\Tests\refreshless\FunctionalJavascript\DrupalSettingsTrait;
use Drupal\Tests\refreshless_turbo\FunctionalJavascript\TurboWebDriverTestBase;

/**
 * drupalSettings updater tests.
 *
 * @group refreshless
 *
 * @group refreshless_turbo
 */
class DrupalSettingsTest extends TurboWebDriverTestBase {

  use DrupalSettingsTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block', 'refreshless_turbo', 'refreshless_turbo_drupal_settings_test',
    'system',
  ];


  /**
   * Test that drupalSettings updates are merged when navigating to new pages.
   */
  public function testMerging(): void {

    $this->drupalPlaceBlock('local_tasks_block', [
      'region' => 'content', 'id' => 'local-tasks-block',
    ]);

    $loginUrl = Url::fromRoute('user.login');

    $registerUrl = Url::fromRoute('user.register');

    $resetPasswordUrl = Url::fromRoute('user.pass');

    $this->drupalGet($loginUrl);

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    $this->assertDrupalSettingsEqualsValue(
      'path.currentPath', $loginUrl->getInternalPath(),
    );

    $this->click('[data-drupal-link-system-path="' .
      $registerUrl->getInternalPath() .
    '"]');

    $this->assertWaitOnRefreshlessRequest();

    $this->assertDrupalSettingsEqualsValue(
      'path.currentPath', $registerUrl->getInternalPath(),
    );

    $this->click('[data-drupal-link-system-path="' .
      $resetPasswordUrl->getInternalPath() .
    '"]');

    $this->assertWaitOnRefreshlessRequest();

    $this->assertDrupalSettingsEqualsValue(
      'path.currentPath', $resetPasswordUrl->getInternalPath(),
    );

  }

  /**
   * Test that drupalSettings does not get updated from potential XSS attempts.
   */
  public function testXssPrevention(): void {

    $noXssUrl = Url::fromRoute('refreshless_turbo_drupal_settings_test.no_xss');

    $yesXssUrl = Url::fromRoute(
      'refreshless_turbo_drupal_settings_test.yes_xss',
    );

    $this->drupalGet($noXssUrl);

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    $this->assertDrupalSettingsEqualsValue(
      'path.currentPath', $noXssUrl->getInternalPath(),
    );

    $this->refreshlessHtmlOutput();

    $this->click('main a[href="' . $yesXssUrl->setAbsolute(
      false,
    )->toString() . '"]');

    $this->assertWaitOnRefreshlessRequest();

    $this->assertDrupalSettingsValueExists('path.currentPath');

    // Assert that this remains the Drupal core-generated drupalSettings value
    // and not the value our route controller smuggled into the page content as
    // a second <script> element with additional JSON.
    $this->assertDrupalSettingsEqualsValue(
      'path.currentPath', $yesXssUrl->getInternalPath(),
    );

    $this->assertDrupalSettingsValueDoesNotExist('verySusValue');

  }

}
