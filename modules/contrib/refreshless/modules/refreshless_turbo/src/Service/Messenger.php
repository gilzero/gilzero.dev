<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Service;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\refreshless_turbo\Service\RefreshlessTurboContextInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Decorated messenger service ensure messages are not lost if Turbo reloads.
 *
 * This is accomplished by saving a back up copy of the messages into a
 * secondary flash message bag on Turbo requests, and if a subsequent request
 * appears to be a Turbo reload request, we show any backed up messages on the
 * reload request; this effectively means that in such cases, we output messages
 * twice, but the first time won't be shown to the user because Turbo will
 * examine the response and do a reload instead of displaying it.
 *
 * @see \Drupal\Core\Messenger\MessengerInterface
 *
 * @see https://symfony.com/doc/current/session.html#flash-messages
 *
 * @see https://www.drupal.org/node/3109877
 *   Drupal core change record detailing how to register custom session bags.
 */
class Messenger implements MessengerInterface {

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $decorated
   *   The messenger service that we decorate.
   *
   * @param \Drupal\refreshless_turbo\Service\RefreshlessTurboContextInterface $refreshlessContext
   *   The RefreshLess Turbo context service.
   *
   * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $secondaryFlashBag
   *   Our secondary session flash message bag.
   */
  public function __construct(
    #[AutowireDecorated]
    protected readonly MessengerInterface $decorated,
    protected readonly RefreshlessTurboContextInterface $refreshlessContext,
    #[Autowire(service: 'session.refreshless_turbo_secondary_flash_bag')]
    protected readonly FlashBagInterface $secondaryFlashBag,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function addError($message, $repeat = false) {
    return $this->decorated->addError($message, $repeat);
  }

  /**
   * {@inheritdoc}
   */
  public function addMessage(
    $message, $type = self::TYPE_STATUS, $repeat = false,
  ) {
    return $this->decorated->addMessage($message, $type, $repeat);
  }

  /**
   * {@inheritdoc}
   */
  public function addStatus($message, $repeat = false) {
    return $this->decorated->addStatus($message, $repeat);
  }

  /**
   * {@inheritdoc}
   */
  public function addWarning($message, $repeat = false) {
    return $this->decorated->addWarning($message, $repeat);
  }

  /**
   * {@inheritdoc}
   */
  public function all() {
    return $this->decorated->all();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {

    // On a RefreshLess Turbo request, save a copy of all messages to our
    // secondary flash message bag. Note that we use MessengerInterface::all()
    // which doesn't delete any messages.
    if ($this->refreshlessContext->isRefreshlessRequest()) {

      $this->secondaryFlashBag->setAll($this->decorated->all());

    // If this is a reload request, pop any backed up messages in our secondary
    // flash message bag and add them as messages. They will be returned and
    // deleted in the call to the decorated MessengerInterface::deleteAll() at
    // the end of this method.
    } else if ($this->refreshlessContext->isReloadRequest()) {

      foreach ($this->secondaryFlashBag->all() as $type => $messagesByType) {

        foreach ($messagesByType as $i => $message) {
          $this->decorated->addMessage($message, $type);
        }

      }

    }

    return $this->decorated->deleteAll();

  }

  /**
   * {@inheritdoc}
   */
  public function deleteByType($type) {
    return $this->decorated->deleteByType($type);
  }

  /**
   * {@inheritdoc}
   */
  public function messagesByType($type) {
    return $this->decorated->messagesByType($type);
  }

}
