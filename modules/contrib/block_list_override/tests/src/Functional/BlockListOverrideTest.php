<?php

namespace Drupal\Tests\block_list_override\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests for the Block List Override module.
 *
 * @group block_list_override
 */
class BlockListOverrideTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block_list_override',
    'block',
    'layout_discovery',
    'layout_builder',
    'field',
  ];

  /**
   * Values to override.
   *
   * Array of configuration options with the rule to use and the ID of a block
   * the rule should show/hide.
   */
  protected static $replace = [
    'system_match' => [
      'label' => 'System List Match',
      'rule' => 'user_login_block',
      'hides' => 'user_login_block',
    ],
    'system_prefix' => [
      'label' => 'System List Prefix',
      'rule' => 'system_menu_block',
      'hides' => 'system_menu_block:admin',
    ],
    'system_regex' => [
      'label' => 'System List Regex',
      'rule' => '/local_(.*)_block/',
      'hides' => 'local_tasks_block',
    ],
    'layout_match' => [
      'label' => 'Layout Builder List Match',
      'rule' => 'page_title_block',
      'hides' => 'page_title_block',
    ],
    'layout_prefix' => [
      'label' => 'Layout Builder List Prefix',
      'rule' => 'extra_field_block',
      'hides' => 'extra_field_block:user:user:member_for',
    ],
    'layout_regex' => [
      'label' => 'Layout Builder List Regex',
      'rule' => '/system_(.*)_block/',
      'hides' => 'system_powered_by_block',
    ],
  ];


  /**
    * A simple user.
    */
  private $user;

  /**
   * Perform initial setup tasks that run before every test method.
   */
  public function setUp(): void {
    parent::setUp();
    $this->user = $this->drupalCreateUser([
      'administer site configuration',
      'access block list override',
    ]);
  }

  /**
   * Tests that the Block List Override pages can be reached before configuration.
   */
  public function testBlockListPagesExist() {
    $this->drupalLogin($this->user);
    $pages = [
      'admin/config/block_list_override/settings',
      'admin/config/block_list_override/system-list',
      'admin/config/block_list_override/layout-list',
    ];
    foreach ($pages as $page) {
      $this->drupalGet($page);
      $this->assertSession()->statusCodeEquals(200);
    }
    $this->drupalLogout();
  }

  /**
   * Tests the config form.
   */
  public function testBlockListConfigForm() {
    $assert = $this->assertSession();
    // Login.
    $this->drupalLogin($this->user);

    // Access config page.
    $this->drupalGet('admin/config/block_list_override/settings');
    $assert->statusCodeEquals(200);
    // Test the form elements exist and have default values.
    $config = $this->config('block_list_override.settings');

    foreach (self::$replace as $field => $opt) {
      $assert->fieldValueEquals($field, $config->get('block_list_override.settings.' . $field));
    }
    // Test form submission.
    $edit = [];
    foreach (self::$replace as $field => $opt) {
      $edit[$field] = $opt['rule'];
    }
    $edit['system_negate'] = 0;
    $edit['layout_negate'] = 0;
    $this->submitForm($edit, 'Save configuration');
    $assert->pageTextContains('The configuration options have been saved.');
    // Test the new values are there.
    $this->drupalGet('admin/config/block_list_override/settings');
    $assert->statusCodeEquals(200);
    foreach (self::$replace as $field => $opt) {
      $string = strtr('//*[@name=":field_name"]', [':field_name' => $field]);
      $elements = $this->xpath($string);
      $value = count($elements) ? $elements[0]->getValue() : NULL;
      $this->assertEquals($value, $opt['rule']);
    }

    // Test block list results.
    $pages = [
      'system' => 'admin/config/block_list_override/system-list',
      'layout' => 'admin/config/block_list_override/layout-list',
    ];
    foreach ($pages as $type => $page) {
      $this->drupalGet($page);
      //sleep(30);
      $assert->statusCodeEquals(200);
      foreach (self::$replace as $field => $opt) {
        switch ($field) {
          case $type . '_match':
          case $type . '_prefix':
          case $type . '_regex':
            $assert->pageTextNotContains($opt['hides']);
            break;

        }
      }
    }
    $this->drupalLogout();
  }

  /**
   * Tests the negated system config form.
   *
   * We have to test system and layout separately since the system settings
   * will affect the layout blocks.
   */
  public function testBlockListConfigFormNegatedSystem() {
    $assert = $this->assertSession();
    $type = 'system';

    // Login.
    $this->drupalLogin($this->user);

    // Access config page.
    $this->drupalGet('admin/config/block_list_override/settings');
    $assert->statusCodeEquals(200);
    // Test the form elements exist and have default values.
    $config = $this->config('block_list_override.settings');

    foreach (self::$replace as $field => $opt) {
      $assert->fieldValueEquals($field, $config->get('block_list_override.settings.' . $field));
    }
    // Test form submission.
    $edit = [];
    foreach (self::$replace as $field => $opt) {
      if (strpos($field, $type) !== NULL) {
        $edit[$field] = $opt['rule'];
      }
      else {
        $edit[$field] = '';
      }
    }
    $edit['system_negate'] = 1;
    $this->submitForm($edit, 'Save configuration');
    $assert->pageTextContains('The configuration options have been saved.');

    // Test block list results.
    $page = 'admin/config/block_list_override/' . $type . '-list';
    $this->drupalGet($page);
    //sleep(30);
    $assert->statusCodeEquals(200);
    foreach (self::$replace as $field => $opt) {
      switch ($field) {
        case $type . '_match':
        case $type . '_prefix':
        case $type . '_regex':
          $assert->pageTextContains($opt['hides']);
          break;

      }
    }
    $this->drupalLogout();
  }

  /**
   * Tests the negated layout config form.
   *
   * We have to test system and layout separately since the system settings
   * will affect the layout blocks.
   */
  public function testBlockListConfigFormNegatedLayout() {
    $assert = $this->assertSession();
    $type = 'system';

    // Login.
    $this->drupalLogin($this->user);

    // Access config page.
    $this->drupalGet('admin/config/block_list_override/settings');
    $assert->statusCodeEquals(200);
    // Test the form elements exist and have default values.
    $config = $this->config('block_list_override.settings');

    foreach (self::$replace as $field => $opt) {
      $assert->fieldValueEquals($field, $config->get('block_list_override.settings.' . $field));
    }
    // Test form submission.
    $edit = [];
    foreach (self::$replace as $field => $opt) {
      if (strpos($field, $type) !== NULL) {
        $edit[$field] = $opt['rule'];
      }
      else {
        $edit[$field] = '';
      }
    }
    $edit['system_negate'] = 1;
    $this->submitForm($edit, 'Save configuration');
    $assert->pageTextContains('The configuration options have been saved.');

    // Test block list results.
    $page = 'admin/config/block_list_override/' . $type . '-list';
    $this->drupalGet($page);
    //sleep(30);
    $assert->statusCodeEquals(200);
    foreach (self::$replace as $field => $opt) {
      switch ($field) {
        case $type . '_match':
        case $type . '_prefix':
        case $type . '_regex':
          $assert->pageTextContains($opt['hides']);
          break;

      }
    }
    $this->drupalLogout();
  }

}
