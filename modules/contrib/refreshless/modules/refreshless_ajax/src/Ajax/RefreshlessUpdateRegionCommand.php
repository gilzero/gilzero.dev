<?php

declare(strict_types=1);

namespace Drupal\refreshless_ajax\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\Core\Ajax\CommandWithAttachedAssetsInterface;
use Drupal\Core\Ajax\CommandWithAttachedAssetsTrait;

/**
 * AJAX command for updating a region in the page.
 *
 * This command is implemented by
 * Drupal.AjaxCommands.prototype.refreshlessUpdateRegion()
 * defined in js/refreshless.js.
 *
 * @ingroup ajax
 *
 * @see \Drupal\Core\Ajax\InsertCommand
 */
class RefreshlessUpdateRegionCommand implements CommandInterface, CommandWithAttachedAssetsInterface {

  use CommandWithAttachedAssetsTrait;

  /**
   * Constructs an RefreshlessUpdateRegionCommand object.
   *
   * @param string $region
   *   A region name.
   * @param array $content
   *   The render array with the content for the region.
   */
  public function __construct(
    protected string $region, protected array $content,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    return [
      'command' => 'refreshlessUpdateRegion',
      'region' => $this->region,
      'data' => $this->getRenderedContent(),
    ];
  }

}
