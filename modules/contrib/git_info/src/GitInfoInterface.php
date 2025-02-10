<?php

namespace Drupal\git_info;

/**
 * Interface for git info.
 *
 * @todo It really does not seem like we are using this any more?
 *
 * @package Drupal\git_info
 */
interface GitInfoInterface {

  const STATE_KEY = 'git_info.state_key';

  const CACHE_TAG = 'git_info.cache_tag';

  /**
   * {@inheritdoc}
   */
  public function getShortHash();

  /**
   * {@inheritdoc}
   */
  public function getVersion();

  /**
   * {@inheritdoc}
   */
  public function getDate();

  /**
   * {@inheritdoc}
   */
  public function getApplicationVersionString();

}
