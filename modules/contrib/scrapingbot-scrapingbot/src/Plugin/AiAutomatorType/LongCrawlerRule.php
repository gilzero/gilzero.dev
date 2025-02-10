<?php

namespace Drupal\scrapingbot\Plugin\AiAutomatorType;

use Drupal\ai_automators\PluginBaseClasses\ExternalBase;
use Drupal\ai_automators\PluginInterfaces\AiAutomatorTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\scrapingbot\ScrapingBot;
use ivan_boring\Readability\Configuration;
use ivan_boring\Readability\Readability;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Helper class for the similar text crawlers.
 */
class LongCrawlerRule extends ExternalBase implements AiAutomatorTypeInterface, ContainerFactoryPluginInterface {

  /**
   * ScrapingBot API Caller.
   */
  protected ScrapingBot $scrapingBot;

  /**
   * Construct a text long field.
   *
   * @param array $configuration
   *   Inherited configuration.
   * @param string $plugin_id
   *   Inherited plugin id.
   * @param mixed $plugin_definition
   *   Inherited plugin definition.
   * @param \Drupal\scrapingbot\ScrapingBot $scrapingBot
   *   The ScrapingBot requester.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ScrapingBot $scrapingBot) {
    $this->scrapingBot = $scrapingBot;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('scrapingbot.api')
    );
  }

  /**
   * {@inheritDoc}
   */
  public $title = 'ScrapingBot: Crawler';

  /**
   * {@inheritDoc}
   */
  public function needsPrompt() {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function advancedMode() {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function placeholderText() {
    return "";
  }

  /**
   * {@inheritDoc}
   */
  public function allowedInputs() {
    return ['link'];
  }

  /**
   * {@inheritDoc}
   */
  public function extraAdvancedFormFields(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition, FormStateInterface $formState, array $defaultValues = []) {
    $form['automator_use_chrome'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Chrome'),
      '#description' => $this->t("Use Chrome when scraping"),
      '#default_value' => $defaultValues['automator_use_chrome'] ?? TRUE,
      '#weight' => -15,
    ];

    $form['automator_wait_for_network'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Wait for network'),
      '#description' => $this->t("Check if you want to wait for most ajax requests to finish until returning the Html content. This can slowdown or fail your scraping if some requests are never ending only use if really needed to get some price loaded asynchronously for example."),
      '#default_value' => $defaultValues['automator_wait_for_network'] ?? FALSE,
      '#weight' => -14,
    ];

    $form['automator_proxy_country'] = [
      '#type' => 'select',
      '#options' => ScrapingBot::$proxyCountries,
      '#title' => $this->t('Proxy Country'),
      '#description' => $this->t("Where should the scraping take place."),
      '#default_value' => $defaultValues['automator_proxy_country'] ?? 'US',
      '#weight' => -13,
    ];

    $form['automator_use_premium_proxy'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Premium Proxy'),
      '#description' => $this->t("Use Premium Proxy when scraping. This is VERY expensive."),
      '#default_value' => $defaultValues['automator_use_premium_proxy'] ?? FALSE,
      '#weight' => -12,
    ];

    $form['automator_crawler_mode'] = [
      '#type' => 'select',
      '#options' => [
        'all' => $this->t('Raw Dump'),
        'readibility' => $this->t('Article Segmentation (Readability)'),
        'selector' => $this->t('HTML Selector'),
      ],
      '#attributes' => [
        'name' => 'automator_crawler_mode',
      ],
      '#required' => TRUE,
      '#title' => $this->t('Crawler Mode'),
      '#description' => $this->t("Choose the mode to fetch the page. The options are:<ul>
      <li><strong>Raw Dump</strong> - This fetches the whole body.</li>
      <li><strong>Article Segmentation (Readability)</strong> - This uses the Readability segmentation algorithm of trying to figure out the main content.</li>
      <li><strong>HTML Selector</strong> - Use a tag type and optionally class or id to fetch parts. Can also remove tags.</li></ul>"),
      '#default_value' => $defaultValues['automator_crawler_mode'] ?? 'readibility',
      '#weight' => -10,
    ];

    $form['automator_crawler_tag'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Tag to get'),
      '#default_value' => $defaultValues['automator_crawler_tag'] ?? 'body',
      '#weight' => -10,
      '#states' => [
        'visible' => [
          ':input[name="automator_crawler_mode"]' => [
            'value' => 'selector',
          ],
        ],
      ],
    ];

    $form['automator_crawler_remove'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Tags to remove'),
      '#description' => $this->t('These are tags that are just garbage and can be removed. One per line.'),
      '#default_value' => $defaultValues['automator_crawler_remove'] ?? "style\nscript\n",
      '#weight' => -10,
      '#states' => [
        'visible' => [
          ':input[name="automator_crawler_mode"]' => [
            'value' => 'selector',
          ],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function generate(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Set options.
    $options['useChrome'] = $automatorConfig['use_chrome'];
    $options['waitForNetworkRequests'] = $automatorConfig['wait_for_network'];
    $options['proxyCountry'] = $automatorConfig['proxy_country'];
    $options['premiumProxy'] = $automatorConfig['use_premium_proxy'];

    $uris = $entity->get($automatorConfig['base_field'])->getValue();
    // Scrape.
    $values = [];
    foreach ($uris as $uri) {
      try {
        $rawHtml = $this->scrapingBot->scrapeRaw($uri['uri'], $options);

        // Return depending on crawler mode.
        switch ($automatorConfig['crawler_mode']) {
          case 'all':
            $values[] = $rawHtml;
            break;

          case 'readibility':
            $readability = new Readability(new Configuration());
            $done = $readability->parse($rawHtml);
            $values[] = $done ? $readability->getContent() : 'No scrape';
            break;

          case 'selector':
            $values[] = $this->getPartial($rawHtml, $automatorConfig['crawler_tag'], $automatorConfig['crawler_remove']);
            break;
        }
      }
      catch (\Exception $e) {
        $values[] = 'No HTML found';
      }
    }

    return $values;
  }

  /**
   * {@inheritDoc}
   */
  public function verifyValue(ContentEntityInterface $entity, $value, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Should be a string.
    if (!is_string($value)) {
      return FALSE;
    }
    // Otherwise it is ok.
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function storeValues(ContentEntityInterface $entity, array $values, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Then set the value.
    $entity->set($fieldDefinition->getName(), $values);
  }

  /**
   * Crude simple DOM traverser.
   *
   * @todo Use an actual traversal library.
   *
   * @var string $html
   *   The html.
   * @var string $tag
   *   The tag to get.
   * @var string $remove
   *   The tags to remove.
   *
   * @return string
   *   The cut out html.
   */
  public function getPartial($html, $tag = 'body', $remove = "") {
    // Get the whole HTML.
    $dom = new \DOMDocument();
    $dom->loadHTML($html);
    // Get the new one.
    $mock = new \DOMDocument();

    // Figure out if classes or id is involved.
    $parts = explode('.', $tag);
    $tag = isset($parts[1]) ? $parts[0] : $tag;
    $class = $parts[1] ?? '';
    $parts = explode('#', $tag);
    $tag = isset($parts[1]) ? $parts[0] : $tag;
    $id = $parts[1] ?? '';

    // Remove.
    foreach (explode("\n", $remove) as $tagRemove) {
      $removals = $dom->getElementsByTagName($tagRemove);
      for ($t = 0; $t < $removals->count(); $t++) {
        $dom->removeChild($removals->item($t));
      }
    }

    // Get the rest.
    $tags = $dom->getElementsByTagName($tag);

    for ($t = 0; $t < $tags->count(); $t++) {
      /** @var DOMNode */
      $tag = $tags->item($t);
      if ($class && $tag->getAttribute('class') != $class) {
        continue;
      }
      if ($id && $tag->getAttribute('id') != $id) {
        continue;
      }
      foreach ($tag->childNodes as $child) {
        $mock->appendChild($mock->importNode($child, TRUE));
      }
    }
    return $mock->saveHTML();
  }

}
