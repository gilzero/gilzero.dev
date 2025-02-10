<?php

namespace Drupal\hide_submit\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class HideSubmitController extends ControllerBase {

  protected $formBuilder;

  public function __construct(FormBuilderInterface $form_builder) {
    $this->formBuilder = $form_builder;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
    );
  }

  /**
   *
   */
  public function settings(Request $request) {
    // Render settings form here.
    $form = $this->formBuilder->getForm('Drupal\hide_submit\Form\HideSubmitSettingsForm');
    return [
      '#type' => 'markup',
      '#markup' => \Drupal::service('renderer')->render($form),
    ];
  }

}
