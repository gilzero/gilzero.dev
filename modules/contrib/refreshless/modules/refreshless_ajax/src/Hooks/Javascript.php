<?php

declare(strict_types=1);

namespace Drupal\refreshless_ajax\Hooks;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\EventSubscriber\AjaxResponseSubscriber;
use Drupal\hux\Attribute\Alter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * JavaScript hook implementations.
 */
class Javascript implements ContainerInjectionInterface {

  /**
   * Hook constructor; saves dependencies.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The Symfony request stack.
   */
  public function __construct(
    protected readonly RequestStack $requestStack,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
    );
  }

  #[Alter('js_settings')]
  /**
   * Implements \hook_js_settings_alter().
   *
   * @see system_js_settings_alter()
   *
   * @see \Drupal\Core\Ajax\AjaxResponseAttachmentsProcessor::buildAttachmentsCommands()
   */
  public function alter(
    array &$settings, AttachedAssetsInterface $assets,
  ): void {

    /** @var \Symfony\Component\HttpFoundation\Request */
    $request = $this->requestStack->getMainRequest();

    // Unlike for generic/typical Ajax responses, RefreshLess responses in fact
    // do change the path. But AjaxResponseAttachmentsProcessor removes the
    // path settings. So we copy the path settings into another subtree of the
    // settings array, to allow path settings to be sent to the client for
    // RefreshLess responses.
    //
    // Also do this when there is no wrapper format (i.e. when simply sending a
    // HTML response) to ensure drupalSettings behaves consistently regardless
    // of full page loads or RefreshLess page loads.
    if (!$request->query->has(MainContentViewSubscriber::WRAPPER_FORMAT) || $request->query->get(MainContentViewSubscriber::WRAPPER_FORMAT) === 'drupal_refreshless') {
      $settings['refreshless']['path'] = $settings['path'];
      // When following redirects, XHR provides no way of knowing the final URL.
      // This allows the RefreshLess JavaScript to calculate it.
      //
      // @see \Drupal\refreshless_ajax\EventSubscriber\RedirectResponseSubscriber
      $query = UrlHelper::buildQuery(UrlHelper::filterQueryParameters($request->query->all(), [
        MainContentViewSubscriber::WRAPPER_FORMAT,
        AjaxResponseSubscriber::AJAX_REQUEST_PARAMETER,
        'js',
        'ajax_page_state',
        'refreshless_page_state',
      ]));
      $settings['refreshless']['path']['refreshless_absolute_url'] = $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo() . ($query ? '?' . $query : '');

    }

  }

}
