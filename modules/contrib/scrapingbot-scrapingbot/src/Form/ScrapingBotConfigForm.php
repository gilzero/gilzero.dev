<?php

namespace Drupal\scrapingbot\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure ScrapingBotConfigForm API access.
 */
class ScrapingBotConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   */
  const CONFIG_NAME = 'scrapingbot.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scrapingbot';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::CONFIG_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIG_NAME);

    $form['user_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ScrapingBot Username'),
      '#required' => TRUE,
      '#description' => $this->t('Can be found <a href="https://www.scraping-bot.io/dashboard/" target="_blank">here</a>.'),
      '#default_value' => $config->get('user_name'),
    ];

    $form['api_key'] = [
      '#type' => 'key_select',
      '#title' => $this->t('ScrapingBot API Key'),
      '#required' => TRUE,
      '#description' => $this->t('Can be found and generated <a href="https://www.scraping-bot.io/dashboard/" target="_blank">here</a>.'),
      '#default_value' => $config->get('api_key'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config(static::CONFIG_NAME)
      ->set('user_name', $form_state->getValue('user_name'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
