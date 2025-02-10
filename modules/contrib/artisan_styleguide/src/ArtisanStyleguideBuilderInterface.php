<?php

namespace Drupal\artisan_styleguide;

/**
 * Artisan Styleguider builder interface.
 */
interface ArtisanStyleguideBuilderInterface {

  /**
   * Build styleguide.
   *
   * @return array
   *   Ready to render styleguide.
   */
  public function build(): array;

  /**
   * Get intro notes.
   *
   * @return array
   *   Intro notes translatable markups.
   */
  public function getIntroNotes(): array;

  /**
   * Component definition preview.
   *
   * @param string $plugin_id
   *   Plugin id e.g "artisan:header".
   * @param array $definition
   *   Plugin definition.
   *
   * @return array
   *   Renderable component preview.
   */
  public function componentDefinitionPreview($plugin_id, array $definition): array;

  /**
   * Component definition preview process props.
   *
   * @param array $definition
   *   Component definition to process.
   * @param array $component
   *   Component result to process.
   * @param array $clarifications
   *   Clarifications.
   * @param bool $empty_props_slots
   *   Empty props or slots indicator.
   */
  public function componentDefinitionPreviewProcessProps(array $definition, array &$component, array &$clarifications, bool &$empty_props_slots): void;

  /**
   * Component definition preview process slots.
   *
   * @param array $definition
   *   Component definition to process.
   * @param array $component
   *   Component result to process.
   * @param array $clarifications
   *   Clarifications.
   * @param bool $empty_props_slots
   *   Empty props or slots indicator.
   */
  public function componentDefinitionPreviewProcessSlots(array $definition, array &$component, array &$clarifications, bool &$empty_props_slots): void;

}
