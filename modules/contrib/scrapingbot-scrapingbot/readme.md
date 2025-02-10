
# ScrapingBot

## What is this

[ScrapingBot](https://www.scraping-bot.io/) is a service that can take a link and scrape it using an actual browser from almost anywhere in the world. It also offers depth scraping/crawling of an entire website using the AI Automator in the [AI module](https://www.drupal.org/project/ai).

ScrapingBot is a module that currently have two things available for it. The one thing is a service where you can get scrape a url with the [ScrapingBot](https://www.scraping-bot.io/) service for any third party module that would want to use it for contextualize AI calls or scrape website in general using a real browser, without having to setup any services on your server.

The other core feature is that it has one AI Automator type for the AI Automator module that can be found in the [AI module](https://www.drupal.org/project/ai). It makes it possible to take any link field and fill out any text field or even image fields. It also offers depth scraping, meaning that you can fill out multiple text fields into a fully crawled/scraped website. This can work in an Automator workflow/blueprint to make powerful end products.

For more information on how to use the AI Automator (previously AI Interpolator), check https://workflows-of-ai.com.

Note that this is the follow up module of the AI Interpolator ScrapingBot and makes that module obsolete for Drupal 10.3+.

## Features
* Scrape any url into text.
* Crawl whole webpages from a initial url.
* Scrape a specific selector/DOM object.
* Scrape only the actual text content.
* Scrape client side rendered webpages using a real browser.
* Scrape from almost 100 different countries.
* Scrape at an extra cost using premium proxies, meaning IP ranges that are not server based.

## Requirements
* Requires an account at [ScrapingBot](https://www.scraping-bot.io/). There is a free trial and if you use the code *DRUPALAI* you will get 10% off your first month.
* Requires the [Key module](https://www.drupal.org/project/key) for storing the API key.
* To use it, you need to use a third party module using the service. Currently its only usable with the AI Automator submodule of the [AI module](https://www.drupal.org/project/ai)

## How to use as AI Automator type
1. Install the [AI module](https://www.drupal.org/project/ai).
2. Install this module.
3. Visit /admin/config/scrapingbot/settings and add your username and api key from your ScrapingBot account.
4. Create some entity or node type with a link field. Set it to external links.
5. Create a Text Long, either formatted or unformatted.
6. Enable AI Automator checkbox and configure it.
7. Create an entity of the type you generated, add a link.
8. The text will be filled out from the scraped content using your settings.
## How to use the ScrapingBot service.
This is a code example on how you can get text for a url.

For the full configuration array, check: https://www.scraping-bot.io/web-scraping-documentation/advanced-options

```
$scrapingbot = \Drupal::service('scrapingbot.api');
// Config.
$config = [
  'proxyCountry' => 'DE',
];
// Get html response.
$html = $scrapingbot->scrapeRaw('https://www.reddit.com/r/drupal', $config);
```
