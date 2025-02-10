<?php

declare(strict_types=1);

namespace Drupal\refreshless_ajax\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * AJAX command for updating the HTML <head>.
 *
 * Note that this does not touch CSS or JS in <head>: for those, the AJAX
 * system has separate commands, we don't need to do anything about that.
 * This is for everything in <head> *except* CSS and JS.
 *
 * @see \Drupal\Core\Ajax\AjaxResponseAttachmentsProcessor::buildAttachmentsCommands()
 *
 * This command is implemented by
 * Drupal.AjaxCommands.prototype.refreshlessUpdateHtmlHead()
 * defined in js/refreshless.js
 *
 * @ingroup ajax
 */
class RefreshlessUpdateHtmlHeadCommand implements CommandInterface {

  /**
   * Constructs an RefreshlessUpdateHtmlHeadCommand object.
   *
   * @param string $title
   *   The page title, to be set as <title> in the HTML <head>.
   * @param string $head_markup
   *   The HTML <head> markup.
   */
  public function __construct(
    protected string $title,
    protected string $headMarkup,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    return [
      'command' => 'refreshlessUpdateHtmlHead',
      'title' => $this->title,
      'headMarkup' => $this->headMarkup,
    ];
  }

}
