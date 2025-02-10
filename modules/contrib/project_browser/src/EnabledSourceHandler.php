<?php

namespace Drupal\project_browser;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\project_browser\Plugin\ProjectBrowserSourceManager;
use Drupal\project_browser\ProjectBrowser\Project;
use Drupal\project_browser\ProjectBrowser\ProjectsResultsPage;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Defines enabled source.
 */
class EnabledSourceHandler implements LoggerAwareInterface, EventSubscriberInterface {

  use LoggerAwareTrait;

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly ProjectBrowserSourceManager $pluginManager,
    private readonly ActivatorInterface $activator,
    private readonly KeyValueFactoryInterface $keyValueFactory,
  ) {}

  /**
   * Returns a key-value store for a particular source plugin.
   *
   * @param string $source_id
   *   The ID of a source plugin.
   *
   * @return \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   *   A key-value store for the specified source plugin.
   */
  private function keyValue(string $source_id): KeyValueStoreInterface {
    return $this->keyValueFactory->get("project_browser:$source_id");
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::SAVE => 'onConfigSave',
    ];
  }

  /**
   * Reacts when config is saved.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The event object.
   */
  public function onConfigSave(ConfigCrudEvent $event): void {
    $config = $event->getConfig();
    if ($config->getName() === 'project_browser.admin_settings' && $event->isChanged('enabled_sources')) {
      // Ensure that the cached plugin definitions stay in sync with the
      // enabled sources.
      $this->pluginManager->clearCachedDefinitions();

      // Clear stored data for the sources that have been disabled.
      $disabled_sources = array_diff(
        $config->getOriginal('enabled_sources') ?? [],
        $config->get('enabled_sources'),
      );
      array_walk($disabled_sources, $this->clearStorage(...));
    }
  }

  /**
   * Returns all plugin instances corresponding to the enabled_source config.
   *
   * @return \Drupal\project_browser\Plugin\ProjectBrowserSourceInterface[]
   *   Array of plugin instances.
   */
  public function getCurrentSources(): array {
    $plugin_instances = [];
    $config = $this->configFactory->get('project_browser.admin_settings');

    $plugin_ids = $config->get('enabled_sources');
    foreach ($plugin_ids as $plugin_id) {
      if (!$this->pluginManager->hasDefinition($plugin_id)) {
        // Ignore if the plugin does not exist, but log it.
        $this->logger?->warning('Project browser tried to load the enabled source %source, but the plugin does not exist. Make sure you have run update.php after updating the Project Browser module.', ['%source' => $plugin_id]);
      }
      else {
        $plugin_instances[$plugin_id] = $this->pluginManager->createInstance($plugin_id);
      }
    }

    return $plugin_instances;
  }

  /**
   * Returns projects that match a particular query, from specified source.
   *
   * @param string $source_id
   *   The ID of the source plugin to query projects from.
   * @param array $query
   *   (optional) The query to pass to the specified source.
   *
   * @return \Drupal\project_browser\ProjectBrowser\ProjectsResultsPage[]
   *   The result of the query, keyed by source plugin ID.
   */
  public function getProjects(string $source_id, array $query = []): array {
    // Cache only exact query, down to the page number.
    $cache_key = 'query:' . md5(Json::encode($query));

    $storage = $this->keyValue($source_id);

    $results = $storage->get($cache_key);
    // If $results is an array, it's a set of arguments to ProjectsResultsPage,
    // with a list of project IDs that we expect to be in the data store.
    if (is_array($results)) {
      $results[1] = array_map($this->getStoredProject(...), $results[1]);
      $results = new ProjectsResultsPage(...$results);
    }
    else {
      $results = $this->doQuery($source_id, $query);

      foreach ($results->list as $project) {
        // Prefix the local project ID with the source plugin ID, so we can
        // look it up unambiguously.
        $project->id = $source_id . '/' . $project->id;

        $storage->setIfNotExists($project->id, $project);
        // Add activation data to the project. This is volatile, which is why we
        // never store it.
        $this->getActivationData($project);
      }
      // If there were no query errors, store the results as a set of arguments
      // to ProjectsResultsPage.
      if (empty($results->error)) {
        $storage->set($cache_key, [
          $results->totalResults,
          array_column($results->list, 'id'),
          $results->pluginLabel,
          $source_id,
          $results->error,
        ]);
      }
    }
    return [$source_id => $results];
  }

  /**
   * Queries the specified source.
   *
   * @param string $source_id
   *   The ID of the source plugin to query projects from.
   * @param array $query
   *   (optional) The query to pass to the specified source.
   *
   * @return \Drupal\project_browser\ProjectBrowser\ProjectsResultsPage
   *   The results of the query.
   *
   * @see \Drupal\project_browser\Plugin\ProjectBrowserSourceInterface::getProjects()
   */
  private function doQuery(string $source_id, array $query = []): ProjectsResultsPage {
    $query['categories'] ??= '';

    $enabled_sources = $this->getCurrentSources();
    assert(array_key_exists($source_id, $enabled_sources));
    return $enabled_sources[$source_id]->getProjects($query);
  }

  /**
   * Looks up a previously stored project by its ID.
   *
   * @param string $id
   *   The project ID. See ::getProjects() for where this is set.
   *
   * @return \Drupal\project_browser\ProjectBrowser\Project
   *   The project object, with activation status and commands added.
   *
   * @throws \RuntimeException
   *   Thrown if the project is not found in the non-volatile data store.
   */
  public function getStoredProject(string $id): Project {
    [$source_id] = explode('/', $id, 2);
    $project = $this->keyValue($source_id)->get($id) ?? throw new \RuntimeException("Project '$id' was not found in non-volatile storage.");
    $this->getActivationData($project);
    return $project;
  }

  /**
   * Adds activation data to a project object.
   *
   * @param \Drupal\project_browser\ProjectBrowser\Project $project
   *   The project object.
   */
  private function getActivationData(Project $project): void {
    // The project's activator is the source of truth about the status of
    // the project with respect to the current site.
    $project->status = $this->activator->getStatus($project);
    // The activator is responsible for generating the instructions.
    $project->commands = $this->activator->getInstructions($project);
    // Give the front-end the ID of the source plugin that exposed this project.
    [$project->source] = explode('/', $project->id, 2);
  }

  /**
   * Clears the key-value store so it can be re-fetched.
   *
   * @param string|null $source_id
   *   (optional) The ID of the source for which data should be cleared. If
   *   NULL, stored data is cleared for all enabled sources. Defaults to NULL.
   */
  public function clearStorage(?string $source_id = NULL): void {
    if ($source_id) {
      $this->keyValue($source_id)->deleteAll();
    }
    else {
      foreach ($this->getCurrentSources() as $source) {
        $this->clearStorage($source->getPluginId());
      }
    }
  }

}
