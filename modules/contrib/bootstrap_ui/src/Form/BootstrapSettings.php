<?php

namespace Drupal\bootstrap_ui\Form;

use Drupal\bootstrap_ui\BootstrapConstants;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\mdbootstrap\MDBootstrapConstants;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that configures Bootstrap settings.
 */
class BootstrapSettings extends ConfigFormBase {

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a SiteInformationForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config manager.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TypedConfigManagerInterface $typedConfigManager, ThemeHandlerInterface $theme_handler) {
    parent::__construct($config_factory, $typedConfigManager);
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('theme_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bootstrap_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'bootstrap_ui.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Usage of the Bootstrap latest version constant.
    $latest_version = BootstrapConstants::LATEST_VERSION;

    // Flags for Bootstrap version.
    $dist_label = ' <span class="bootstrap-dist-flag" title="Distribution">Dist</span>';
    $code_label = ' <span class="bootstrap-code-flag" title="Source code">Code</span>';

    // The Bootstrap UI config settings.
    $config = $this->config('bootstrap_ui.settings');

    // Choose bootstrap library.
    $library = $config->get('library');
    $libraries = bootstrap_ui_library_names();
    if (is_array($libraries) && count($libraries) > 1) {
      $form['library'] = [
        '#type'          => 'select',
        '#title'         => $this->t('Library'),
        '#options'       => $libraries,
        '#default_value' => $library,
        '#description'   => $this->t('You can choose to use original Bootstrap library or use other Bootstrap UI kits libraries such as Material Design Bootstrap.'),
        '#attributes'    => ['class' => ['bootstrap-library']],
        '#weight'        => -99,
        '#ajax'          => [
          'callback' => '::bootstrapAjaxCallback',
          'wrapper'  => 'edit-version-wrapper',
          'method'   => 'replace',
          'event'    => 'change',
          'effect'   => 'fade',
          'progress' => ['type' => 'throbber'],
        ],
      ];
    }
    else {
      $library = 'bootstrap';
      $form['library'] = [
        '#type'  => 'value',
        '#value' => $library,
      ];
    }

    // Handle load Bootstrap framework.
    $form['load'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Load'),
      '#default_value' => $config->get('load'),
      '#description'   => $this->t("If enabled, this module will attempt to load the Bootstrap for your site. To prevent loading it twice, leave this option disabled if you're including the assets manually or through another module or theme."),
      '#weight'        => -90,
    ];

    // Enable right-to-left languages.
    $form['rtl'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Support RTL'),
      '#default_value' => $config->get('rtl'),
      '#description'   => $this->t('Enable right-to-left text support in Bootstrap for languages such as Persian/Arabic across your layouts, components and utilities.'),
      '#weight'        => -66,
    ];

    // Bootstrap Stable version releases.
    $form['options_wrapper'] = [
      '#type'       => 'container',
      '#weight'     => -60,
      '#attributes' => [
        'class' => [
          'bootstrap-options-wrapper',
          'form-item',
        ],
      ],
    ];

    // Show warning missing library and lock on cdn method.
    $assets = bootstrap_ui_check_installed();
    $method = $config->get('method');
    $method_lock_change = FALSE;
    if ($assets === FALSE) {
      $method = 'cdn';
      $method_lock_change = TRUE;
      $method_warning = $this->t('You cannot set local due to the Bootstrap library is missing. Please <a href=":downloadUrl" rel="external" target="_blank">Download</a> and extract to "/libraries/bootstrap" directory.', [
        ':downloadUrl' => '//github.com/twbs/bootstrap/tags',
      ]);

      // Display warning message off.
      $form['hide'] = [
        '#type'          => 'checkbox',
        '#title'         => $this->t('Hide warning'),
        '#default_value' => $config->get('hide') ?? FALSE,
        '#description'   => $this->t("If you want to use the CDN without installing the local library, you can turn off the warning."),
      ];

      $form['method_warning'] = [
        '#type'   => 'item',
        '#markup' => '<div class="library-status-report">' . $method_warning . '</div>',
        '#prefix' => '<div id="method-warning">',
        '#suffix' => '</div>',
        '#states' => [
          'visible' => [
            ':input[name="load"]' => ['checked' => TRUE],
          ],
          'invisible' => [
            ':input[name="hide"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $version = !empty($form_state->getValue('version')) ? $form_state->getValue('version') : (!empty($config->get('version')) ? $config->get('version') : $latest_version);
    }
    else {
      $version = ($method == 'cdn') ? (!empty($form_state->getValue('version')) ? $form_state->getValue('version') : $config->get('version')) : bootstrap_ui_detect_version();
    }

    // Load method library from CDN or Locally.
    $form['method'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Attach method'),
      '#options'       => [
        'local' => $this->t('Local'),
        'cdn'   => $this->t('CDN'),
      ],
      '#default_value' => $method,
      '#description'   => $this->t('These settings control how the library is loaded. You can choose to load it from a "CDN" (external source) or from the "Local" and "Composer" (internal library) installations.'),
      '#disabled'      => $method_lock_change,
      '#prefix'        => '<div id="method-options">',
      '#suffix'        => '</div>',
    ];

    // Bootstrap Stable version releases.
    $form['version_wrapper'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => [
          'bootstrap-version-wrapper',
          'container-inline',
          'form-item',
        ],
      ],
      '#states'     => [
        'disabled' => [
          'select[name="method"]' => ['value' => 'local'],
        ],
      ],
    ];

    // Get Bootstrap Stable versions.
    $versions = bootstrap_ui_version_options();
    $in_versions = in_array($version, _bootstrap_ui_array_flatten($versions));
    $form['version_wrapper']['version'] = [
      '#type'             => 'select',
      '#options'          => $versions,
      '#title'            => $this->t('Version'),
      '#default_value'    => $in_versions ? $version : 'other',
      '#needs_validation' => FALSE,
      '#validated'        => TRUE,
      '#prefix'           => '<div id="version-options">',
      '#suffix'           => '</div>',
      '#ajax'             => [
        'callback' => '::bootstrapFilesAjaxCallback',
        'wrapper'  => 'edit-file-types',
        'method'   => 'replace',
        'event'    => 'change',
        'effect'   => 'fade',
        'progress' => ['type' => 'throbber'],
      ],
    ];

    // Get Bootstrap latest stable releases.
    $releases = bootstrap_ui_version_releases();
    $in_releases = in_array($version, _bootstrap_ui_array_flatten($releases));
    $form['version_wrapper']['release'] = [
      '#type'             => 'select',
      '#options'          => $in_releases ? $releases : $releases + ['' => $version . ' (' . $this->t('Not stable') . ')'],
      '#default_value'    => $in_releases ? $version : '',
      '#title'            => $this->t('Stable releases'),
      '#attributes'       => ['class' => ['bootstrap-version-release']],
      '#needs_validation' => FALSE,
      '#validated'        => TRUE,
      '#prefix'           => '<div id="release-options">',
      '#suffix'           => '</div>',
      '#ajax'             => [
        'callback' => '::bootstrapFilesAjaxCallback',
        'wrapper'  => 'edit-file-types',
        'method'   => 'replace',
        'event'    => 'change',
        'effect'   => 'fade',
        'progress' => ['type' => 'throbber'],
      ],
      '#states'           => [
        'visible' => [
          'select[name="version"]' => ['value' => 'other'],
        ],
      ],
    ];
    $form['version_wrapper']['assets'] = [
      '#type'   => 'item',
      '#field_suffix' => $assets && $method != 'cdn' ? ($assets == 'dist' ? $dist_label : $code_label) : '',
      '#states' => [
        'visible' => [
          'select[name="method"]' => ['value' => 'local'],
        ],
      ],
    ];
    $form['version_wrapper']['version_description'] = [
      '#type' => 'item',
      '#description' => $this->t('Choose the latest stable major version that has been released. Select "Other" option to choose a specific release from older minor versions. The installed local library will automatically detect the version.'),
    ];
    $form['version_wrapper']['release_notice'] = [
      '#type' => 'item',
      '#description' => $this->t('Notice: Major versions (e.g., v5.x) introduce significant changes, while minor versions (e.g., v5.2.x) focus on bug fixes and improvements. Choosing the latest minor release within a major version ensures stability with up-to-date enhancements without major changes.'),
      '#states'        => [
        'visible' => [
          'select[name="version"]' => ['value' => 'other'],
        ],
        'enabled' => [
          ':input[name="version"]' => ['value' => 'other'],
        ],
      ],
    ];

    // Production or minimized version.
    $form['build'] = [
      '#type'        => 'details',
      '#title'       => $this->t('Build variant'),
      '#open'        => TRUE,
      '#states'      => [
        'disabled' => [
          'select[name="library"]' => ['value' => 'mdbootstrap'],
        ],
        'invisible' => [
          'select[name="library"]' => ['value' => 'mdbootstrap'],
        ],
      ],
    ];
    $form['build']['variant'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Choose production or development library.'),
      '#options'       => [
        $this->t('Use non-minimized library (Development)'),
        $this->t('Use minimized library (Deployment)'),
      ],
      '#default_value' => $config->get('build.variant'),
      '#description'   => $this->t('These settings work with both local libraries and CDN methods.'),
    ];

    // Vertical tabs for usability.
    $form['usability'] = [
      '#type'   => 'vertical_tabs',
      '#title'  => $this->t('Usability'),
      '#weight' => 99,
    ];

    // Per-file usability.
    $form['file_types'] = [
      '#type'       => 'details',
      '#title'      => $this->t('Files'),
      '#attributes' => [
        'class' => [
          'details--settings',
          'b-tooltip',
        ],
      ],
      '#group'      => 'usability',
      '#open'       => TRUE,
    ];

    // Bootstrap files wrapper.
    $form['file_types']['files_wrapper'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => [
          'bootstrap-file-wrapper',
          'form-item',
        ],
      ],
    ];

    $is_bootstrap = is_null($form_state->getValue('library')) ? $library == 'bootstrap' : $form_state->getValue('library') == 'bootstrap';
    if ($is_bootstrap) {
      $js_files = [
        'none' => $this->t('None'),
        'bootstrap' => $this->t('Bootstrap JS'),
      ];
      $triggerdElement = $form_state->getTriggeringElement();
      if (isset($triggerdElement['#name']) && ($triggerdElement['#name'] == 'version' || $triggerdElement['#name'] == 'release')) {
        $current_version = $triggerdElement['#value'] != 'other' ? $triggerdElement['#value'] : $latest_version;
      }
      else {
        $current_version = $version;
      }
      if (intval($current_version) >= 4) {
        $js_files['bundle'] = $this->t('Bootstrap bundle JS (Included Popper JS)');
        $js_default = $config->get('file_types.js') ?? 'bundle';
        $js_description = $this->t('Use Bootstrap standalone to exclude Popper if you don’t plan to use dropdowns, popovers, or tooltips. This saves some kilobytes and prevents loading Popper twice if it’s already handled manually in your theme or loaded by another module.');
      }
      else {
        $js_default = $config->get('file_types.js') != 'bundle' ? $config->get('file_types.js') : 'bootstrap';
        $js_description = $this->t('If you don’t plan to use Bootstrap components, you can set the JavaScript file to "None" mode to save some kilobytes.');
      }

      // States for JavaScript and CSS files.
      $file_states = [
        ':input[name="js"]' => ['value' => 'none'],
        ':input[name="css"]' => ['checked' => FALSE],
      ];

      $form['file_types']['js'] = [
        '#type'          => 'radios',
        '#options'       => $js_files,
        '#title'         => $this->t('Attach JavaScript'),
        '#description'   => $js_description,
        '#default_value' => $js_default,
        '#required'      => TRUE,
        '#prefix'        => '<div id="file-js">',
        '#suffix'        => '</div>',
      ];
      $form['file_types']['css'] = [
        '#type'          => 'checkbox',
        '#title'         => $this->t('Attach CSS'),
        '#default_value' => $config->get('file_types.css'),
        '#description'   => $this->t('If you don’t plan to use the full Bootstrap CSS or only want to use specific files like the reset file, you can disable "Attach CSS" to view and select individual files.'),
      ];
      $form['file_types']['css_files'] = [
        '#type'       => 'container',
        '#title'      => $this->t('Particle CSS files'),
        '#prefix'     => '<div id="file-css">',
        '#suffix'     => '</div>',
        '#states'     => [
          'invisible' => [
            ':input[name="css"]' => ['checked' => TRUE],
          ],
        ],
      ];

      if (intval($current_version) >= 4) {
        $file_states[':input[name="reboot"]'] = ['checked' => FALSE];
        $file_states[':input[name="grid"]'] = ['checked' => FALSE];
        $form['file_types']['css_files']['reboot'] = [
          '#type'          => 'checkbox',
          '#title'         => $this->t('Use reboot'),
          '#default_value' => $config->get('file_types.reboot'),
        ];
        $form['file_types']['css_files']['grid'] = [
          '#type'          => 'checkbox',
          '#title'         => $this->t('Use grid'),
          '#default_value' => $config->get('file_types.grid'),
        ];
      }
      if (intval($current_version) >= 5) {
        $file_states[':input[name="utilities"]'] = ['checked' => FALSE];
        $form['file_types']['css_files']['utilities'] = [
          '#type'          => 'checkbox',
          '#title'         => $this->t('Use utilities'),
          '#default_value' => $config->get('file_types.utilities'),
        ];
      }
      if (intval($current_version) == 3) {
        $file_states[':input[name="theme"]'] = ['checked' => FALSE];
        $form['file_types']['css_files']['theme'] = [
          '#type'          => 'checkbox',
          '#title'         => $this->t('Use theme'),
          '#default_value' => $config->get('file_types.theme'),
        ];
      }
      if (intval($current_version) == 2) {
        $file_states[':input[name="responsive"]'] = ['checked' => FALSE];
        $form['file_types']['css_files']['responsive'] = [
          '#type'          => 'checkbox',
          '#title'         => $this->t('Use responsive'),
          '#default_value' => $config->get('file_types.responsive'),
        ];
      }

      // Warning for disabled all files.
      $file_warning = $this->t('If all JavaScript and CSS files are disabled, the library cannot load. Therefore, after saving settings, the load option will be disabled and revert to the default state.');
      $form['file_types']['file_warning'] = [
        '#type'   => 'item',
        '#markup' => '<div class="file-type-report">' . $file_warning . '</div>',
        '#prefix' => '<div id="file-warning">',
        '#suffix' => '</div>',
        '#states' => [
          'visible' => [
            $file_states,
          ],
        ],
        '#weight' => -9,
      ];
    }

    // Get list of all installed themes.
    $themes = $this->themeHandler->listInfo();
    $active_themes = [];
    foreach ($themes as $key => $theme) {
      if ($theme->status) {
        $active_themes[$key] = $theme->info['name'];
      }
    }

    // Per-theme usability.
    $form['theme_groups'] = [
      '#type'       => 'details',
      '#title'      => $this->t('Themes'),
      '#attributes' => ['class' => ['details--settings', 'b-tooltip']],
      '#group'      => 'usability',
      '#open'       => TRUE,
    ];
    $form['theme_groups']['themes'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Installed themes'),
      '#description'   => $this->t("Specify themes by selecting their names. The list of themes to which library loading will be restricted."),
      '#options'       => $active_themes,
      '#multiple'      => TRUE,
      '#default_value' => $config->get('theme_groups.themes'),
    ];
    $form['theme_groups']['theme_negate'] = [
      '#type' => 'radios',
      '#title' => $this->t('Activate on specific themes'),
      '#title_display' => 'invisible',
      '#options' => [
        $this->t('All themes except those selected'),
        $this->t('Only the selected themes'),
      ],
      '#default_value' => $config->get('theme_groups.negate'),
    ];

    // Per-path usability.
    $form['request_path'] = [
      '#type'       => 'details',
      '#title'      => $this->t('Pages'),
      '#attributes' => ['class' => ['details--settings', 'b-tooltip']],
      '#group'      => 'usability',
    ];
    $form['request_path']['pages'] = [
      '#type'          => 'textarea',
      '#title'         => '<span class="element-invisible">' . $this->t('Pages') . '</span>',
      '#default_value' => _bootstrap_ui_array_to_string($config->get('request_path.pages')),
      '#description'   => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. An example path is %admin-wildcard for every user page. %front is the front page.", [
        '%admin-wildcard' => '/admin/*',
        '%front'          => '<front>',
      ]),
    ];
    $form['request_path']['page_negate'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Load Bootstrap on specific pages'),
      '#title_display' => 'invisible',
      '#options'       => [
        $this->t('All pages except those listed'),
        $this->t('Only for the listed pages'),
      ],
      '#default_value' => $config->get('request_path.negate'),
    ];

    // The Bootstrap UI settings library.
    $form['#attached']['library'][] = 'bootstrap_ui/bootstrap.settings';

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function bootstrapAjaxCallback(array &$form, FormStateInterface $form_state) {
    // Set version and release options for selected bootstrap library.
    $library = $form_state->getValue('library');
    if ($library == 'mdbootstrap') {
      $versions = mdbootstrap_version_options();
      $releases = mdbootstrap_version_releases();
      $form['version_wrapper']['version']['#options'] = $versions;
      $form['version_wrapper']['release']['#options'] = $releases;

      if (mdbootstrap_check_installed() === FALSE) {
        // Latest version of the Material Design Bootstrap UI Kit.
        $latest_version = MDBootstrapConstants::MDBOOTSTRAP_LATEST_VERSION;

        $method = 'cdn';
        $method_warning = $this->t('You cannot set local due to the MDBootstrap library is missing. Please <a href=":downloadUrl" rel="external" target="_blank">Download</a> and extract to "/libraries/mdb" directory.', [
          ':downloadUrl' => 'https://github.com/mdbootstrap/mdb-ui-kit/tags',
        ]);

        $form['method']['#markup'] = '<div class="library-status-report">' . $method_warning . '</div>';

        $form['method']['#value'] = $method;
        $form['method']['#default_value'] = $method;
        $form['method']['#disabled'] = TRUE;

        $form['version_wrapper']['version']['#value'] = $latest_version;
        $form['version_wrapper']['version']['#default_value'] = $latest_version;

        $form_state->setValue('version', $latest_version);
      }
      else {
        $version = mdbootstrap_detect_version();
        $method = 'local';

        $form['method']['#value'] = $method;
        $form['method']['#default_value'] = $method;

        $in_versions = in_array($version, _bootstrap_ui_array_flatten($versions));
        $form['version_wrapper']['version']['#value'] = $in_versions ? $version : 'other';
        $form['version_wrapper']['version']['#default_value'] = $in_versions ? $version : 'other';

        $in_releases = in_array($version, _bootstrap_ui_array_flatten($releases));
        $form['version_wrapper']['release']['#value'] = $in_releases ? $version : '';
        $form['version_wrapper']['release']['#default_value'] = $in_releases ? $version : '';
        $form['version_wrapper']['release']['#options'] = $in_releases ? $releases : $releases + ['' => $this->t('Not stable')];

        $form_state->setValue('version', $version);
      }
    }
    else {
      $versions = bootstrap_ui_version_options();
      $releases = bootstrap_ui_version_releases();
      $form['version_wrapper']['version']['#options'] = $versions;
      $form['version_wrapper']['release']['#options'] = $releases;

      if (bootstrap_ui_check_installed() === FALSE) {
        // Latest version of the Bootstrap CSS Framework.
        $latest_version = BootstrapConstants::LATEST_VERSION;

        $method = 'cdn';

        $form['method']['#value'] = $method;
        $form['method']['#default_value'] = $method;
        $form['method']['#disabled'] = TRUE;

        $form['version_wrapper']['version']['#value'] = $latest_version;
        $form['version_wrapper']['version']['#default_value'] = $latest_version;

        $form_state->setValue('version', $latest_version);
      }
      else {
        $version = bootstrap_ui_detect_version();
        $method = 'local';

        $form['method']['#value'] = $method;
        $form['method']['#default_value'] = $method;

        $in_versions = in_array($version, _bootstrap_ui_array_flatten($versions));
        $form['version_wrapper']['version']['#value'] = $in_versions ? $version : 'other';
        $form['version_wrapper']['version']['#default_value'] = $in_versions ? $version : 'other';

        $in_releases = in_array($version, _bootstrap_ui_array_flatten($releases));
        $form['version_wrapper']['release']['#value'] = $in_releases ? $version : '';
        $form['version_wrapper']['release']['#default_value'] = $in_releases ? $version : '';
        $form['version_wrapper']['release']['#options'] = $in_releases ? $releases : $releases + ['' => $this->t('Not stable')];

        $form_state->setValue('version', $version);
      }
    }

    // Save the updated Bootstrap settings.
    $this->config('bootstrap_ui.settings')
      ->set('library', $library)
      ->set('method', $method)
      ->set('version', $version)
      ->save();

    $form_state->setRebuild(TRUE);

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand("#method-warning", $form['method_warning']));
    $response->addCommand(new ReplaceCommand("#method-options", $form['method']));
    $response->addCommand(new ReplaceCommand("#version-options", $form['version_wrapper']['version']));
    $response->addCommand(new ReplaceCommand("#release-options", $form['version_wrapper']['release']));
    $response->addCommand(new ReplaceCommand('#file-js', $form['file_types']['js']));
    $response->addCommand(new ReplaceCommand('#file-css', $form['file_types']['css_files']));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function bootstrapFilesAjaxCallback(array &$form, FormStateInterface $form_state) {
    // Return local library, because there is no option to change version.
    if ($form_state->getValue('method') == 'local') {
      return;
    }

    $library = $form_state->getValue('library');
    $version = $form_state->getValue('version') != 'other' ? $form_state->getValue('version') : $form_state->getValue('release');
    if ($library == 'mdbootstrap') {
      // Save the updated MD Bootstrap settings.
      $this->config('mdbootstrap.settings')
        ->set('version', $version)
        ->save();
    }
    else {
      // Save the updated Bootstrap settings.
      $this->config('bootstrap_ui.settings')
        ->set('version', $version)
        ->save();
    }

    $form_state->setRebuild(TRUE);

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#file-js', $form['file_types']['js']));
    $response->addCommand(new ReplaceCommand('#file-css', $form['file_types']['css_files']));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Force detected version from installed library.
    if ($values['library'] != 'bootstrap') {
      $values['js'] = TRUE;
      $values['css'] = TRUE;
      $values['reboot'] = FALSE;
      $values['grid'] = FALSE;
      $values['utilities'] = FALSE;
      $values['theme'] = FALSE;
      $values['responsive'] = FALSE;
      $version = '';
    }
    elseif (bootstrap_ui_check_installed() !== FALSE && $values['method'] == 'local') {
      $version = bootstrap_ui_detect_version();
    }
    else {
      $version = $values['version'] == 'other' ? $values['release'] : $values['version'];
    }

    // Check if no file (JS/CSS) enabled to load.
    if ($values['js'] == 'none' && $values['css'] == 0) {
      if (intval($version) >= 5) {
        $particles = (isset($values['grid']) && $values['grid']) || (isset($values['reboot']) && $values['reboot']) || (isset($values['utilities']) && $values['utilities']) ?? FALSE;
      }
      elseif (intval($version) >= 4) {
        $particles = (isset($values['grid']) && $values['grid']) || (isset($values['reboot']) && $values['reboot']) ?? FALSE;
      }
      elseif (intval($version) >= 3) {
        $particles = (isset($values['theme']) && $values['theme']) ?? FALSE;
      }
      elseif (intval($version) >= 2) {
        $particles = (isset($values['responsive']) && $values['responsive']) ?? FALSE;
      }
      else {
        $particles = FALSE;
      }

      // Turn off bootstrap library load instead and reset files to default.
      if (!$particles) {
        $values['js'] = intval($version) >= 4 ? 'bundle' : 'bootstrap';
        $values['css'] = 1;
        $values['load'] = 0;
      }
    }

    // Save the updated Bootstrap settings.
    $this->config('bootstrap_ui.settings')
      ->set('library', $values['library'])
      ->set('load', $values['load'])
      ->set('rtl', $values['rtl'])
      ->set('hide', isset($values['hide']) && $values['hide'] !== 0 ?? FALSE)
      ->set('method', $values['method'])
      ->set('version', $version)
      ->set('build.variant', $values['variant'])
      ->set('file_types.js', $values['js'])
      ->set('file_types.css', $values['css'])
      ->set('file_types.reboot', isset($values['reboot']) && $values['reboot'] !== 0 ?? FALSE)
      ->set('file_types.grid', isset($values['grid']) && $values['grid'] !== 0 ?? FALSE)
      ->set('file_types.utilities', isset($values['utilities']) && $values['utilities'] !== 0 ?? FALSE)
      ->set('file_types.theme', isset($values['theme']) && $values['theme'] !== 0 ?? FALSE)
      ->set('file_types.responsive', isset($values['responsive']) && $values['responsive'] !== 0 ?? FALSE)
      ->set('theme_groups.negate', $values['theme_negate'])
      ->set('theme_groups.themes', $values['themes'])
      ->set('request_path.negate', $values['page_negate'])
      ->set('request_path.pages', _bootstrap_ui_string_to_array($values['pages']))
      ->save();

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

}
