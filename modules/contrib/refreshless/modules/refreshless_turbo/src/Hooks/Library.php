<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Hooks;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\hux\Attribute\Alter;
use function array_search;
use function array_splice;
use function in_array;

/**
 * Library hook implementations.
 */
class Library {

  /**
   * Hook constructor; saves dependencies.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The Drupal module handler.
   */
  public function __construct(
    protected readonly ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * Replace JavaScript in a provided library with our JavaScript override.
   *
   * @param array &$library
   *   The library entry to alter.
   *
   * @param string $originalJs
   *   The original JavaScript file path, as specified in *.libraries.yml.
   *
   * @param string $ourJs
   *   The path to our JavaScript override, relative to our js directory.
   */
  protected function replaceJs(
    array &$library, string $originalJs, string $ourJs,
  ): void {

    if (
      // Don't do anything if the library doesn't have the expected file. This
      // is both to avoid errors and to ensure we don't alter libraries that
      // have been altered by another extension because we could end up
      // conflicting with their changes.
      !isset($library['js'][$originalJs])
    ) {
      return;
    }

    // Copy the existing library definition from the original's to ours.
    $library['js'][
      '/' . $this->moduleHandler->getModule('refreshless_turbo')->getPath() .
      '/js/' . $ourJs
    ] = $library['js'][$originalJs];

    // Remove the original JavaScript entry.
    unset($library['js'][$originalJs]);

    // Remove any existing version as this is now no longer accurate; we don't
    // want a core or module version to be used as the cache-busting query
    // string as this can now have changes regardless of whether core or the
    // extension has been updated.
    unset($library['version']);

  }

  #[Alter('library_info')]
  /**
   * Alter library definitions to add our Ajax compatibility to core's Ajax.
   *
   * This adds our library as a dependency to 'core/drupal.ajax' so that our
   * library is always attached when core's is.
   *
   * @see \hook_library_info_alter()
   */
  public function alterCoreAjax(
    array &$libraries, string $extension,
  ): void {

    if ($extension !== 'core') {
      return;
    }

    $libraries['drupal.ajax']['dependencies'][] = 'refreshless_turbo/ajax';
    $libraries['drupal.ajax']['dependencies'][] = 'refreshless_turbo/ajax_load_css';

  }


  #[Alter('library_info')]
  /**
   * Alter library definitions to add our dialog compatibility to core's.
   *
   * @see hook_library_info_alter
   */
  public function alterCoreDialog(
    array &$libraries, string $extension,
  ): void {

    if ($extension !== 'core') {
      return;
    }

    $libraries['drupal.dialog']['dependencies'][] = 'refreshless_turbo/dialog';

  }

  #[Alter('library_info')]
  /**
   * Replaces core/misc/announce.js with our own.
   *
   * @see \hook_library_info_alter()
   *
   * @see https://www.drupal.org/project/refreshless/issues/3399243
   */
  public function replaceCoreAnnounce(
    array &$libraries, string $extension,
  ): void {

    if ($extension !== 'core') {
      return;
    }

    $this->replaceJs(
      $libraries['drupal.announce'], 'misc/announce.js',
      'overrides/announce.js',
    );

  }

  #[Alter('library_info')]
  /**
   * Replaces core/misc/batch.js with our own.
   *
   * @see \hook_library_info_alter()
   */
  public function alterCoreBatch(
    array &$libraries, string $extension,
  ): void {

    if ($extension !== 'core') {
      return;
    }

    $this->replaceJs(
      $libraries['drupal.batch'], 'misc/batch.js', 'overrides/batch.js',
    );

  }

  #[Alter('library_info')]
  /**
   * Replace Drupal core's 'misc/drupalSettingsLoader.js' with our own.
   *
   * @see \hook_library_info_alter()
   */
  public function alterCoreDrupalSettingsLoader(
    array &$libraries, string $extension,
  ): void {

    if ($extension !== 'core') {
      return;
    }

    $this->replaceJs(
      $libraries['drupalSettings'], 'misc/drupalSettingsLoader.js',
      'overrides/drupal_settings_loader.js',
    );

  }

  #[Alter('library_info')]
  /**
   * Replace dependencies on refreshless_turbo/js-cookie with core's if found.
   *
   * Note that this assumes this hook will always be called for core first, so
   * could cause issues if that ever changes.
   *
   * @see hook_library_info_alter
   *
   * @see https://www.drupal.org/node/3322720
   *   core/js-cookie will be removed in Drupal core 11.0.0.
   *
   * @todo Remove this when we require minimum of drupal/core:^11.0
   */
  public function alterJsCookie(
    array &$libraries, string $extension,
  ): void {

    if ($extension !== 'core' && $extension !== 'refreshless_turbo') {
      return;
    }

    static $coreHasJsCookie = false;

    if ($extension === 'core' && isset($libraries['js-cookie'])) {

      $coreHasJsCookie = true;

      return;

    }

    if ($extension !== 'refreshless_turbo' || $coreHasJsCookie === false) {
      return;
    }

    foreach ($libraries as $libraryName => &$library) {

      if (
        empty($library['dependencies']) ||
        !in_array('refreshless_turbo/js-cookie', $library['dependencies'])
      ) {
        continue;
      }

      array_splice(
        $library['dependencies'],
        array_search('refreshless_turbo/js-cookie', $library['dependencies']),
        1,
        ['core/js-cookie'],
      );

    }

  }

}
