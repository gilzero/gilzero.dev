<?php

declare(strict_types=1);

namespace Drupal\refreshless_ajax\Hooks;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\SessionConfigurationInterface;
use Drupal\hux\Attribute\Hook;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Page attachments hook implementations.
 */
class PageAttachments implements ContainerInjectionInterface {

  /**
   * Hook constructor; saves dependencies.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The Symfony request stack.
   *
   * @param \Drupal\Core\Session\SessionConfigurationInterface $sessionConfiguration
   *   The Drupal session configuration generator.
   */
  public function __construct(
    protected readonly RequestStack $requestStack,
    protected readonly SessionConfigurationInterface $sessionConfiguration,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('session_configuration'),
    );
  }

  #[Hook('page_attachments')]
  /**
   * Attaches RefreshLess to the page.
   *
   * @see \hook_page_attachments()
   */
  public function attach(array &$attachments): void {

    // RefreshLess is only sensible when there is an actual session
    // (otherwise the entire page can be cached by the page_cache module, and
    // be sent more quickly than RefreshLess could).
    //
    // @see \Drupal\big_pipe\Render\Placeholder\BigPipeStrategy.
    $sessionExists = $this->sessionConfiguration->hasSession(
      $this->requestStack->getCurrentRequest(),
    );

    $attachments['#cache']['contexts'][] = 'session.exists';

    if ($sessionExists) {
      $attachments['#attached']['library'][] = 'refreshless_ajax/refreshless';
    }

  }

}
