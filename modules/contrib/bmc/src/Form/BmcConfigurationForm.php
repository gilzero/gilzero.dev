<?php

namespace Drupal\bmc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * BmcConfigurationForm class.
 */
class BmcConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['bmc.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bmc_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bmc.settings');

    $form['bmc_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration Buy Me a Coffee module'),
      '#open' => TRUE,
    ];

    $form['bmc_settings']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add your account'),
      '#description' => $this->t('Enter your Buy Me a Coffee Username.'),
      '#default_value' => $config->get('bmc_settings.username') ?: '',
      '#required' => TRUE,
    ];

    $form['bmc_settings']['description'] = [
      '#type' => 'textmarkup',
      '#title' => $this->t('Description'),
      '#markup' => $this->t('If you are not on Buy Me a Coffee yet, please <a target="_blank" class="bmc-link" href="https://www.buymeacoffee.com/signup">create a new account for free</a>. You will need it to receive payments, and to keep tab on your supporters.'),
    ];

    $form['bmc_customize_button'] = [
      '#type' => 'details',
      '#title' => $this->t('Customize Button'),
      '#open' => TRUE,
    ];

    $form['bmc_customize_button']['custom_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom Text'),
      '#description' => $this->t('Customize the Buy Me a Coffee button on your website.'),
      '#maxlength' => '25',
      '#default_value' => $config->get('bmc_settings.custom_text') ?: 'Buy Me a Coffee',
    ];

    $font_options = [
      'Cookie' => 'Cookie',
      'Lato' => 'Lato',
      'Arial' => 'Arial',
      'Comic' => 'Comic',
      'Inter' => 'Inter',
      'Bree' => 'Bree',
      'Poppins' => 'Poppins',
    ];

    $form['bmc_customize_button']['font_family'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose font'),
      '#description' => $this->t('Customize the Buy Me a Coffee button on your website.'),
      '#options' => $font_options,
      '#default_value' => $config->get('bmc_settings.font_family') ?: 'Cookie',
    ];

    $color_options = [
      '#FF813F' => $this->t('orange'),
      '#5F7FFF' => $this->t('blue'),
      '#BD5FFF' => $this->t('violet'),
      '#FF5F5F' => $this->t('red'),
      '#40DCA5' => $this->t('green'),
      '#F471FF' => $this->t('pink'),
      '#FFDD00' => $this->t('yellow'),
      '#FFFFFF' => $this->t('white'),
    ];

    foreach ($color_options as $color => $label) {
      $options[$color] = ucfirst($label);
    }

    $form['bmc_customize_button']['coffee_color'] = [
      '#type' => 'select',
      '#title' => $this->t('Coffee cup color'),
      '#description' => $this->t('Customize the Buy Me a Coffee button on your website.'),
      '#options' => $options,
      '#default_value' => $config->get('bmc_settings.coffee_color') ?: '#FFFFFF',
    ];

    $form['bmc_customize_button']['background_color'] = [
      '#type' => 'select',
      '#title' => $this->t('Background color'),
      '#description' => $this->t('Customize the Buy Me a Coffee button on your website.'),
      '#options' => $options,
      '#default_value' => $config->get('bmc_settings.background_color') ?: '#FFDD00',
    ];

    $form['bmc_customize_widget'] = [
      '#type' => 'details',
      '#title' => $this->t('Customize Widget'),
      '#description' => $this->t('Allow your fans to support directly from your website.'),
      '#open' => TRUE,
    ];

    $form['bmc_customize_widget']['widget_visible'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Widget'),
      '#description' => $this->t('Enable this feature to accept payments without leaving your website. You can also showcase your supporters and customize the widget to your style. We highly recommend enabling this feature.'),
      '#default_value' => $config->get('bmc_settings.widget_visible') ?: FALSE,
    ];

    $form['bmc_customize_widget']['widget_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#description' => $this->t('Allow your fans to support directly from your website.'),
      '#default_value' => $config->get('bmc_settings.widget_description') ?: 'Support me on Buy Me a Coffee!',
      '#states' => [
        'invisible' => [
          ':input[name="widget_visible"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['bmc_customize_widget']['widget_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Widget message'),
      '#description' => $this->t('Allow your fans to support directly from your website.'),
      '#default_value' => $config->get('bmc_settings.widget_message') ?: 'Thank you for visiting. You can now buy me a coffee!',
      '#states' => [
        'invisible' => [
          ':input[name="widget_visible"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['bmc_customize_widget']['widget_color'] = [
      '#type' => 'select',
      '#title' => $this->t('Background color'),
      '#description' => $this->t('Allow your fans to support directly from your website.'),
      '#options' => $options,
      '#default_value' => $config->get('bmc_settings.widget_color') ?: '#FFDD00',
      '#states' => [
        'invisible' => [
          ':input[name="widget_visible"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $align_options = [
      'Right' => 'Right',
      'Left' => 'Left',
    ];

    $form['bmc_customize_widget']['widget_align'] = [
      '#type' => 'select',
      '#title' => $this->t('Align'),
      '#description' => $this->t('Allow your fans to support directly from your website.'),
      '#options' => $align_options,
      '#default_value' => $config->get('bmc_settings.widget_align') ?: 'Right',
      '#states' => [
        'invisible' => [
          ':input[name="widget_visible"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['bmc_customize_widget']['widget_side_spasing'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Side spacing (px)'),
      '#description' => $this->t('Allow your fans to support directly from your website.'),
      '#default_value' => $config->get('bmc_settings.widget_side_spasing') ?: '18',
      '#states' => [
        'invisible' => [
          ':input[name="widget_visible"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['bmc_customize_widget']['widget_bottom_spasing'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bottom spacing (px)'),
      '#description' => $this->t('Allow your fans to support directly from your website.'),
      '#default_value' => $config->get('bmc_settings.widget_bottom_spasing') ?: '18',
      '#states' => [
        'invisible' => [
          ':input[name="widget_visible"]' => ['checked' => FALSE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $values = $form_state->getValues();

    $this->config('bmc.settings')
      ->set('bmc_settings.username', $values['username'])
      ->set('bmc_settings.custom_text', $values['custom_text'])
      ->set('bmc_settings.font_family', $values['font_family'])
      ->set('bmc_settings.coffee_color', $values['coffee_color'])
      ->set('bmc_settings.background_color', $values['background_color'])
      ->set('bmc_settings.widget_visible', $values['widget_visible'])
      ->set('bmc_settings.widget_description', $values['widget_description'])
      ->set('bmc_settings.widget_message', $values['widget_message'])
      ->set('bmc_settings.widget_color', $values['widget_color'])
      ->set('bmc_settings.widget_align', $values['widget_align'])
      ->set('bmc_settings.widget_side_spasing', $values['widget_side_spasing'])
      ->set('bmc_settings.widget_bottom_spasing', $values['widget_bottom_spasing'])
      ->save();
  }

}
