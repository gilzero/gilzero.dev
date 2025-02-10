<?php

namespace Drupal\postlight_parser;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Embera\Embera;
use fivefilters\Readability\Configuration;
use fivefilters\Readability\Readability;
use Graby\Graby;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;

/**
 * Url parser service.
 */
class UrlParserService {

  /**
   * UrlParser service constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   The file URL generator.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   Http client service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   Current User service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(protected readonly FileSystemInterface $fileSystem, protected readonly FileUrlGeneratorInterface $fileUrlGenerator, protected readonly MessengerInterface $messenger, protected readonly ClientInterface $httpClient, protected readonly AccountInterface $currentUser, protected readonly EntityTypeManagerInterface $entityTypeManager) {
  }

  /**
   * {@inheritDoc}
   */
  public function parser($settings): array {
    $save_image = $settings['save_image'];
    $parser = $settings['parser'];
    $url = $settings['url'];
    if (!filter_var($url, FILTER_VALIDATE_URL) !== FALSE) {
      return $settings;
    }
    $results = [];
    $urlInfo = parse_url($url);
    $host = $urlInfo['scheme'] . '://' . $urlInfo['host'];
    if (!empty($parser) && !empty($url)) {
      $command = "$parser-parser " . trim($url);
      if ($this->emberaSupport($urlInfo['host'])) {
        $embera = new Embera();
        $results = $embera->getUrlData([$url]);
        if (!empty($results)) {
          $parser = 'embera';
        }
      }
      $context = stream_context_create([
        "http" => [
          "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36",
        ],
      ]);
      switch ($parser) {
        case 'readability':
          // Use Readability php.
          $readability = new Readability(new Configuration());
          $html = file_get_contents($url, FALSE, $context);
          $done = !empty($html) ? $readability->parse($html) : FALSE;
          if (!empty($done)) {
            $settings['data'] = [
              "title" => $readability->getTitle(),
              "content" => $readability->getContent(),
              "excerpt" => $readability->getExcerpt(),
              "lead_image_url" => $readability->getImage(),
              "author" => $readability->getAuthor(),
            ];
          }
          break;

        case 'graby':
          // @phpstan-ignore-next-line
          $graby = new Graby();
          $result = $graby->fetchContent($url);
          if ($result->getResponse()->getStatus() == 200) {
            $settings['data'] = [
              "title" => $result->getTitle(),
              "content" => $result->getHtml(),
              "author" => $result->getAuthors(),
              "excerpt" => $result->getSummary(),
              "lead_image_url" => $result->getImage(),
            ];
          }
          break;

        case 'postlight':
        case 'mercury':
          $output = shell_exec($command);
          if (!strpos($output, 'encountered a problem trying to parse that resource') !== FALSE) {
            $settings['data'] = json_decode($output, TRUE);
          }
          break;

        case 'api':
          $endpoint = $settings['endpoint'];
          if (!empty($endpoint)) {
            $urlApiParser = $endpoint . '?url=' . $url;
            $json = file_get_contents($urlApiParser, FALSE, $context);
            $settings['data'] = json_decode($json, TRUE);
          }
          break;

        case 'embera':;
          $result = current($results);
          $settings['data'] = [
            "title" => $result['title'],
            "content" => $result['html'],
            "author" => $result['author_name'] ?? '',
            "excerpt" => $result['description'] ?? '',
            "lead_image_url" => $result['thumbnail_url'] ?? '',
          ];
          break;

      }
    }
    // Replace all relative link.
    if (!empty($settings['data']['content'])) {
      $settings['data']['content'] = str_replace('src="//', 'src="' . $urlInfo['scheme'] . '://', $settings['data']['content']);
      $settings['data']['content'] = str_replace('href="//', 'href="' . $urlInfo['scheme'] . '://', $settings['data']['content']);
      $settings['data']['content'] = str_replace('src="/', 'src="' . $host . '/', $settings['data']['content']);
      $settings['data']['content'] = str_replace('href="/', 'href="' . $host . '/', $settings['data']['content']);
    }

    if (!empty($save_image) && !empty($settings['data']) && !empty($settings['data']['content'])) {
      // Get all image in content.
      $dom = new \DOMDocument();
      $dom->loadHTML($settings['data']['content'], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
      $xpath = new \DOMXPath($dom);
      $images = $xpath->query('//img');
      $inlineImages = [];
      $directory = $settings['directory'] ?? 'inline-images';
      $directory = 'public://' . $directory . '/';
      $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
      // Save image to inline-images, replace image link.
      foreach ($images as $image) {
        $src = $image->getAttribute('src');
        // Remove lead image in content.
        if (!empty($settings['image']) && !empty($settings['data']['lead_image_url']) && $src == $settings['data']['lead_image_url']) {
          $settings['data']['content'] = str_replace($src, '', $settings['data']['content']);
          continue;
        }
        if (strpos($src, "data:image") === 0) {
          $imagePath = $directory . uniqid();
          $uri = $this->base64ToImage($src, $imagePath);
        }
        else {
          $removeQuery = explode('?', $src);
          $src = $removeQuery[0];
          $name = basename($src);
          $imagePath = $directory . $name;
          try {
            $data = (string) $this->httpClient->get($src)->getBody();
            $uri = $this->fileSystem->saveData($data, $imagePath, FileSystemInterface::EXISTS_REPLACE);
          }
          catch (TransferException $exception) {
            $this->messenger->addError(t('Failed to fetch file due to error "%error"', ['%error' => $exception->getMessage()]));
          }
          catch (FileException | InvalidStreamWrapperException $e) {
            $this->messenger->addError(t('Failed to save file due to error "%error"', ['%error' => $e->getMessage()]));
          }
        }
        if ($uri) {
          $imageUrl = $this->fileUrlGenerator->generateString($uri);
          $inlineImages[$src] = $imageUrl;
        }
      }
      if (!empty($inlineImages)) {
        $settings['data']['content'] = str_replace(array_keys($inlineImages), $inlineImages, $settings['data']['content']);
      }
    }

    if (!empty($settings['data']) && !empty($src = $settings['data']['lead_image_url']) && !empty($settings['image_folder'])) {
      $directory = $settings['image_folder'];
      $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
      $removeQuery = explode('?', $src);
      $src = $removeQuery[0];
      $name = basename($src);
      $imagePath = $directory . '/' . $name;
      $file = FALSE;
      try {
        $data = (string) $this->httpClient->get($settings['data']['lead_image_url'])->getBody();
        $uri = $this->fileSystem->saveData($data, $imagePath, FileSystemInterface::EXISTS_REPLACE);
        $file = $this->entityTypeManager->getStorage('file')->create([
          'filename' => $name,
          'uri' => $uri,
          'status' => TRUE,
          'uid' => $this->currentUser->id(),
        ]);
      }
      catch (TransferException $exception) {
        $this->messenger->addError(t('Failed to fetch file due to error "%error"', ['%error' => $exception->getMessage()]));
      }
      catch (FileException | InvalidStreamWrapperException $e) {
        $this->messenger->addError(t('Failed to save file due to error "%error"', ['%error' => $e->getMessage()]));
      }
      if ($file) {
        $file->save();
        $settings['data']['image_upload'] = [
          'target_id' => $file->id(),
          'alt' => $settings['data']['title'],
          'display' => FALSE,
          'description' => $settings['data']['excerpt'] ?? '',
          'uri' => $file->getFileUri(),
          'fids' => $file->id(),
        ];
      }

    }

    return $settings;
  }

  /**
   * {@inheritDoc}
   */
  public function base64ToImage($imageData, $fileName) {
    [$type, $imageData] = explode(';', $imageData);
    [, $imageData] = explode(',', $imageData);
    [, $extension] = explode('/', $type);
    [$ext, $isXml] = explode('+', $extension);
    $fileName .= '.' . $ext;
    $file_data = $imageData;
    if (empty($isXml)) {
      $file_data = base64_decode($imageData);
    }
    $this->fileSystem->saveData($file_data, $fileName, FileSystemInterface::EXISTS_REPLACE);
    return $fileName;
  }

  /**
   * {@inheritDoc}
   */
  public function emberaSupport($host):bool {
    $providers = [
      'acast', 'actblue', 'adilo', 'adpaths', 'afreecatv',
      'altrulabs', 'altium', 'amcharts', 'animoto', 'anniemusic',
      'apester', 'archivos', 'assemblrworld', 'audioboom', 'audioclip',
      'audiomack', 'audiomeans', 'avocode', 'backtracks',
      'beautiful', 'beams', 'blackfire', 'blogcast', 'bookingmood',
      'buttondown', 'bumper', 'byzart',
      'canva', 'ceros', 'chainflix', 'chartblocks', 'chirb', 'chroco',
      'circuitlab', 'clyp', 'codehs', 'codepen', 'codepoints', 'codesandbox',
      'commaful', 'coub', 'crumb', 'cueup', 'curated', 'crowdsignal',
      'dadan', 'datawrapper', 'dailymotion', 'dalexni', 'deseretnews',
      'deviantart', 'didacte', 'digiteka', 'docdroid', 'docswell', 'dreamBroker',
      'edumedia-sciences', 'embedery', 'ethfiddle', 'evt', 'everviz', 'eyrie',
      'facebook', 'fader', 'faithlifetv', 'fitapp', 'fite', 'flickr',
      'flourish', 'framer',
      'geograph', 'getshow', 'gettyimages', 'gfycat', 'giphy', 'gloria', 'gong',
      'gmetri', 'grain', 'gumlet', 'gyazo',
      'hash', 'hlipp', 'hearthis', 'heyzine', 'hihaho', 'hippovideo',
      'huffduffer', 'halaman',
      'ifixit', 'iheart', 'imenupro', 'imgur', 'infogram', 'infoveave',
      'injurymap', 'instagram', 'insticator', 'issuu', 'itemisCreate',
      'jovian',
      'kakao', 'kickstarter', 'kit', 'kurozora', 'kooapp', 'knacki',
      'learningapps', 'libsyn', 'lineplace', 'livestream', 'lvn', 'loom',
      'lottiefiles', 'ludus', 'lumiere',
      'matterport', 'medialab', 'mdstrm', 'medienarchiv', 'mermaid',
      'microsoftstream', 'miro', 'mixcloud', 'mixpanel', 'minesweeper',
      'musicboxmaniacs',
      'namchey', 'nanoo', 'naturalatlas', 'ndla', 'nfb', 'nftndx', 'nopaste',
      'odysee', 'omny', 'onsizzle', 'ora', 'orbitvu', 'origits', 'outplayed',
      'overflow',
      'padlet', 'pandavideo', 'pastery', 'picturelfy', 'piggy', 'pikasso',
      'pinpoll', 'pinterest', 'pitchhub', 'plusdocs', 'podbean', 'prezi',
      'portfolium',
      'qtpi',
      'radiopublic', 'rcvis', 'reddit', 'releasewire', 'replit', 'reverbnation',
      'roosterteeth', 'rumble', 'runKit',
      'saooti', 'sapo', 'screen9', 'screencast', 'scribblemaps', 'scribd',
      'sendtonews', 'shoudio', 'showtheway', 'sketchfab', 'slateapp',
      'slideshare', 'smashnotes', 'smeme', 'smrthi', 'smugmug', 'soundcloud',
      'socialexplorer', 'speakerdeck', 'spotify', 'spotlightr', 'spreaker',
      'sproutvideo', 'streamable', 'streamio', 'subscribi', 'sudomemo', 'sutori',
      'sway', 'synthesia',
      'ted', 'nytimes', 'tickcounter', 'tiktok', 'toornament', 'tonicaudio',
      'trinityaudio', 'tumblr', 'tuxx', 'tvcf', 'twinmotion', 'twitter',
      'uapod', 'u-pec', 'ustudio', 'veer',
      'verse', 'vidmount', 'videfit', 'vidyard', 'vimeo', 'viously', 'vlipsy',
      'vlive', 'vouchfor', 'voxsnap', 'wecandeo',
      'waltrack', 'wolframcloud',
      'VoxSnap',
      'whimsical', 'wistia', 'wizer', 'wokwi', 'wordpress', 'wordwall',
      'youtube', 'yumpu', 'zeplin',
      'zingsoft', 'zoomable',
    ];
    $extract = explode('.', $host);
    array_pop($extract);
    $provider = array_pop($extract);
    return in_array($provider, $providers);
  }

}
