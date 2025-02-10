<?php

namespace Drupal\xray_audit_insight\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class for settings form.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * The module settings.
   */
  const MODULE_SETTINGS = 'xray_audit_insight.settings';

  /**
   * The plugin manager.
   *
   * @var \Drupal\xray_audit_insight\Plugin\XrayAuditInsightPluginManager
   */
  protected $pluginManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $form_instance = new static($container->get('config.factory'), $container->get('config.typed'));
    $form_instance->pluginManager = $container->get('plugin_manager.xray_audit_insight');
    return $form_instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::MODULE_SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return static::MODULE_SETTINGS;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['insight_switch'] = [
      '#type' => 'container',
    ];
    $form['insight_switch']['title'] = [
      '#type' => 'html_tag',
      '#tag' => 'h4',
      '#value' => $this->t('Insights configuration'),
    ];
    $form['insight_switch']['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Configure the insights that you do not want to be displayed in the Status Report.'),
    ];

    $insights = $this->getInsights();
    foreach ($insights as $insight) {
      $form['insight_switch'][$insight['id'] . '_wrapper'] = $insight['plugin_instance']->buildInsightForSettings();
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Get the insights.
   *
   * @return array
   *   The insights.
   */
  protected function getInsights(): array {
    $insights = [];
    $insight_definitions = $this->pluginManager->getDefinitions();
    foreach ($insight_definitions as $plugin_id => $insight) {
      $created_instance = $this->pluginManager->createInstance($plugin_id);
      $insights[$plugin_id] = [
        'id' => $plugin_id,
        'label' => $insight['label'],
        'plugin_instance' => $created_instance,
      ];
    }
    return $insights;
  }

}
