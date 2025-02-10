<?php

namespace Drupal\Tests\git_info\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Drupal\git_info\GitInfo;
use Drupal\git_info\Plugin\Block\InfoBlock;

/**
 * Test tag name generation.
 *
 * @group git_info
 */
class BlockInfoTest extends UnitTestCase {

  /**
   * Test what is output in the block.
   */
  public function testBlockOutput() {
    $container = new ContainerBuilder();
    $git_info = new GitInfo();
    $container->set('git_info.git_info', $git_info);
    $block = InfoBlock::create($container, [], 'git_info', [
      'provider' => 'git_info',
    ]);
    $this->assertInstanceOf(InfoBlock::class, $block);
  }

}
