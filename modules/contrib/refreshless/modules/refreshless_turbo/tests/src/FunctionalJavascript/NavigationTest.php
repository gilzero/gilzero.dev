<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\Tests\refreshless_turbo\FunctionalJavascript\TurboWebDriverTestBase;

/**
 * Navigation tests.
 *
 * @group refreshless
 *
 * @group refreshless_turbo
 */
class NavigationTest extends TurboWebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'refreshless_turbo', 'system'];

  /**
   * Test basic navigation using Turbo Drive.
   */
  public function testBasic(): void {

    // Place the local tasks block so that we have Turbo Drive-enabled links to
    // click.
    $this->drupalPlaceBlock('local_tasks_block', [
      'region' => 'content', 'id' => 'local-tasks-block',
    ]);

    // Do a full page load request to start.
    $this->drupalGet('user/login');

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    // Click a Turbo Drive-enabled link.
    $this->click('[data-drupal-link-system-path="user/register"]');

    $this->assertWaitOnRefreshlessRequest();

    // Assert that the user register form is now available.
    $this->assertSession()->elementExists(
      'css', 'form[data-drupal-selector="user-register-form"]',
    );

    // Click a Turbo Drive-enabled link.
    $this->click('[data-drupal-link-system-path="user/password"]');

    $this->assertWaitOnRefreshlessRequest();

    // Assert that the reset password form is now available.
    $this->assertSession()->elementExists(
      'css', 'form[data-drupal-selector="user-pass"]',
    );

  }

  /**
   * Test basic navigation in admin section using Turbo Drive.
   */
  public function testAdmin(): void {

    $adminUser = $this->drupalCreateUser([], null, true);

    $this->drupalLogin($adminUser);

    $this->drupalPlaceBlock('system_breadcrumb_block', [
      'region' => 'breadcrumb', 'id' => 'breadcrumbs',
    ]);

    $adminConfigUrl = Url::fromRoute('system.admin_config');

    $siteSettingsUrl = Url::fromRoute('system.site_information_settings');

    $performanceSettingsUrl = Url::fromRoute('system.performance_settings');

    $this->drupalGet($adminConfigUrl);

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    $this->click('main a[href="' . $siteSettingsUrl->setAbsolute(
      false,
    )->toString() . '"]');

    $this->assertWaitOnRefreshlessRequest();

    $this->assertSession()->addressEquals($siteSettingsUrl);

    $this->assertSession()->elementExists(
      'css', 'input[data-drupal-selector="edit-site-name"]',
    );

    $this->click('#block-breadcrumbs a[href="' . $adminConfigUrl->setAbsolute(
      false,
    )->toString() . '"]');

    $this->assertWaitOnRefreshlessRequest();

    $this->assertSession()->addressEquals($adminConfigUrl);

    $this->click('main a[href="' . $performanceSettingsUrl->setAbsolute(
      false,
    )->toString() . '"]');

    $this->assertWaitOnRefreshlessRequest();

    $this->assertSession()->addressEquals($performanceSettingsUrl);

    $this->assertSession()->buttonExists('Clear all caches');

  }

}
