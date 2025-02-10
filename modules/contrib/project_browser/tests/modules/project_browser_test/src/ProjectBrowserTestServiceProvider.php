<?php

declare(strict_types=1);

namespace Drupal\project_browser_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\project_browser\InstallReadiness;

/**
 * Overrides the module installer service.
 */
class ProjectBrowserTestServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container): void {
    // The InstallReadiness service is defined by ProjectBrowserServiceProvider
    // if Package Manager is installed.
    if ($container->hasDefinition(InstallReadiness::class)) {
      $container->register(TestInstallReadiness::class, TestInstallReadiness::class)
        ->setAutowired(TRUE)
        ->addTag('event_subscriber');
    }
  }

}
