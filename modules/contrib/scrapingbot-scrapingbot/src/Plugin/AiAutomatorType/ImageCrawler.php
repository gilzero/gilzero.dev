<?php

namespace Drupal\scrapingbot\Plugin\AiAutomatorType;

use Drupal\ai_automators\Attribute\AiAutomatorType;
use Drupal\ai_automators\PluginBaseClasses\ExternalBase;
use Drupal\ai_automators\PluginInterfaces\AiAutomatorTypeInterface;
use Drupal\scrapingbot\ScrapingBot;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Utility\Token;
use Drupal\file\FileRepositoryInterface;
use ivan_boring\Readability\Configuration;
use ivan_boring\Readability\Readability;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The rules for a image field.
 */
#[AiAutomatorType(
  id: 'scrapingbot_image',
  label: new TranslatableMarkup('ScrapingBot: Image Crawler'),
  field_rule: 'image',
  target: 'file',
)]
class ImageCrawler extends ExternalBase implements AiAutomatorTypeInterface, ContainerFactoryPluginInterface {

  /**
   * ScrapingBot API Caller.
   */
  private ScrapingBot $scrapingBot;

  /**
   * The entity type manager.
   */
  public EntityTypeManagerInterface $entityManager;

  /**
   * The current user.
   */
  public AccountProxyInterface $currentUser;

  /**
   * The File System interface.
   */
  public FileSystemInterface $fileSystem;

  /**
   * The File Repo.
   */
  public FileRepositoryInterface $fileRepo;

  /**
   * The token system to replace and generate paths.
   */
  public Token $token;

  /**
   * Construct an image field.
   *
   * @param array $configuration
   *   Inherited configuration.
   * @param string $plugin_id
   *   Inherited plugin id.
   * @param mixed $plugin_definition
   *   Inherited plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityManager
   *   The entity type manager.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The File system interface.
   * @param \Drupal\Core\Utility\Token $token
   *   The token system.
   * @param \Drupal\file\FileRepositoryInterface $fileRepo
   *   The File repo.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\scrapingbot\ScrapingBot $scrapingBot
   *   The ScrapingBot requester.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entityManager,
    FileSystemInterface $fileSystem,
    Token $token,
    FileRepositoryInterface $fileRepo,
    AccountProxyInterface $currentUser,
    ScrapingBot $scrapingBot,
  ) {
    $this->entityManager = $entityManager;
    $this->fileSystem = $fileSystem;
    $this->fileRepo = $fileRepo;
    $this->currentUser = $currentUser;
    $this->token = $token;
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
      $container->get('entity_type.manager'),
      $container->get('file_system'),
      $container->get('token'),
      $container->get('file.repository'),
      $container->get('current_user'),
      $container->get('scrapingbot.api'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public $title = 'ScrapingBot: Image Crawler';

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
    $options['useChrome'] = $automatorConfig['use_chrome'];
    $options['waitForNetworkRequests'] = $automatorConfig['wait_for_network'];
    $options['proxyCountry'] = $automatorConfig['proxy_country'];
    $options['premiumProxy'] = $automatorConfig['use_premium_proxy'];
    $rawHtml = $this->scrapingBot->scrapeRaw($entity->{$automatorConfig['base_field']}->uri, $options);
    $readability = new Readability(new Configuration());
    $done = $readability->parse($rawHtml);
    return ['value' => $done ? $readability->getImage() : ''];
  }

  /**
   * {@inheritDoc}
   */
  public function verifyValue(ContentEntityInterface $entity, $value, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Should be an url.
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
      return FALSE;
    }
    // Otherwise it is ok.
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function storeValues(ContentEntityInterface $entity, array $values, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Transform string to boolean.
    $fileEntities = [];

    // Successful counter, to only download as many as max.
    $successFul = 0;
    foreach ($values as $value) {
      // Save filename.
      $fileName = explode('?', basename($value))[0];
      // If no ending exists.
      if (!strstr($fileName, '.')) {
        $fileName .= '.jpg';
      }
      $fileHelper = $this->getFileHelper();
      $filePath = $fileHelper->createFilePathFromFieldConfig($fileName, $fieldDefinition, $entity);
      // Create file entity from string.
      $image = $fileHelper->generateImageMetaDataFromBinary(file_get_contents($value), $filePath);

      // If we can save, we attach it.
      if ($image) {
        // Add to the entities list.
        $fileEntities[] = $image;

        $successFul++;
        // If we have enough images, give up.
        if ($successFul == $fieldDefinition->getFieldStorageDefinition()->getCardinality()) {
          break;
        }
      }
    }

    // Then set the value.
    $entity->set($fieldDefinition->getName(), $fileEntities);
  }

}
