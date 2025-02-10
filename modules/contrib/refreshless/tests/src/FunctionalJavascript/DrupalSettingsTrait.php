<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless\FunctionalJavascript;

/**
 * Trait for interacting with and asserting values of drupalSettings.
 */
trait DrupalSettingsTrait {

  /**
   * Get a drupalSettings value.
   *
   * @param string $propertyPath
   *   The JavaScript property path. Note that 'drupalSettings.' is prepended
   *   automatically and so should not be included. For example, to get the
   *   value of 'drupalSettings.path.currentPath', you would pass
   *   'path.currentPath' as this parameter.
   *
   * @return mixed
   *
   * @see \Drupal\FunctionalJavascriptTests\WebDriverTestBase::getDrupalSettings()
   *   We take a slightly different approach from Drupal core in that we fetch
   *   nested keys in JavaScript using JavaScript syntax. Use the core method
   *   if you need to get the whole drupalSettings and/or are interacting with
   *   'drupalSettings.ajaxPageState.libraries'.
   */
  protected function getDrupalSettingsValue(string $propertyPath): mixed {

    return $this->getSession()->evaluateScript(<<<JS
      return drupalSettings.{$propertyPath};
    JS);

  }

  /**
   * Determine if a drupalSettings value exists.
   *
   * @param string $propertyPath
   *   The JavaScript property path. Note that 'drupalSettings.' is prepended
   *   automatically and so should not be included. For example, to get the
   *   value of 'drupalSettings.path.currentPath', you would pass
   *   'path.currentPath' as this parameter.
   *
   * @return bool
   *   True if it exists, false otherwise.
   */
  protected function drupalSettingsValueExists(string $propertyPath): bool {

    return $this->getSession()->evaluateScript(<<<JS

      return (() => {

        let result;

        // If the property is nested - i.e. contains a '.' - this will throw an
        // exception which would fail the test before we could check the value.
        try {
          result = typeof drupalSettings.{$propertyPath} !== 'undefined';
        } catch (exception) {
          result = false;
        }

        return result;

      })();

    JS);

  }

  /**
   * Assert that a drupalSettings property equals an expected value.
   *
   * @param string $propertyPath
   *   The JavaScript property path. Note that 'drupalSettings.' is prepended
   *   automatically and so should not be included. For example, to get the
   *   value of 'drupalSettings.path.currentPath', you would pass
   *   'path.currentPath' as this parameter.
   *
   * @param mixed $expectedValue
   *   The expected value of the property.
   */
  protected function assertDrupalSettingsEqualsValue(
    string $propertyPath, mixed $expectedValue,
  ): void {

    $actualValue = $this->getDrupalSettingsValue($propertyPath);

    $this->assertEquals(
      $expectedValue, $actualValue,
      "drupalSettings.$propertyPath was expected to equal \"$expectedValue\" but got \"$actualValue\"!",
    );

  }

  /**
   * Assert that a drupalSettings property does not equal an expected value.
   *
   * @param string $propertyPath
   *   The JavaScript property path. Note that 'drupalSettings.' is prepended
   *   automatically and so should not be included. For example, to get the
   *   value of 'drupalSettings.path.currentPath', you would pass
   *   'path.currentPath' as this parameter.
   *
   * @param mixed $expectedValue
   *   The expected value of the property.
   */
  protected function assertDrupalSettingsNotEqualsValue(
    string $propertyPath, mixed $expectedValue,
  ): void {

    $actualValue = $this->getDrupalSettingsValue($propertyPath);

    $this->assertNotEquals(
      $expectedValue, $actualValue,
      "drupalSettings.$propertyPath should not equal \"$expectedValue\"!",
    );

  }

  /**
   * Assert that a drupalSettings property exists.
   *
   * @param string $propertyPath
   *   The JavaScript property path. Note that 'drupalSettings.' is prepended
   *   automatically and so should not be included. For example, to get the
   *   value of 'drupalSettings.path.currentPath', you would pass
   *   'path.currentPath' as this parameter.
   */
  protected function assertDrupalSettingsValueExists(
    string $propertyPath,
  ): void {

    $this->assertTrue(
      $this->drupalSettingsValueExists($propertyPath),
      "drupalSettings.$propertyPath should exist but does not!",
    );

  }

  /**
   * Assert that a drupalSettings property does not exist.
   *
   * @param string $propertyPath
   *   The JavaScript property path. Note that 'drupalSettings.' is prepended
   *   automatically and so should not be included. For example, to get the
   *   value of 'drupalSettings.path.currentPath', you would pass
   *   'path.currentPath' as this parameter.
   */
  protected function assertDrupalSettingsValueDoesNotExist(
    string $propertyPath,
  ): void {

    $this->assertFalse(
      $this->drupalSettingsValueExists($propertyPath),
      "drupalSettings.$propertyPath should not exist!",
    );

  }

}
