<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\Tests\refreshless_turbo\FunctionalJavascript\TurboWebDriverTestBase;

/**
 * Behaviour tests.
 *
 * @group refreshless
 *
 * @group refreshless_turbo
 */
class BehaviourTest extends TurboWebDriverTestBase {

  /**
   * Behaviour counter attribute name.
   */
  protected const COUNTER_ATTR =
    'data-refreshless-turbo-behaviour-test-counter';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block', 'refreshless_turbo_behaviour_test', 'refreshless_turbo_test_tools',
    'system',
  ];

  /**
   * Assert the <html> element has the behaviour counter with a given value.
   *
   * @param int $count
   */
  protected function assertBehaviourCounterEquals(int $count): void {

    $html = $this->assertSession()->elementAttributeExists(
      'css', 'html', self::COUNTER_ATTR,
    );

    $this->assertEquals($count, (int) $html->getAttribute(self::COUNTER_ATTR));

  }

  /**
   * Test basic behaviour attach and detach.
   */
  public function testBasicAttachDetach(): void {

    $loginUrl = Url::fromRoute('user.login');

    $registerUrl = Url::fromRoute('user.register');

    $resetPasswordUrl = Url::fromRoute('user.pass');

    $this->drupalPlaceBlock('local_tasks_block', [
      'region' => 'content', 'id' => 'local-tasks-block',
    ]);

    $this->drupalGet($loginUrl);

    $this->assertSession()->assertRefreshlessIsPresent();

    $this->assertSession()->startRefreshlessPersist();

    $this->assertBehaviourCounterEquals(1);

    $this->click('[data-drupal-link-system-path="' .
      $registerUrl->getInternalPath() .
    '"]');

    $this->assertWaitOnRefreshlessRequestAndPageReady();

    $this->assertBehaviourCounterEquals(2);

    $this->click('[data-drupal-link-system-path="' .
      $resetPasswordUrl->getInternalPath() .
    '"]');

    $this->assertWaitOnRefreshlessRequestAndPageReady();

    $this->assertBehaviourCounterEquals(3);

  }

}
