<?php

namespace Drupal\scrapingbot\Plugin\AiAutomatorType;

use Drupal\ai_automators\Attribute\AiAutomatorType;
use Drupal\ai_automators\PluginBaseClasses\ExternalBase;
use Drupal\ai_automators\PluginInterfaces\AiAutomatorTypeInterface;
use Drupal\scrapingbot\ScrapingBot;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use ivan_boring\Readability\Configuration;
use ivan_boring\Readability\Readability;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The rules for a author field.
 */
#[AiAutomatorType(
  id: 'scrapingbot_author_string',
  label: new TranslatableMarkup('ScrapingBot: Author Crawler'),
  field_rule: 'string',
  target: '',
)]
class AuthorCrawler extends ExternalBase implements AiAutomatorTypeInterface, ContainerFactoryPluginInterface {

  /**
   * ScrapingBot API Caller.
   */
  private ScrapingBot $scrapingBot;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ScrapingBot $scrapingBot) {
    $this->scrapingBot = $scrapingBot;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('scrapingbot.api')
    );
  }

  /**
   * {@inheritDoc}
   */
  public $title = 'ScrapingBot: Author Crawler';

  /**
   * {@inheritDoc}
   */
  public function needsPrompt() {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function advancedMode() {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function placeholderText() {
    return "";
  }

  /**
   * {@inheritDoc}
   */
  public function allowedInputs() {
    return ['link'];
  }

  /**
   * {@inheritDoc}
   */
  public function extraAdvancedFormFields(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition, FormStateInterface $formState, array $defaultValues = []) {
    $form['automator_use_chrome'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Chrome'),
      '#description' => $this->t("Use Chrome when scraping"),
      '#default_value' => $defaultValues['automator_use_chrome'] ?? TRUE,
      '#weight' => -15,
    ];

    $form['automator_wait_for_network'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Wait for network'),
      '#description' => $this->t("Check if you want to wait for most ajax requests to finish until returning the Html content. This can slowdown or fail your scraping if some requests are never ending only use if really needed to get some price loaded asynchronously for example."),
      '#default_value' => $defaultValues['automator_wait_for_network'] ?? FALSE,
      '#weight' => -14,
    ];

    $form['automator_proxy_country'] = [
      '#type' => 'select',
      '#options' => ScrapingBot::$proxyCountries,
      '#title' => $this->t('Proxy Country'),
      '#description' => $this->t("Where should the scraping take place."),
      '#default_value' => $defaultValues['automator_proxy_country'] ?? 'US',
      '#weight' => -13,
    ];

    $form['automator_use_premium_proxy'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Premium Proxy'),
      '#description' => $this->t("Use Premium Proxy when scraping. This is VERY expensive."),
      '#default_value' => $defaultValues['automator_use_premium_proxy'] ?? FALSE,
      '#weight' => -12,
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function generate(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Set options.
    $options['useChrome'] = $automatorConfig['use_chrome'];
    $options['waitForNetworkRequests'] = $automatorConfig['wait_for_network'];
    $options['proxyCountry'] = $automatorConfig['proxy_country'];
    $options['premiumProxy'] = $automatorConfig['use_premium_proxy'];
    // Scrape.
    $rawHtml = $this->scrapingBot->scrapeRaw($entity->{$automatorConfig['base_field']}->uri, $options);
    $readability = new Readability(new Configuration());
    $done = $readability->parse($rawHtml);
    return ['value' => $done ? $readability->getAuthor() : ''];

  }

  /**
   * {@inheritDoc}
   */
  public function verifyValue(ContentEntityInterface $entity, $value, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Should be a string.
    if (!is_string($value)) {
      return FALSE;
    }
    // Otherwise it is ok.
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function storeValues(ContentEntityInterface $entity, array $values, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Then set the value.
    $entity->set($fieldDefinition->getName(), $values);
  }

}
