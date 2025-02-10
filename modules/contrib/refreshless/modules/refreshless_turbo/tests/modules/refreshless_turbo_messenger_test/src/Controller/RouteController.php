<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo_messenger_test\Controller;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\refreshless_turbo\Value\RefreshlessRequest;
use Drupal\refreshless_turbo\Value\ReloadRequest;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Route controller for RefreshLess Turbo decorated messenger service tests.
 */
class RouteController implements ContainerInjectionInterface {

  use AutowireTrait;

  use StringTranslationTrait;

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation service.
   */
  public function __construct(
    protected readonly MessengerInterface $messenger,
    protected readonly RequestStack $requestStack,
    TranslationInterface $stringTranslation,
  ) {

    // StringTranslationTrait will use \Drupal::service('string_translation') to
    // fetch the service if it isn't set to the property, so by setting it here
    // we make use of dependency injection best practices.
    $this->setStringTranslation($stringTranslation);

  }

  /**
   * Route callback.
   *
   * @param string $linkToRoute
   *   The route name to generate a link to.
   *
   * @param string $message
   *   Message to display.
   *
   * @param $linkContent
   *   The content to render in the generated link.
   *
   * @return array
   *   A render array.
   */
  public function route(
    string $linkToRoute, string $message, string $linkContent,
  ): array {

    // Important: we only want to output the messages during a RefeshLess
    // request and not during a reload (full page load) to correctly test the
    // decorated messenger service. If we output these on both types of
    // requests, it completely defeats the purpose of the test.
    if ((new RefreshlessRequest(
      $this->requestStack->getCurrentRequest(),
    ))->isTurbo()) {

      $this->messenger->addStatus($message);
      $this->messenger->addWarning($message);
      $this->messenger->addError($message);

    }

    return [
      'link' => [
        '#type'   => 'link',
        '#title'  => $linkContent,
        '#url'    => Url::fromRoute($linkToRoute),
      ],
      'info' => [
        '#theme' => 'item_list',
        '#items'  => [
          'Reload? ' . ((new ReloadRequest(
            $this->requestStack->getCurrentRequest(),
          ))->isReload() ? 'true' : 'false'),
          'RefreshLess request? ' . ((new RefreshlessRequest(
            $this->requestStack->getCurrentRequest(),
          ))->isTurbo() ? 'true' : 'false'),
        ],
      ],
      'headers' => [
        '#type' => 'container',
        'heading' => [
          '#type'   => 'html_tag',
          '#tag'    => 'h2',
          '#value'  => $this->t('Request headers'),
        ],
        'data' => [
          '#type'   => 'html_tag',
          '#tag'    => 'pre',
          '#value'  => (string) $this->requestStack->getCurrentRequest()
            ->headers,
        ],
      ],
      // We don't want this route to be cached. This shouldn't needed during
      // tests, but useful if installing this module in a dev environment where
      // caching is enabled to replicate production as much as possible.
      '#cache' => ['max-age' => 0],
    ];

  }

}
