<?php

namespace Drupal\scrapingbot\Plugin\AiAutomatorType;

use Drupal\ai_automators\PluginBaseClasses\ExternalBase;
use Drupal\ai_automators\PluginInterfaces\AiAutomatorTypeInterface;
use Drupal\scrapingbot\CrawlerHelper;
use Drupal\scrapingbot\ScrapingBot;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use ivan_boring\Readability\Configuration;
use ivan_boring\Readability\Readability;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Helper class to depth scrape links into a field.
 */
class DepthCrawlerRule extends ExternalBase implements AiAutomatorTypeInterface, ContainerFactoryPluginInterface {

  /**
   * ScrapingBot API Caller.
   */
  private ScrapingBot $scrapingBot;

  /**
   * The Crawler Helper.
   */
  private CrawlerHelper $crawlerHelper;

  /**
   * The links found so far, so it doesn't rerun links.
   */
  private array $foundLinks = [];

  /**
   * The stored texts.
   */
  private array $foundHtmls = [];

  /**
   * We need the Interpolator config globally.
   */
  private array $automatorConfig;

  /**
   * We need crawler options globally.
   */
  private array $options;

  /**
   * Construct a boolean field.
   *
   * @param array $configuration
   *   Inherited configuration.
   * @param string $plugin_id
   *   Inherited plugin id.
   * @param mixed $plugin_definition
   *   Inherited plugin definition.
   * @param \Drupal\scrapingbot\ScrapingBot $scrapingBot
   *   The ScrapingBot requester.
   * @param \Drupal\scrapingbot\CrawlerHelper $crawlerHelper
   *   The Crawler Helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ScrapingBot $scrapingBot, CrawlerHelper $crawlerHelper) {
    $this->scrapingBot = $scrapingBot;
    $this->crawlerHelper = $crawlerHelper;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('scrapingbot.api'),
      $container->get('scrapingbot.crawler_helper')
    );
  }

  /**
   * {@inheritDoc}
   */
  public $title = 'ScrapingBot: Depth Crawler';

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
    $form['automator_depth'] = [
      '#type' => 'number',
      '#title' => $this->t('Depth'),
      '#description' => $this->t('How many levels deep should the crawler go.'),
      '#default_value' => $defaultValues['automator_depth'] ?? 1,
      '#weight' => -20,
    ];

    $form['automator_include_source_url'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include Source URL'),
      '#description' => $this->t('Include the source URL as one of the links.'),
      '#default_value' => $defaultValues['automator_include_source_url'] ?? TRUE,
      '#weight' => -19,
    ];

    $form['automator_host_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Host Only'),
      '#description' => $this->t('Only crawl the host of the base link. DO NOT UNCHECK THIS UNLESS YOU KNOW WHAT YOU ARE DOING.'),
      '#default_value' => $defaultValues['automator_host_only'] ?? TRUE,
      '#weight' => -19,
    ];

    $form['automator_url_on_top'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('URL on top'),
      '#description' => $this->t('Put the URL on top of the scraped content field.'),
      '#default_value' => $defaultValues['automator_url_on_top'] ?? TRUE,
      '#weight' => -19,
    ];

    $form['automator_body_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Body Only'),
      '#description' => $this->t('Only crawl the body of the base link.'),
      '#default_value' => $defaultValues['automator_body_only'] ?? TRUE,
      '#weight' => -19,
    ];

    $defaultPages = [
      'privacy',
      'privacy-policy',
      'privacy_policy',
      'terms',
      'terms-of-service',
      'terms_of_service',
      'terms-and-conditions',
      'terms_and_conditions',
      'disclaimers',
      'disclaimer',
      'cookies',
      'cookie-policy',
      'cookie_policy',
      'login',
      'register',
    ];

    $form['automator_exclude_pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Exclude Pages'),
      '#description' => $this->t('Comma separated list of pages to exclude.'),
      '#default_value' => $defaultValues['automator_exclude_pages'] ?? implode(', ', $defaultPages),
      '#weight' => -19,
    ];

    $form['automator_include_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Include Pattern'),
      '#description' => $this->t('Only include links that match this regex pattern. Leave empty to include all.'),
      '#default_value' => $defaultValues['automator_include_pattern'] ?? '',
      '#weight' => -19,
    ];

    $form['automator_exclude_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Exclude Pattern'),
      '#description' => $this->t('Exclude links that match this regex pattern. Leave empty to exclude none.'),
      '#default_value' => $defaultValues['automator_exclude_pattern'] ?? '',
      '#weight' => -19,
    ];

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

    $form['automator_cool_down'] = [
      '#type' => 'number',
      '#title' => $this->t('Cool Down'),
      '#description' => $this->t('How many milliseconds to wait between each request. Don\'t take down websites by spamming them.'),
      '#default_value' => $defaultValues['automator_cool_down'] ?? 500,
      '#weight' => -11,
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
    // Set the config.
    $this->automatorConfig = $automatorConfig;
    // Set options.
    $options['useChrome'] = $automatorConfig['use_chrome'];
    $options['waitForNetworkRequests'] = $automatorConfig['wait_for_network'];
    $options['proxyCountry'] = $automatorConfig['proxy_country'];
    $options['premiumProxy'] = $automatorConfig['use_premium_proxy'];
    // Set the options globally.
    $this->options = $options;

    // Take all input links.
    foreach ($entity->{$automatorConfig['base_field']} as $link) {
      // A link is found.
      if (!empty($link->uri)) {
        // If its batch mode.
        if ($automatorConfig['worker_type'] == 'batch') {
          $batch = \batch_get();
          $batch['operations'][] = [
            'Drupal\scrapingbot\Batch\DepthCrawler::startCrawl',
            [$entity, $link->uri, $automatorConfig, $fieldDefinition, $this->getMode()],
          ];
        } else {
          $this->scrapeLink($link->uri, $automatorConfig['depth']);
        }
      }
    }
    if ($automatorConfig['worker_type'] == 'batch' && !empty($batch)) {
      \batch_set($batch);
      return [];
    } else {
      return $this->foundHtmls;
    }
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
   * Recursive function to scrape links.
   *
   * @param string $link
   *   The link to scrape.
   * @param int $depth
   *   The current depth.
   */
  private function scrapeLink($link, $depth) {
    if (!empty($this->automatorConfig['cool_down'])) {
      // Milliseconds.
      usleep($this->automatorConfig['cool_down'] * 1000);
    }
    // If we have already scraped this link, return.
    if (in_array($link, $this->foundLinks)) {
      return;
    }
    // Add it to scraped list.
    $this->foundLinks[] = $link;
    // Scrape the link.
    $rawHtml = $this->scrapingBot->scrapeRaw($link, $this->options);
    // Return depending on crawler mode.
    $value = '';
    switch ($this->automatorConfig['crawler_mode']) {
      case 'all':
        $value = $rawHtml;
        break;

      case 'readibility':
        $readability = new Readability(new Configuration());
        $done = $readability->parse($rawHtml);
        $value = $done ? $readability->getContent() : 'No scrape';
        break;

      case 'selector':
        $value = $this->crawlerHelper->getPartial($rawHtml, $this->automatorConfig['crawler_tag'], $this->automatorConfig['crawler_remove']);
        break;
    }
    // Put url on top if wanted.
    if ($this->automatorConfig['url_on_top'] && $value) {
      $value = 'Source: ' . $link . "<br>\n" . $value;
    }
    if ($value) {
      $this->foundHtmls[] = $value;
    }

    // If we are at the end, return.
    if ($depth == 0) {
      return;
    }
    // If its wanted to just do inside the body, we get the body only using regex.
    if ($this->automatorConfig['body_only']) {
      preg_match('/<body[^>]*>(.*?)<\/body>/is', $rawHtml, $body);
      if (!empty($body[1])) {
        $rawHtml = $body[1];
      }
    }
    // Parse the html, collecting links starting with http* or / using regex.
    preg_match_all('/href=["\']?([^"\'>]+)["\']?/', $rawHtml, $matches);
    if (!empty($matches[1])) {
      $links = $matches[1];
      $links = $this->crawlerHelper->cleanLinks($links, $link, $this->automatorConfig);

      foreach ($links as $link) {
        // Get the extension if it has one.
        $extension = pathinfo($link, PATHINFO_EXTENSION);
        // If it has no extension or if it is a web page, we scrape it.
        if (in_array($extension, ['html', 'htm', 'asp', 'php']) || empty($extension)) {
          $this->scrapeLink($link, $depth - 1);
        }
      }
    }
  }

  /**
   * Get the mode of the batch job.
   *
   * @return string
   *   Text or string.
   */
  public function getMode() {
    return 'text';
  }

}
