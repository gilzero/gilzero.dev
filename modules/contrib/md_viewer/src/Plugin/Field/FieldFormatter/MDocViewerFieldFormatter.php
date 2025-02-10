<?php

namespace Drupal\md_viewer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'MDocViewerFieldFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "mdocviewer_field",
 *   label = @Translation("Embedded Microsoft Document Viewer Formatter"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class MDocViewerFieldFormatter extends FileFormatterBase {
  /**
   * StreamWrapperManager object.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */
  protected $streamWrapperManager;

  /**
   * FileUrlGenerator object.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a StringFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   *   The stream wrapper manager.
   * @param \Drupal\Core\File\FileUrlGenerator $file_url_generator
   *   The file url generator.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    StreamWrapperManager $stream_wrapper_manager,
    FileUrlGeneratorInterface $file_url_generator
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings
    );
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration["field_definition"],
      $configuration["settings"],
      $configuration["label"],
      $configuration["view_mode"],
      $configuration["third_party_settings"],
      $container->get("stream_wrapper_manager"),
      $container->get("file_url_generator")
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      "width" => "",
      "height" => "600",
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form["width"] = [
      "#type" => "number",
      "#title" => $this->t("Width"),
      "#default_value" => $this->getSetting("width"),
      '#description' => $this->t('Default is 100%'),
      '#weight' => 1,
    ];
    $form["height"] = [
      "#type" => "number",
      "#title" => $this->t("Height"),
      "#default_value" => $this->getSetting("height"),
      '#description' => $this->t('Default is 600px'),
      '#weight' => 2,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    if ($this->getSetting("width")) {
      $summary[] = $this->t("Width: @width px", [
        "@width" => $this->getSetting("width"),
      ]);
    }
    else {
      $summary[] = $this->t("Width: 100%");
    }

    $summary[] = $this->t("Height: @height px", [
      "@height" => $this->getSetting("height"),
    ]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $file_uri = $file->getFileUri();
      $file_name = $file->getFileName();

      if ($this->streamWrapperManager->getScheme($file_uri) == "public") {
        $elements[$delta] = [
          "#theme"     => "mdoc_viewer_field",
          "#file_name" => $file_name,
          "#url"       => $this->fileUrlGenerator->generateAbsoluteString($file_uri),
          '#settings'  => [
            'width' => $this->getSetting("width"),
            'height' => $this->getSetting("height"),
          ],
        ];
      }
      else {
        $this->messenger()->addError(
          $this->t("Microsoft Docs viewer can display only files that are publicly accessible",
            ["%file" => $file_name]), FALSE
        );
      }
    }

    return $elements;
  }

}
