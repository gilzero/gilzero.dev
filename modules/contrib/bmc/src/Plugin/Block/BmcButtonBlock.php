<?php

namespace Drupal\bmc\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Buy Me a Coffee block.
 *
 * @Block(
 * id = "by_mee_coffee_block",
 * admin_label = @Translation("Buy Me a Coffee"),
 * )
 */
class BmcButtonBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor a BmcButtonBlock object.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $bmc_settings = $this->configFactory->get('bmc.settings')->get('bmc_settings');

    $build['custom_script'] = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#attributes' => [
        'type' => 'text/javascript',
        'src' => 'https://cdnjs.buymeacoffee.com/1.0.0/button.prod.min.js',
        'data-name' => 'bmc-button',
        'data-slug' => $bmc_settings['username'] ?? '',
        'data-color' => $bmc_settings['background_color'] ?? '#FFDD00',
        'data-emoji' => '',
        'data-font' => $bmc_settings['font_family'] ?? 'Cookie',
        'data-text' => $bmc_settings['custom_text'] ?? 'Buy Me a Coffee',
        'data-outline-color' => '#000000',
        'data-font-color' => '#000000',
        'data-coffee-color' => $bmc_settings['coffee_color'] ?? '#FFFFFF',
      ],
    ];

    return $build;
  }

}
