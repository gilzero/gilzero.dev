<?php

namespace Drupal\scrapingbot\Plugin\AiAutomatorType;

use Drupal\ai_automators\Attribute\AiAutomatorType;
use Drupal\ai_automators\PluginInterfaces\AiAutomatorTypeInterface;
use Drupal\scrapingbot\ScrapingBot;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The rules for a text_long field.
 */
#[AiAutomatorType(
  id: 'scrapingbot_text_long',
  label: new TranslatableMarkup('ScrapingBot: Crawler'),
  field_rule: 'text_long',
  target: '',
)]
class TextLongCrawler extends LongCrawlerRule implements AiAutomatorTypeInterface {

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Construct a boolean field.
   *
   * @param array $configuration
   *   Inherited configuration.
   * @param string $plugin_id
   *   Inherited plugin id.
   * @param mixed $plugin_definition
   *   Inherited plugin definition.
   * @param \Drupal\scrapingbot\ScrapingBot $scrapingBot
   *   The ScrapingBot requester.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ScrapingBot $scrapingBot, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $scrapingBot);
    $this->scrapingBot = $scrapingBot;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('scrapingbot.api'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function storeValues(ContentEntityInterface $entity, array $values, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Get text format.
    $textFormat = $this->getGeneralHelper()->calculateTextFormat($fieldDefinition);

    // Then set the value.
    $cleanedValues = [];
    foreach ($values as $value) {
      $cleanedValues[] = [
        'value' => $value,
        'format' => $textFormat,
      ];
    }
    $entity->set($fieldDefinition->getName(), $cleanedValues);
  }

}
