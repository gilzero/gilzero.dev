<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\Kernel;

use Drupal\Core\Asset\AttachedAssets;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that JavaScript libraries are altered as needed for Turbo.
 *
 * Note that at the time of writing with Hux 1.2, discovery of hook classes
 * seems to only work if they're defined in self::$modules but for whatever
 * reason they don't get discovered as hooks if the module containing the hook
 * classes is enabled after that point; KernelTestBase::enableModules() does
 * not cause them to be discovered, nor does installing the modules via the
 * 'module_installer' service; forcing a container rebuild doesn't work, nor
 * does deleting various caches such as 'cache.bootstrap'. Because of this, we
 * can't do a before and after comparison in the test, and ultimately what
 * matters is that no JavaScript is left in the footer but is instead found in
 * the header.
 *
 * @group refreshless
 *
 * @group refreshless_turbo
 *
 * @coversDefaultClass \Drupal\refreshless_turbo\Hooks\Javascript
 *
 * @see \Drupal\KernelTests\Core\Asset\AttachedAssetsTest::testAddFiles()
 *
 * @see \Drupal\KernelTests\Core\Asset\AttachedAssetsTest::testAlter()
 *
 * @see https://www.drupal.org/project/hux/issues/3401023
 *   Hux issue opened as part of writing this test with the above problem.
 *
 * @see \Drupal\Core\Test\FunctionalTestSetupTrait::rebuildAll()
 *   Functional tests have access to this which can apparently be called after
 *   installing modules during a test, but it's unclear what the kernel test
 *   equivalent is.
 */
class JavascriptAlterTest extends KernelTestBase {

  /**
   * The Drupal asset resolver service.
   *
   * @var \Drupal\Core\Asset\AssetResolverInterface
   */
  protected readonly AssetResolverInterface $assetResolver;

  /**
   * The Drupal language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected readonly LanguageManagerInterface $languageManager;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'hux', 'refreshless', 'refreshless_turbo',
    'refreshless_turbo_library_alter_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->assetResolver    = $this->container->get('asset.resolver');
    $this->languageManager  = $this->container->get('language_manager');

  }

  /**
   * Test that our alter hook forces our test JavaScript into header + defer.
   *
   * @covers ::alter
   */
  public function testAlterBasic(): void {

    $build = ['#attached' => ['library' => [
      'refreshless_turbo_library_alter_test/alter_basic',
    ]]];

    /** @var \Drupal\Core\Asset\AttachedAssetsInterface Attached assets collection. */
    $assets = AttachedAssets::createFromRenderArray($build);

    /** @var array[] Array containing resolved header and footer JavaScript. */
    $javascript = $this->assetResolver->getJsAssets(
      $assets, false, $this->languageManager->getCurrentLanguage(),
    );

    // Header should not be empty.
    $this->assertNotEmpty($javascript[0]);

    $this->assertCount(1, $javascript[0]);

    // Footer should be completely empty.
    $this->assertEmpty($javascript[1]);

    $entry = \reset($javascript[0]);

    $this->assertArrayHasKey('attributes', $entry);

    $this->assertArrayHasKey('defer', $entry['attributes']);

    $this->assertTrue($entry['attributes']['defer']);

  }

  /**
   * Test that our alter hook leaves test JavaScript alone if already in header.
   *
   * @covers ::alter
   */
  public function testAlterAlreadyHeader(): void {

    $build = ['#attached' => ['library' => [
      'refreshless_turbo_library_alter_test/alter_already_header',
    ]]];

    /** @var \Drupal\Core\Asset\AttachedAssetsInterface Attached assets collection. */
    $assets = AttachedAssets::createFromRenderArray($build);

    /** @var array[] Array containing resolved header and footer JavaScript. */
    $javascript = $this->assetResolver->getJsAssets(
      $assets, false, $this->languageManager->getCurrentLanguage(),
    );

    // Header should not be empty.
    $this->assertNotEmpty($javascript[0]);

    $this->assertCount(1, $javascript[0]);

    // Footer should be completely empty.
    $this->assertEmpty($javascript[1]);

    $entry = \reset($javascript[0]);

    // The 'attributes' key should always exist but we didn't set the 'defer'
    // attribute in the library definition so we expect 'defer' to not exist.
    $this->assertArrayNotHasKey('defer', $entry['attributes']);

  }

  /**
   * Test that our alter hook forces core JavaScript into the header.
   *
   * @covers ::alter
   */
  public function testAlterCore(): void {

    $build = ['#attached' => ['library' => [
      'core/drupal.ajax',
      'core/drupal.message',
      'core/drupal.states',
    ]]];

    /** @var \Drupal\Core\Asset\AttachedAssetsInterface Attached assets collection. */
    $assets = AttachedAssets::createFromRenderArray($build);

    /** @var array[] Array containing resolved header and footer JavaScript. */
    $javascript = $this->assetResolver->getJsAssets(
      $assets, false, $this->languageManager->getCurrentLanguage(),
    );

    // Header should not be empty.
    $this->assertNotEmpty($javascript[0]);

    $this->assertArrayHasKey('core/misc/ajax.js',     $javascript[0]);
    $this->assertArrayHasKey('core/misc/message.js',  $javascript[0]);
    $this->assertArrayHasKey('core/misc/states.js',   $javascript[0]);

  }

}
