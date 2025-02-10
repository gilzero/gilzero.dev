<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless_turbo\FunctionalJavascript;

use Drupal\Tests\refreshless\FunctionalJavascript\RefreshlessWebDriverTestBase;
use Drupal\Tests\refreshless_turbo\FunctionalJavascript\TurboAssertTrait;

/**
 * Base class for RefreshLess Turbo functional JavaScript tests.
 */
abstract class TurboWebDriverTestBase extends RefreshlessWebDriverTestBase {

  use TurboAssertTrait;

}
