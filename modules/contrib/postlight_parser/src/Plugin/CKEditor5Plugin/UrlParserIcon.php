<?php

namespace Drupal\postlight_parser\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 Url get content Icon plugin.
 */
class UrlParserIcon extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [
      'parser' => 'readability',
      'endpoint' => '',
      'save_image' => FALSE,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['parser'] = [
      '#title' => $this->t('Parser library'),
      '#default_value' => $this->configuration['parser'],
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
    $form['endpoint'] = [
      '#title' => $this->t('Url endpoint api'),
      '#default_value' => $this->configuration['endpoint'],
      '#descriptions' => $this->t('Url Postlight / Mercury parser'),
      '#type' => 'url',
      '#states' => [
        'visible' => [
          'select[name="editor[settings][plugins][postlight_parser_plugin][parser]"]' => ['value' => 'api'],
        ],
      ],
    ];
    // Under development.
    $form['save_image'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Save image inline'),
      '#default_value' => $this->configuration['save_image'] ?? FALSE,
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['parser'] = $form_state->getValue('parser') ?? 'readability';
    $this->configuration['endpoint'] = $form_state->getValue('endpoint') ?? '';
    $this->configuration['save_image'] = $form_state->getValue('save_image') ?? FALSE;
  }

  /**
   * {@inheritdoc}
   *
   * Get configuration in editor config.
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $url = Url::fromRoute('postlight.parser', ['parser' => $this->configuration['parser']], ['absolute' => TRUE]);
    if ($url && empty($this->configuration['endpoint'])) {
      $this->configuration['endpoint'] = $url->toString();
    }
    return [
      'url_parser' => $this->configuration,
    ];
  }

}
