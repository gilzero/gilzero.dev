<?php

declare(strict_types=1);

namespace Drupal\Tests\refreshless\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\refreshless\FunctionalJavascript\RefreshlessHtmlDebugTrait;

/**
 * Base class for RefreshLess functional JavaScript tests.
 */
abstract class RefreshlessWebDriverTestBase extends WebDriverTestBase {

  use RefreshlessHtmlDebugTrait;

}
