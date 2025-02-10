<?php

namespace Drupal\hide_submit\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 *
 */
class HideSubmitFormAlter {
  use StringTranslationTrait;

  protected $currentUser;
  protected $configFactory;
  protected $stringTranslation;

  public function __construct(AccountProxyInterface $current_user, ConfigFactoryInterface $config_factory, TranslationInterface $string_translation) {
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
    $this->stringTranslation = $string_translation;
  }

  /**
   *
   */
  public function alterForm(&$form, FormStateInterface $form_state, $form_id) {
    // Skip alteration for users with 'bypass hide submit' permission.
    if ($this->currentUser->hasPermission('bypass hide submit') && $this->currentUser->id() != 1) {
      // \Drupal::messenger()->addMessage('you are not allowed to access this page.');
      return;
    }

    $config = $this->configFactory->get('hide_submit.settings');
    // If (!$config->get('hide_submit_status')) {
    //   return;
    // }.
    $this->hideSubmitAttachJsCss($form);
    $form['#attached']['drupalSettings']['hide_submit'] = [
      'method' => $config->get('hide_submit_method'),
      'reset_time' => (int) $config->get('hide_submit_reset_time'),
      'abtext' => $config->get('hide_submit_abtext'),
      'atext' => $config->get('hide_submit_atext'),
      'hide_fx' => $config->get('hide_submit_hide_fx'),
      'hide_text' => $config->get('hide_submit_hide_text'),
      'indicator_style' => $config->get('hide_submit_indicator_style'),
      'spinner_color' => $config->get('hide_submit_spinner_color'),
      'spinner_lines' => (int) $config->get('hide_submit_spinner_lines'),
    ];
  }

  /**
   *
   */
  protected function hideSubmitAttachJsCss(&$form) {
    $form['#attached']['library'][] = 'hide_submit/hide_submit';

    // Attach additional JS/CSS based on configuration.
    $config = $this->configFactory->get('hide_submit.settings');
    if ($config->get('hide_submit_method') == 'indicator') {
      $form['#attached']['library'][] = 'hide_submit/spin';
      $form['#attached']['library'][] = 'hide_submit/ladda';
    }
    if ($config->get('hide_submit_method') == 'disable') {
      $form['#attached']['library'][] = 'hide_submit/hide_submit';
    }
    if ($config->get('hide_submit_method') == 'hide') {
      $form['#attached']['library'][] = 'hide_submit/hide_submit';
    }
  }

}
