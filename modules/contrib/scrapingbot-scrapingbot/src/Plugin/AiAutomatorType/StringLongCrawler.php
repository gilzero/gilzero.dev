<?php

namespace Drupal\scrapingbot\Plugin\AiAutomatorType;

use Drupal\ai_automators\Attribute\AiAutomatorType;
use Drupal\ai_automators\PluginInterfaces\AiAutomatorTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The rules for a string_long field.
 */
#[AiAutomatorType(
  id: 'scrapingbot_string_long',
  label: new TranslatableMarkup('ScrapingBot: Crawler'),
  field_rule: 'string_long',
  target: '',
)]
class StringLongCrawler extends LongCrawlerRule implements AiAutomatorTypeInterface {

}
