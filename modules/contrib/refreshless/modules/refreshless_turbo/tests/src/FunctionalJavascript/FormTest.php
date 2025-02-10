<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\Tests\refreshless_turbo\FunctionalJavascript\TurboWebDriverTestBase;

/**
 * Form tests.
 *
 * @group refreshless
 *
 * @group refreshless_turbo
 */
class FormTest extends TurboWebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'refreshless_turbo',
    'refreshless_turbo_test_tools',
    'system',
  ];

  /**
   * Test basic form submission in admin section.
   */
  public function testBasicSubmit(): void {

    $adminUser = $this->drupalCreateUser([], null, true);

    // This technically also submits a form but it's not the focus of the test.
    $this->drupalLogin($adminUser);

    $siteSettingsUrl = Url::fromRoute('system.site_information_settings');

    $this->drupalGet('admin/config');

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    $this->click('main a[href="' . $siteSettingsUrl->setAbsolute(
      false,
    )->toString() . '"]');

    $this->assertWaitOnRefreshlessRequestAndPageReady();

    $this->assertSession()->addressEquals($siteSettingsUrl);

    // This actually clicks the submit button after filling out the values so
    // it should be handled by Turbo.
    $this->submitForm(['site_name' => 'RefreshLess'], 'Save configuration');

    $this->assertWaitOnRefreshlessRequestAndPageReady();
    // This is necessary to avoid the dreaded stale element reference exception
    // that seems to to get thrown sometimes as a result of
    // WebAssert::fieldValueEquals() probably because our assert wait methods
    // don't currently account for the redirect.
    $this->assertWaitOnRefreshlessRequestAndPageReady();

    $this->assertSession()->fieldValueEquals('site_name', 'RefreshLess');

    // Just to be extra sure the value we set and are seeing on the form was in
    // fact saved to configuration storage.
    $this->assertEquals(
      'RefreshLess',
      $this->container->get('config.factory')->get('system.site')->get('name'),
    );

    $this->assertSession()->statusMessageContains(
      'The configuration options have been saved.',
    );

  }

  /**
   * Test that validation errors are visible after a server-side validation.
   *
   * @see https://www.drupal.org/project/refreshless/issues/3492760
   *   Tests that the fix implemented in this issue continues to work as
   *   expected. Turbo normally refuses to load a page if a POST submit does not
   *   redirect, but that results in nothing happening in just about every
   *   Drupal form when there's a validation error, as Drupal will not redirect
   *   in those cases.
   */
  public function testValidationErrors(): void {

    $user = $this->drupalCreateUser([], null, false, [
      'mail' => 'refreshless@example.com',
    ]);

    // Do a full page load request to start.
    $this->drupalGet('user/register');

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    $this->submitForm([
      'mail'  => 'refreshless@example.com',
      'name'  => 'refreshless',
    ], 'Create new account');

    $this->assertWaitOnRefreshlessRequest();

    $this->assertSession()->statusMessageContains(
      'The email address refreshless@example.com is already taken.', 'error',
    );

  }

}
