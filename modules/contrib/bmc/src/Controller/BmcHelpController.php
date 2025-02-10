<?php

namespace Drupal\bmc\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for the help route.
 */
class BmcHelpController extends ControllerBase {

  /**
   * Displays help content for the module.
   *
   * @return array
   *   A render array containing the help content.
   */
  public function helpContent() {
    $content = [
      '#markup' => $this->t('<h2>About</h2><p>The Buy Me a Coffee module for Drupal allows users to easily integrate the Buy Me a Coffee donation platform into their websites, enabling visitors to support content creators by making small donations. This module simplifies the process of adding donation functionality to Drupal sites, empowering users to monetize their content and engage with their audience in a meaningful way.</p><p>You can thank the author and test the module <a href="https://www.buymeacoffee.com/darkdim" target="_blank">by following this link</a>.</p>'),
    ];

    return $content;
  }

}
