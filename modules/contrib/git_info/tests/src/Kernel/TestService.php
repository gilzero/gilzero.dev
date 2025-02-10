<?php

namespace Drupal\Tests\git_info\Kernel;

use Drupal\Core\State\StateInterface;
use Drupal\git_info\GitInfo;

/**
 * Service we can use in tests.
 */
class TestService extends GitInfo {

  const COUNTER_KEY = 'git_info_test.counter';
  const BYPASS_CACHE_KEY = 'git_info_test.bypass_cache';

  /**
   * State.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * TestService constructor.
   */
  public function __construct($git_command = 'git', ?StateInterface $state = NULL) {
    parent::__construct($git_command);
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function getApplicationVersionString() {
    $count = $this->state->get(self::COUNTER_KEY, 0);
    $this->state->set(self::COUNTER_KEY, $count + 1);
    return 'v.13.3.7.133ee7 (2017-03-16 17:03:15)';
  }

  /**
   * {@inheritdoc}
   */
  public function getShortHash() {
    if ($this->state->get(self::BYPASS_CACHE_KEY, 0)) {
      return '133ee8';
    }
    return '133ee7';
  }

}
