<?php

namespace Drupal\Tests\git_info\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the basic functionality of the module.
 *
 * @group git_info
 */
class KernelIntegrationTest extends KernelTestBase {

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
    self::assertNotEmpty($build["info_block"]["#markup"]);
    // v.dev. would be the output if git did not find a git repo. Which sounds
    // like kind of a bug, but this repo is not the place for it. So we do a
    // check for that, and it will still pass if the library ever fixes that
    // "bug". It's basically the result of this sprintf:
    // sprintf('v.%s.%s%s', $this->getVersion(), $this->getShortHash(),
    // $date_string).
    self::assertNotEquals($build["info_block"]["#markup"], 'v.dev.');
  }

}
