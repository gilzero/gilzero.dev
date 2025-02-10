<?php

namespace Drupal\artisan_styleguide;

use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Theme\ComponentPluginManager;

/**
 * Artisan Styleguider builder.
 */
class ArtisanStyleguideBuilder implements ArtisanStyleguideBuilderInterface {

  use StringTranslationTrait;

  /**
   * Contructor.
   */
  public function __construct(
    private readonly RendererInterface $renderer,
    private readonly ComponentPluginManager $pluginManagerSdc,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $components = [];
    $build = [];
    foreach ($this->pluginManagerSdc->getDefinitions() as $plugin_id => $definition) {
      $components[] = $this->componentDefinitionPreview($plugin_id, $definition);
    }
    $build['content'] = [
      '#theme' => 'artisan_styleguide',
      '#cache' => ['max-age' => 0],
      '#intro_notes' => $this->getIntroNotes(),
      '#components' => $components,
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getIntroNotes(): array {
    return [
      $this->t('Note customizations of main theme uses CSS variables like "--theme-palette-primary". Make sure you take that into account in order to preserve site design harmony.'),
      $this->t('Theme customization usage format "--example-color: var(--theme-example-color, var(--FALLBACK-CSS-VAR, FALLBACK-CSS-VALUE));".'),
      $this->t('Theme customization usage defined format "color: var(--example-color);".'),
      $this->t('Ensure correct prop (predefined schema simple value) or slot (renderable "block") definitions for components.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentDefinitionPreview($plugin_id, array $definition): array {
    $component = [
      '#type' => 'component',
      '#component' => $plugin_id,
      '#props' => [],
      '#slots' => [],
    ];
    $clarifications = [];
    $name = $definition['name'] ?? $this->t('TBD');
    $empty_props_slots = TRUE;
    self::componentDefinitionPreviewProcessProps($definition, $component, $clarifications, $empty_props_slots);
    self::componentDefinitionPreviewProcessSlots($definition, $component, $clarifications, $empty_props_slots);
    // Uncomment for debug:
    // @code if ($plugin_id == 'artisan_styleguide:artisan-styleguide-sdc-model') { @endcode
    // @code   dump($component); @endcode
    // @code   dump($definition); @endcode
    // @code } @endcode
    try {
      $to_render = $component;
      $rendered = $this->renderer->renderPlain($to_render);
      $status = TRUE;
    }
    catch (\Exception $ex) {
      $rendered = '';
      $status = FALSE;
      $clarifications[] = $this->t('Unable to preview, ensure props & slots definitions, slots should be always render arrays or twig string (scalar): @error.', [
        '@error' => $ex->getMessage(),
      ]);
      $clarifications[] = $this->t('Note no attached CSS/JS in preview for this component due failed to render.');
    }
    if ($empty_props_slots) {
      $status = FALSE;
      $clarifications[] = $this->t('Missing props and/or slots definitions.');
    }
    if (empty(trim(strip_tags((string) $rendered), '<img>'))) {
      $status = FALSE;
      $clarifications[] = $this->t('Empty output detected, please review definition & examples definitions.');
    }
    if ($status) {
      $clarifications[] = $this->t('It should look great!');
    }
    return [
      '#theme' => 'artisan_styleguide__component',
      '#status' => $status,
      '#plugin_id' => $plugin_id,
      '#name' => $name,
      '#rendered' => $rendered,
      '#component' => $component,
      '#weight' => $plugin_id == 'artisan_styleguide:artisan-styleguide-sdc-model' ? -9999 : NULL,
      '#clarifications' => $clarifications,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function componentDefinitionPreviewProcessProps(array $definition, array &$component, array &$clarifications, bool &$empty_props_slots): void {
    foreach ($definition['props']['properties'] ?? [] as $prop_key => $prop_def) {
      if (empty($prop_def['examples'] ?? NULL)) {
        $clarifications[] = $this->t('@prop - Missing examples for property declaration.', [
          '@prop' => $prop_key,
        ]);
        continue;
      }
      $empty_props_slots = FALSE;
      if ($prop_def['type'] && class_exists($prop_def['type'])) {
        $class = $prop_def['type'];
        $component['#props'][$prop_key] = new $class(reset($prop_def['examples']));
      }
      else {
        $component['#props'][$prop_key] = reset($prop_def['examples']);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function componentDefinitionPreviewProcessSlots(array $definition, array &$component, array &$clarifications, bool &$empty_props_slots): void {
    foreach ($definition['slots'] ?? [] as $slot_key => $slot_def) {
      if (empty($slot_def['examples'] ?? NULL)) {
        $clarifications[] = $this->t('@slot - Missing examples for slot declaration.', [
          '@slot' => $slot_key,
        ]);
        continue;
      }
      $empty_props_slots = FALSE;
      $first_example = reset($slot_def['examples']);
      if (is_string($first_example)) {
        $first_example = [
          '#type' => 'inline_template',
          '#template' => $first_example,
        ];
      }
      $component['#slots'][$slot_key] = $first_example;
    }
  }

}
