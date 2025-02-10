<?php

namespace Drupal\artisan_styleguide\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\artisan_styleguide\ArtisanStyleguideBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Artisan styleguide routes.
 */
final class ArtisanStyleguideController extends ControllerBase {

  /**
   * The controller constructor.
   */
  public function __construct(
    private readonly ArtisanStyleguideBuilderInterface $styleguideBuilder,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('artisan_styleguide.builder'),
    );
  }

  /**
   * Builds main Artisan styleguide.
   */
  public function __invoke(): array {
    return $this->styleguideBuilder->build();
  }

}
