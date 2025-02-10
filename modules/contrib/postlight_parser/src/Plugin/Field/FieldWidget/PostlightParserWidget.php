<?php

namespace Drupal\postlight_parser\Plugin\Field\FieldWidget;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\field\FieldConfigInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the 'postlight_parser' field widget.
 *
 * @FieldWidget(
 *   id = "postlight_parser",
 *   label = @Translation("Url content parser"),
 *   field_types = {"link"},
 * )
 */
final class PostlightParserWidget extends LinkWidget {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Url parser service.
   *
   * @var \Drupal\postlight_parser\UrlParserServiceInterface
   */
  protected $urlParser;

  /**
   * Field list store.
   *
   * @var array
   */
  public array $fieldsList = [];

  /**
   * Constructs a FormatterBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param mixed $url_parser
   *   URL parser service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, $url_parser) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->urlParser = $url_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('postlight_parser.url_parser'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    $setting = [
      'title_field' => '',
      'content_field' => '',
      'image_field' => '',
      'excerpt_field' => '',
      'parser' => 'readability',
      'endpoint' => '',
      'save_image' => FALSE,
    ];
    return $setting + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $entity_type = $form["#entity_type"];
    $bundle = $form["#bundle"];
    // @phpstan-ignore-next-line
    $entityFieldManager = \Drupal::service('entity_field.manager');
    $fieldDefinitions = $entityFieldManager->getFieldDefinitions($entity_type, $bundle);
    $element['title_field'] = [
      '#title' => $this->t('Title field'),
      '#description' => $this->t('To store title'),
      '#default_value' => $this->getSetting('title_field'),
      '#type' => 'select',
      '#options' => $this->getConfigurableFields($fieldDefinitions, [
        'string',
        'string_long',
        'string_textfield',
      ]),
      '#empty_option' => $this->t('- None -'),
    ];
    $element['content_field'] = [
      '#title' => $this->t('Content field'),
      '#default_value' => $this->getSetting('content_field'),
      '#type' => 'select',
      '#options' => $this->getConfigurableFields($fieldDefinitions, [
        'text',
        'text_long',
        'text_with_summary',
        'text_textarea_with_summary',
      ]),
      '#empty_option' => $this->t('- None -'),
    ];
    $element['image_field'] = [
      '#title' => $this->t('Image field'),
      '#default_value' => $this->getSetting('image_field'),
      '#type' => 'select',
      '#options' => $this->getConfigurableFields($fieldDefinitions, 'image'),
      '#empty_option' => $this->t('- None -'),
    ];
    $element['excerpt_field'] = [
      '#title' => $this->t('Summary field'),
      '#default_value' => $this->getSetting('excerpt_field'),
      '#type' => 'select',
      '#options' => $this->getConfigurableFields($fieldDefinitions, [
        'text',
        'text_long',
        'text_with_summary',
        'text_textarea_with_summary',
      ]),
      '#empty_option' => $this->t('- None -'),
    ];
    $element['parser'] = [
      '#title' => $this->t('Parser library'),
      '#default_value' => $this->getSetting('parser'),
      '#type' => 'select',
      '#options' => [
        'readability' => $this->t('Readability php'),
        'postlight' => $this->t('Postlight parser'),
        'mercury' => $this->t('Mercury parser'),
        'api' => $this->t('Parser api'),
        'graby' => $this->t('Graby'),
      ],
      '#empty_option' => $this->t('- None -'),
    ];
    $element['endpoint'] = [
      '#title' => $this->t('Url endpoint api'),
      '#default_value' => $this->getSetting('endpoint'),
      '#descriptions' => $this->t('Url Postlight / Mercury parser'),
      '#type' => 'url',
      '#states' => [
        'visible' => [
          'select[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][parser]"]' => ['value' => 'api'],
        ],
      ],
    ];
    // Under development.
    $element['save_image'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Save image inline'),
      '#default_value' => $this->getSetting('save_image') ?? FALSE,
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    return [
      $this->t('Parser: @parser', ['@parser' => $this->getSetting('parser')]),
      $this->t('Endpoint: @url', ['@url' => $this->getSetting('url')]),
      $this->t('Title field: @title', ['@title' => $this->getSetting('title_field')]),
      $this->t('Content field: @content', ['@content' => $this->getSetting('content_field')]),
      $this->t('Image field: @image', ['@image' => $this->getSetting('image_field')]),
      $this->t('Summary field: @excerpt', ['@excerpt' => $this->getSetting('excerpt_field')]),
      $this->t('Endpoint: @url', ['@url' => $this->getSetting('endpoint')]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $baseId = $this->fieldDefinition->get('id');
    $id = $baseId . '-' . $delta;
    $parser = $this->getSetting('parser');
    $endpoint = $this->getSetting('endpoint');
    if (!empty($parser) && $parser == 'api' && !empty($endpoint)) {
      $url = $endpoint;
    }
    else {
      $url = Url::fromRoute('postlight.parser', ['parser' => $parser]);
    }
    $image_field = $this->getSetting('image_field');
    if (!empty($form[$image_field]["widget"][0]["#upload_location"])) {
      $imageFolder = $form[$image_field]["widget"][0]["#upload_location"];
      $element["uri"]["#attributes"]["data-image_folder"] = $imageFolder;
    }
    $element["uri"]["#attributes"]["class"][] = 'postlight-parser';
    $element["uri"]["#attributes"]["data-disable-refocus"] = 'true';
    $element["uri"]["#attributes"]["data-url_field_id"] = $id;
    $element["uri"]["#attributes"]["data-url"] = $url->toString();
    $element["uri"]["#attributes"]["data-link"] = $items->getName();
    $element["uri"]["#attributes"]["data-parser"] = $parser;
    $element["uri"]["#attributes"]["data-content"] = $this->getSetting('content_field');
    $element["uri"]["#attributes"]["data-excerpt"] = $this->getSetting('excerpt_field');
    $element["uri"]["#attributes"]["data-image"] = $image_field;
    $element["uri"]["#attributes"]["data-title"] = $this->getSetting('title_field');
    $element["title"]["#attributes"]["data-text_field_id"] = $id;
    $element['#attached']['library'][] = 'postlight_parser/postlight_parser';
    // Use ajax reload form.
    $element["uri"]['#ajax'] = [
      'callback' => [$this, 'updateContentFromUrl'],
      'event' => 'change',
      'progress' => [
        'type' => 'throbber',
        'message' => $this->t('Get content from url...'),
      ],
    ];
    return $element;
  }

  /**
   * AJAX Callback for get content from url. Calls custom Javascript command.
   *
   * @param array $form
   *   Nested array of form elements that comprise the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return Drupal\Core\Ajax\AjaxResponse
   *   Returns an AJAX response object.
   */
  public function updateContentFromUrl(array &$form, FormStateInterface $form_state) {
    // Get value from url textfield.
    $elements = $form_state->getTriggeringElement();
    $settings = $this->getSettings();
    $url = $elements['#value'] ?? '';
    $imgField = $settings['image_field'];
    $argument = [
      'url' => $url,
      'parser' => $settings['parser'],
      'parser_url' => $elements['#attributes']['data-url'] ?? '',
      'content' => $settings['content_field'],
      'excerpt' => $settings['excerpt_field'],
      'image' => $imgField,
      'image_folder' => $elements['#attributes']['data-image_folder'] ?? '',
      'title' => $settings['title_field'],
      'link' => $elements['#attributes']['data-link'] ?? '',
      'save_image' => $settings['save_image'],
    ];
    $argument = $this->urlParser->parser($argument);
    if (!empty($argument['data']['image_upload']) && !empty($imgField)) {
      $form[$imgField]["widget"][0]["#default_value"] = $argument['data']['image_upload'];
      $form[$imgField]["widget"][0]["fids"]['#value'] = [$argument['data']['image_upload']['target_id']];
      $form[$imgField]["widget"][0]["preview"] = [
        '#theme' => 'image_style',
        '#style_name' => $form[$imgField]["widget"][0]["#preview_image_style"],
        '#uri' => $argument['data']['image_upload']['uri'],
      ];
      $form[$imgField]["widget"][0]["#description"] = '';
      $form[$imgField]["widget"][0]["upload"]['#access'] = FALSE;
      // @phpstan-ignore-next-line
      $argument['data']['image_upload'] = \Drupal::service('renderer')
        ->render($form[$imgField]['widget']);
      $argument['image'] = 'edit-' . str_replace('_', '-', $argument['image']) . '-wrapper';
    }
    // Invoke the callback function.
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'postlightParser', [json_encode($argument)]));
    return $response;
  }

  /**
   * Get list of fields.
   *
   * {@inheritDoc}
   */
  protected function getConfigurableFields($fieldDefinitions, $type = FALSE) {
    if (empty($this->fieldsList)) {
      $this->setFieldsList($fieldDefinitions);
    }
    $listField = [];
    foreach ($this->fieldsList as $field_name => $field) {
      $listField[$field_name] = $field->getLabel();
      if (!empty($type)) {
        if (!is_array($type) && $type != $field->getType()) {
          unset($listField[$field_name]);
        }
        if (is_array($type) && !in_array($field->getType(), $type)) {
          unset($listField[$field_name]);
        }
      }
    }
    return $listField;
  }

  /**
   * Set field for entity.
   *
   * {@inheritDoc}
   */
  protected function setFieldsList($fieldDefinitions) {
    foreach ($fieldDefinitions as $field_name => $field) {
      if ($field instanceof FieldConfigInterface || $field_name == 'title') {
        $this->fieldsList[$field_name] = $field;
      }
    }
  }

}
