<?php

namespace Drupal\Tests\launch_checklist\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Test basic functionality of the Launch Checklist module.
 *
 * @group launch_checklist
 */
class BaseTests extends BrowserTestBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    // Module(s) for core functionality.
    'block',
    'field',
    'field_ui',
    'node',
    'views',
    'views_ui',

    // Contrib modules.
    'checklistapi',

    // This custom module.
    'launch_checklist',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    // Make sure to complete the normal setup steps first.
    parent::setUp();

    // Set the front page to "node".
    \Drupal::configFactory()
      ->getEditable('system.site')
      ->set('page.front', '/node')
      ->save(TRUE);
  }

  /**
   * Make sure the site still works.
   */
  public function testTheSiteStillWorks() {
    // Load the front page.
    $this->drupalGet('<front>');

    // Confirm that the site didn't throw a server error or something else.
    $this->assertSession()->statusCodeEquals(200);

    // Confirm that the front page contains the standard text.
    $this->assertSession()->pageTextContains('Welcome to Drupal');

  }

  /**
   * Checks if the Launch Checklist loads.
   */
  public function testLaunchChecklistLoads() {
    // Create a user with the right permission and log in.
    $permissions = [
      'access administration pages',
      'administer site configuration',
      'edit launch_checklist checklistapi checklist',
    ];
    $account = $this->drupalCreateUser($permissions);
    $this->drupalLogin($account);

    // Navigate to the checklist.
    $this->drupalGet('admin/config/development/launch-checklist');

    // Confirm that the site didn't throw a server error or something else.
    $this->assertSession()->statusCodeEquals(200);

    // Confirm that the front page contains the standard text.
    $this->assertSession()->pageTextContains('General settings for the Drupal site.');

  }

  /**
   * Checks if we can check and save an item.
   */
  public function testLaunchChecklistItem() {
    // Create a user with the right permission and log in.
    $permissions = [
      'access administration pages',
      'administer site configuration',
      'edit launch_checklist checklistapi checklist',
    ];
    $account = $this->drupalCreateUser($permissions);
    $this->drupalLogin($account);

    // Navigate to the checklist.
    $this->drupalGet('admin/config/development/launch-checklist');

    // Confirm that the site didn't throw a server error or something else.
    $this->assertSession()->statusCodeEquals(200);

    // Submit form with the first item checked.
    $this->submitForm([
      'edit-checklistapi-01-general-site-information-email' => '1',
    ], $this->t('Save'));
    // Verify the item has been saved.
    $this->assertSession()->pageTextContains('Launch checklist progress has been saved. 1 item changed. ');
  }

}
