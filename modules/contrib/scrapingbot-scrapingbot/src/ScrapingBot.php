<?php

namespace Drupal\scrapingbot;

use Drupal\scrapingbot\Form\ScrapingBotConfigForm;
use Drupal\Core\Config\ConfigFactory;
use Drupal\key\KeyRepositoryInterface;
use GuzzleHttp\Client;

/**
 * ScrapingBot API creator.
 */
class ScrapingBot {

  /**
   * The possible countries to proxy from.
   */
  public static array $proxyCountries = [
    "AE" => "United Arab Emirates",
    "AL" => "Albania",
    "AM" => "Armenia",
    "AR" => "Argentina",
    "AT" => "Austria",
    "AU" => "Australia",
    "AZ" => "Azerbaijan",
    "BA" => "Bosnia and Herzegovina",
    "BD" => "Bangladesh",
    "BE" => "Belgium",
    "BG" => "Bulgaria",
    "BO" => "Bolivia",
    "BR" => "Brazil",
    "BY" => "Belarus",
    "CA" => "Canada",
    "CH" => "Switzerland",
    "CL" => "Chile",
    "CN" => "China",
    "CO" => "Colombia",
    "CR" => "Costa Rica",
    "CY" => "Cyprus",
    "CZ" => "Czech Republic",
    "DE" => "Germany",
    "DK" => "Denmark",
    "DO" => "Dominican Republic",
    "EC" => "Ecuador",
    "EE" => "Estonia",
    "EG" => "Egypt",
    "ES" => "Spain",
    "FI" => "Finland",
    "FK" => "Falkland Islands",
    "FR" => "France",
    "GB" => "United Kingdom",
    "GE" => "Georgia",
    "GR" => "Greece",
    "GS" => "South Georgia and the South Sandwich Islands",
    "HK" => "Hong Kong",
    "HR" => "Croatia",
    "HU" => "Hungary",
    "ID" => "Indonesia",
    "IE" => "Ireland",
    "IL" => "Israel",
    "IM" => "Isle of Man",
    "IN" => "India",
    "IQ" => "Iraq",
    "IS" => "Iceland",
    "IT" => "Italy",
    "JM" => "Jamaica",
    "JO" => "Jordan",
    "JP" => "Japan",
    "KE" => "Kenya",
    "KG" => "Kyrgyzstan",
    "KH" => "Cambodia",
    "KR" => "South Korea",
    "KW" => "Kuwait",
    "KZ" => "Kazakhstan",
    "LA" => "Laos",
    "LK" => "Sri Lanka",
    "LT" => "Lithuania",
    "LU" => "Luxembourg",
    "LV" => "Latvia",
    "MA" => "Morocco",
    "MD" => "Moldova",
    "MK" => "North Macedonia",
    "MS" => "Montserrat",
    "MX" => "Mexico",
    "MY" => "Malaysia",
    "NG" => "Nigeria",
    "NL" => "Netherlands",
    "NO" => "Norway",
    "NZ" => "New Zealand",
    "OM" => "Oman",
    "PA" => "Panama",
    "PE" => "Peru",
    "PH" => "Philippines",
    "PK" => "Pakistan",
    "PL" => "Poland",
    "PT" => "Portugal",
    "RO" => "Romania",
    "RS" => "Serbia",
    "RU" => "Russia",
    "SA" => "Saudi Arabia",
    "SE" => "Sweden",
    "SG" => "Singapore",
    "SI" => "Slovenia",
    "SK" => "Slovakia",
    "SL" => "Sierra Leone",
    "TH" => "Thailand",
    "TJ" => "Tajikistan",
    "TM" => "Turkmenistan",
    "TN" => "Tunisia",
    "TR" => "Turkey",
    "TW" => "Taiwan",
    "UA" => "Ukraine",
    "US" => "United States",
    "UZ" => "Uzbekistan",
    "VN" => "Vietnam",
    "ZA" => "South Africa",
  ];

  /**
   * The http client.
   */
  protected Client $client;

  /**
   * Username.
   */
  private string $userName;

  /**
   * API Key.
   */
  private string $apiKey;

  /**
   * The base path.
   */
  private string $basePath = 'http://api.scraping-bot.io/scrape/';

  /**
   * Constructs a new ScrapingBot object.
   *
   * @param \GuzzleHttp\Client $client
   *   Http client.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The config factory.
   */
  public function __construct(Client $client, ConfigFactory $configFactory, KeyRepositoryInterface $keyRepository) {
    $this->client = $client;
    $config = $configFactory->get(ScrapingBotConfigForm::CONFIG_NAME);
    $this->userName = $config->get('user_name') ?? '';
    $key = $config->get('api_key') ?? '';
    if ($key) {
      $this->apiKey = $keyRepository->getKey($key)->getKeyValue();
    }
  }

  /**
   * Scrape raw.
   *
   * @param string $url
   *   The url.
   * @param array $options
   *   Options to pass on to the scraper.
   *
   * @return string
   *   The HTML.
   */
  public function scrapeRaw($url, $options = []) {
    if (!$this->apiKey || !$this->userName) {
      return [];
    }
    $data['url'] = $url;
    // Solving boolean problem.
    $data['options']['useChrome'] = $options['useChrome'] ? TRUE : FALSE;
    $data['options']['premiumProxy'] = $options['premiumProxy'] ? TRUE : FALSE;
    $data['options']['proxyCountry'] = $options['proxyCountry'] ?? 'US';
    $data['options']['waitForNetworkRequests'] = $options['waitForNetworkRequests'] ? TRUE : FALSE;
    return (string) $this->makeRequest("raw-html", [], 'POST', $data);
  }

  /**
   * Make ScrapingBot call.
   *
   * @param string $path
   *   The path.
   * @param array $query_string
   *   The query string.
   * @param string $method
   *   The method.
   * @param string $json
   *   JSON params.
   *
   * @return string|object
   *   The return response.
   */
  protected function makeRequest($path, array $query_string = [], $method = 'GET', $json = '') {
    $apiEndPoint = $this->basePath . $path;
    $apiEndPoint .= count($query_string) ? '?' . http_build_query($query_string) : '';

    // We can wait some.
    $options['connect_timeout'] = 60;
    $options['read_timeout'] = 60;
    $options['timeout'] = 60;

    // Headers.
    $options['auth'] = [
      $this->userName,
      $this->apiKey,
    ];
    $options['headers']['Content-Type'] = 'application/json';

    if ($json) {
      $options['body'] = json_encode($json);
    }

    $res = $this->client->request($method, $apiEndPoint, $options);
    return $res->getBody();
  }

}
