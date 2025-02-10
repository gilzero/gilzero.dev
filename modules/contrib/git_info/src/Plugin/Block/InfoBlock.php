<?php

namespace Drupal\git_info\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\git_info\GitInfo;
use Drupal\git_info\GitInfoInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'InfoBlock' block.
 *
 * @Block(
 *  id = "info_block",
 *  admin_label = @Translation("Info block"),
 * )
 */
class InfoBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Git info service.
   *
   * @var \Drupal\git_info\GitInfo
   */
  protected $gitInfo;

  /**
   * Constructs an InfoBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\git_info\GitInfo $git_info
   *   Helpers to display info.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GitInfo $git_info) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->gitInfo = $git_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('git_info.git_info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $hash = $this->gitInfo->getApplicationVersionString();
    $build['info_block']['#markup'] = $hash;

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'view git info');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return [
      GitInfoInterface::CACHE_TAG,
    ];
  }

}
