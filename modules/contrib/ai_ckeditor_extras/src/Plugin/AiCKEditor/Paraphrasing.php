<?php

namespace Drupal\ai_ckeditor_extras\Plugin\AICKEditor;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ai_ckeditor\AiCKEditorPluginBase;
use Drupal\ai_ckeditor\Attribute\AiCKEditor;
use Drupal\ai_ckeditor\Command\AiRequestCommand;
use Drupal\taxonomy\Entity\Term;

/**
 * Plugin to paraphrase the selected text.
 */
#[AiCKEditor(
  id: 'ai_ckeditor_paraphrasing',
  label: new TranslatableMarkup('Paraphrasing'),
  description: new TranslatableMarkup('This solution helps you to quickly reword text by replacing certain words with synonyms or restructuring sentences. A paraphraser is ideal for rephrasing articles, essays, and various types of content, making the rewriting process seamless and effective.'),
)]
final class Paraphrasing extends AiCKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'provider' => NULL,
      'paraphrasing_mode' => NULL,
      'use_description' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {


    $options = $this->aiProviderManager->getSimpleProviderModelOptions('chat');
    array_shift($options);
    array_splice($options, 0, 1);
    $form['provider'] = [
      '#type' => 'select',
      "#empty_option" => $this->t('-- Default from AI module (chat) --'),
      '#title' => $this->t('AI provider'),
      '#options' => $options,
      '#default_value' => $this->configuration['provider'] ?? $this->aiProviderManager->getSimpleDefaultProviderOptions('chat'),
      '#description' => $this->t('Select which provider to use for this plugin. See the <a href=":link">Provider overview</a> for details about each provider.', [':link' => '/admin/config/ai/providers']),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['provider'] = $form_state->getValue('provider');
    $this->configuration['autocreate'] = (bool) $form_state->getValue('autocreate');
    $this->configuration['paraphrasing_mode'] = $form_state->getValue('paraphrasing_mode');
  }

  /**
   * {@inheritdoc}
   */
  public function buildCkEditorModalForm(array $form, FormStateInterface $form_state, array $settings = []) {
    $storage = $form_state->getStorage();
    $editor_id = $this->requestStack->getParentRequest()->get('editor_id');

    if (empty($storage['selected_text'])) {
      return [
        '#markup' => '<p>' . $this->t('You must select some text before you can paraphrase it.') . '</p>',
      ];
    }

    $form = parent::buildCkEditorModalForm($form, $form_state);

    $form['paraphrasing_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose default vocabulary for paraphrasing options'),
      '#options' => $this->getParaphrasingOptions(),
      '#description' => $this->t('Select the vocabulary that contains paraphrasing options.'),
      '#default_value' => $this->configuration['paraphrasing_mode'],
    ];


    $form['selected_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Selected text to paraphrase it'),
      '#disabled' => TRUE,
      '#default_value' => $storage['selected_text'],
    ];

    $form['response_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Suggested paraphrase text'),
      '#description' => $this->t('The response from AI will appear in the box above. You can edit and tweak the response before saving it back to the main editor.'),
      '#prefix' => '<div id="ai-ckeditor-response">',
      '#suffix' => '</div>',
      '#default_value' => '',
      '#allowed_formats' => [$editor_id],
      '#format' => $editor_id,
    ];

    $form['actions']['generate']['#value'] = $this->t('Paraphrase');

    return $form;
  }

  /**
   * Generate text callback.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return mixed
   *   The result of the AJAX operation.
   */
  public function ajaxGenerate(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $options = $this->getParaphrasingOptions();
    $paraphrasing_option = strtolower($options[$values["plugin_config"]['paraphrasing_mode']]);

    try {
      $prompt = 'Paraphrase the following text ' . $paraphrasing_option . ' using the same input language and using basic HTML to be used on a WYSIWYG editor:\r\n"' . $values["plugin_config"]["selected_text"];
      \Drupal::logger('ai_ckeditor')->notice('prompt: @prompt', ['@prompt' =>  $prompt]);
      $response = new AjaxResponse();
      $values = $form_state->getValues();
      $response->addCommand(new AiRequestCommand($prompt, $values["editor_id"], $this->pluginDefinition['id'], 'ai-ckeditor-response'));
      return $response;
    }
    catch (\Exception $e) {
      $this->logger->error("There was an error in the Paraphrase AI plugin for CKEditor.");
      return $form['plugin_config']['response_text']['#value'] = "There was an error in the Paraphrase AI plugin for CKEditor.";
    }
  }

  /**
   * Helper function to get paraphrasing array.
   *
   * @return array
   *   The options array.
   */
  protected function getParaphrasingOptions(): array {
    return [
      'simplify' => t('Simplifying and making it more understandable'),
      'general' => t('Restructuring text without changing the meaning'),
      'extend' => t('Expanding the text to provide more context'),
      'synonymic' => t('Replacing certain words with their synonyms'),
    ];
  }

}
