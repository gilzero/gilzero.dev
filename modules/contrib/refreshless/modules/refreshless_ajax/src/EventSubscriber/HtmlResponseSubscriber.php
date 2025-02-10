<?php

declare(strict_types=1);

namespace Drupal\refreshless_ajax\EventSubscriber;

use Drupal\Core\Render\HtmlResponse;
use Drupal\refreshless_ajax\RefreshlessPageStateInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Response subscriber to process HTML responses for RefreshLess.
 *
 * All modifications this makes are harmless: they don't ever break HTML or
 * normal site operation. Even if JavaScript is turned.
 */
class HtmlResponseSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a HtmlResponseSubscriber object.
   *
   * @param \Drupal\refreshless_ajax\RefreshlessPageStateInterface $refreshlessPageState
   *   The RefreshLess page state service.
   */
  public function __construct(
    protected readonly RefreshlessPageStateInterface $refreshlessPageState,
  ) {}

  /**
   * Processes HTML responses to allow RefreshLess' JavaScript to work.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onRespond(ResponseEvent $event): void {
    $response = $event->getResponse();
    if (!$response instanceof HtmlResponse) {
      return;
    }

    $this->wrapHeadAttachmentsInMarkers($response);
    $this->initializeRefreshlessPageState($response);
  }

  /**
   * Wraps the <head> attachments placeholder in markers.
   *
   * @param \Drupal\Core\Render\HtmlResponse $response
   *   The HTML response to update.
   *
   * @see \Drupal\refreshless_ajax\Ajax\RefreshlessUpdateHtmlHeadCommand
   */
  protected function wrapHeadAttachmentsInMarkers(
    HtmlResponse $response,
  ): void {
    // Wrap the head placeholder with a marker before and after,
    // because the JS for RefreshlessUpdateHtmlHeadCommand needs to be able to
    // replace that markup when navigating using RefreshLess.
    $attachments = $response->getAttachments();
    if (isset($attachments['html_response_attachment_placeholders']['head'])) {
      $head_placeholder = $attachments['html_response_attachment_placeholders']['head'];
      $content = $response->getContent();
      $content = str_replace($head_placeholder, '<meta name="refreshless-head-marker-start" />' . "\n" . $head_placeholder . "\n" . '<meta name="refreshless-head-marker-stop" />', $content);
      $response->setContent($content);
    }
  }

  /**
   * Initializes RefreshLess page state.
   *
   * @param \Drupal\Core\Render\HtmlResponse $response
   *   The HTML response to update.
   */
  protected function initializeRefreshlessPageState(
    HtmlResponse $response,
  ): void {
    $response->addAttachments(['drupalSettings' => ['refreshlessPageState' => $this->refreshlessPageState->build($response->getCacheableMetadata())]]);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Run just before \Drupal\Core\EventSubscriber\HtmlResponseSubscriber
    // (priority 0), which invokes
    // \Drupal\Core\Render\HtmlResponseAttachmentsProcessor::processAttachments,
    // which is what processes all attachments into a final HTML response.
    $events[KernelEvents::RESPONSE][] = ['onRespond', 1];

    return $events;
  }

}
