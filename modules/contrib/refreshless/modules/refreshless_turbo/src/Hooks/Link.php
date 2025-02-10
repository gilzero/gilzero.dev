<?php

declare(strict_types=1);

namespace Drupal\refreshless_turbo\Hooks;

use Drupal\hux\Attribute\Alter;

/**
 * Link element hook implementations.
 */
class Link {

  /**
   * Array of route names to disable Turbo prefetching on.
   *
   * @var string[]
   */
  protected array $prefetchDisabledRoutes = [
    'devel.cache_clear',
    'devel.run_cron',
    'entity.block.disable',
    'entity.block.enable',
    'entity.shortcut.link_delete_inline',
    'shortcut.link_add_inline',
    'system.admin_compact_page',
    'system.batch_page.html',
    'system.cron',
    'system.run_cron',
    'system.theme_install',
    'system.theme_set_default',
    'system.theme_uninstall',
    'update.manual_status',
    'user.logout',
    'user.logout.http',
  ];

  #[Alter('link')]
  /**
   * Implements hook_link_alter().
   *
   * This adds the 'data-turbo-prefetch="false"' attribute to various
   * administration links that would perform unexpected actions when prefetched
   * by Turbo.
   *
   * @see \Drupal\Core\Url
   *
   * @see https://www.drupal.org/project/refreshless/issues/3423544
   *   Issue detailing the problem.
   */
  public function alterDisablePrefetch(array &$variables): void {

    if (
      $variables['url']->isExternal() ||
      !$variables['url']->isRouted() ||
      !\in_array(
        $variables['url']->getRouteName(), $this->prefetchDisabledRoutes,
      )
    ) {
      return;
    }

    $variables['options']['attributes']['data-turbo-prefetch'] = 'false';

  }

}
