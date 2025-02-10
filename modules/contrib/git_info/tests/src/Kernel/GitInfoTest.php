<?php

namespace Drupal\Tests\git_info\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\block\Entity\Block;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Tests the most basic functionality of the module.
 *
 * @group git_info
 */
class GitInfoTest extends KernelTestBase implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'git_info',
    'block',
    // These modules are used by the block module to get regions and roles.
    'system',
    'user',
  ];

  /**
   * Info block.
   *
   * @var \Drupal\git_info\Plugin\Block\InfoBlock
   */
  protected $block;

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();
    /** @var \Drupal\Core\Block\BlockManagerInterface $block_mng */
    $block_mng = $this->container->get('plugin.manager.block');
    $this->block = $block_mng->createInstance('info_block');
    $this->installEntitySchema('block');
  }

  /**
   * Test that info works like we think.
   */
  public function testInfoFunctionality() {
    $build = $this->block->build();
    self::assertEquals($build["info_block"]["#markup"], 'v.13.3.7.133ee7 (2017-03-16 17:03:15)');
  }

  /**
   * Test that cache is used until it is not.
   */
  public function testCache() {
    // Start by informing the cache of the current state.
    git_info_cron();
    $block = Block::create([
      'id' => 'test_info_block',
      'plugin' => 'info_block',
      'settings' => [],
    ]);
    $block->save();
    /** @var \Drupal\block\BlockViewBuilder $view_builder */
    $view_builder = $this->container->get('entity_type.manager')->getViewBuilder('block');
    $build = $view_builder->view($block);
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->container->get('renderer');
    $renderer->renderRoot($build);
    $state = $this->container->get('state');
    self::assertEquals($state->get(TestService::COUNTER_KEY), 1);
    $build = $view_builder->view($block);
    $renderer->renderRoot($build);
    self::assertEquals($state->get(TestService::COUNTER_KEY), 1);
    // Run cron. The count should not increase, since our short hash is still
    // the same.
    git_info_cron();
    $build = $view_builder->view($block);
    $renderer->renderRoot($build);
    self::assertEquals($state->get(TestService::COUNTER_KEY), 1);
    // Now let's inform our test service that another git version string should
    // be returned.
    $state->set(TestService::BYPASS_CACHE_KEY, 1);
    // Run cron. Cron should now see that the hash has changed, and clear the
    // cache for the version string of the block.
    git_info_cron();
    // This should now be uncached. Which in turn should mean the counter should
    // inrease by one.
    $build = $view_builder->view($block);
    $renderer->renderRoot($build);
    self::assertEquals($state->get(TestService::COUNTER_KEY), 2);
  }

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $def = $container->getDefinition('git_info.git_info');
    $def->setClass(TestService::class);
    $def->addArgument('git');
    $def->addArgument(new Reference('state'));
    $container->setDefinition('git_info.git_info', $def);
  }

}
