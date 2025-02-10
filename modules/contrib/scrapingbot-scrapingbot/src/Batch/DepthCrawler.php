<?php

namespace Drupal\scrapingbot\Batch;

use ivan_boring\Readability\Configuration;
use ivan_boring\Readability\Readability;

/**
 * The Link Crawler for batch processing.
 */
class DepthCrawler {

  /**
   * Start the crawl.
   *
   * @param object $entity
   *   The entity.
   * @param string $link
   *   The link to crawl.
   * @param array $config
   *   The config.
   * @param object $fieldDefinition
   *   The field definition.
   * @param string $mode
   *   The mode.
   * @param array $context
   *   The context.
   */
  public static function startCrawl($entity, $link, array $config, $fieldDefinition, $mode, &$context) {
    if (!isset($context['results']['links_left'])) {
      $context['results']['links_left'] = $config['links_left'] ?? 1;
    }
    if (!isset($context['results']['found_links'])) {
      $context['results']['found_links'] = $config['found_links'] ?? [];
    }
    $context['message'] = 'Crawling ' . $link;

    $context['results']['links_left']--;
    // If we have already scraped this link, return.
    if (in_array($link, $context['results']['found_links'] ?? [])) {
      return;
    }
    if (!empty($config['cool_down'])) {
      // Milliseconds.
      usleep($config['cool_down'] * 1000);
    }

    // Scrape the link.
    $options['useChrome'] = $config['use_chrome'];
    $options['waitForNetworkRequests'] = $config['wait_for_network'];
    $options['proxyCountry'] = $config['proxy_country'];
    $options['premiumProxy'] = $config['use_premium_proxy'];
    $rawHtml = \Drupal::service('scrapingbot.api')->scrapeRaw($link, $options);
    $value = '';
    switch ($config['crawler_mode']) {
      case 'all':
        $value = $rawHtml;
        break;

      case 'readibility':
        $readability = new Readability(new Configuration());
        $done = $readability->parse($rawHtml);
        $value = $done ? $readability->getContent() : 'No scrape';
        break;

      case 'selector':
        $value = \Drupal::service('scrapingbot.crawler_helper')->getPartial($rawHtml, $config['crawler_tag'], $config['crawler_remove']);
        break;
    }
    // Put url on top if wanted.
    if ($config['url_on_top'] && $value) {
      $value = 'Source: ' . $link . "<br>\n" . $value;
    }
    if ($value) {
      $context['results']['found_texts'][] = $value;
    }
    $context['results']['found_links'][] = $link;

    // If its wanted to just do inside the body, we get the body only using regex.
    if ($config['body_only']) {
      preg_match('/<body[^>]*>(.*?)<\/body>/is', $rawHtml, $body);
      if (!empty($body[1])) {
        $rawHtml = $body[1];
      }
    }
    // Parse the html, collecting links starting with http* or / using regex.
    $newOperations = [];
    $batch = \batch_get();
    preg_match_all('/href=["\']?([^"\'>]+)["\']?/', $rawHtml, $matches);
    if (!empty($matches[1])) {
      $links = $matches[1];
      $links = \Drupal::service('scrapingbot.crawler_helper')->cleanLinks($links, $link, $config);

      $config['depth']--;
      // If we have links, scrape them.
      $config['links_left'] = $context['results']['links_left'] + count($links);
      $config['found_links'] = $context['results']['found_links'];
      foreach ($links as $link) {
        // Get the extension if it has one.
        $extension = pathinfo($link, PATHINFO_EXTENSION);
        // If its in found links, we don't scrape it.
        if (in_array($link, $config['found_links'])) {
          continue;
        }
        // If it has no extension or if it is a web page, we scrape it.
        if (in_array($extension, ['html', 'htm', 'asp', 'php']) || empty($extension)) {
          // Add to the batch job.
          $context['results']['links_left']++;
          $newOperations[] = [
            'Drupal\scrapingbot\Batch\DepthCrawler::startCrawl',
            [$entity, $link, $config, $fieldDefinition, $mode],
          ];
        }
      }
      if (!empty($newOperations)) {
        $batch['operations'] = !empty($batch['operations']) ? array_merge_recursive($batch['operations'], $newOperations) : $newOperations;
        \batch_set($batch);
      }
    }

    $jobsLeft = FALSE;
    if (!empty($batch['operations'])) {
      foreach ($batch['operations'] as $operation) {
        if ($operation[0] == 'Drupal\scrapingbot\Batch\DepthCrawler::startCrawl') {
          $jobsLeft = TRUE;
          break;
        }
      }
    }
    // If there are no jobs left, save it.
    if (!$jobsLeft) {
      $saveTexts = [];
      foreach ($context['results']['found_texts'] as $foundText) {
        if ($mode == 'string') {
          $saveTexts[] = $foundText;
        } else {
          $saveTexts[] = ['value' => $foundText, 'format' => \Drupal::service('scrapingbot.crawler_helper')->getTextFormat($fieldDefinition)];
        }
      }
      $entity->set($fieldDefinition->getName(), $saveTexts);
      $entity->save();
    }
  }

}
