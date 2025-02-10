<?php

namespace Drupal\postlight_parser\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Postlight parser routes.
 */
class ParserController extends ControllerBase {
  /**
   * The url parser service.
   *
   * @var \Drupal\postlight_parser\UrlParserServiceInterface
   */
  protected $urlParser;

  /**
   * Construct the subscriptions controller from parent.
   *
   * @param \Drupal\postlight_parser\UrlParserServiceInterface $url_parser
   *   Url parser service.
   */
  public function __construct($url_parser) {
    // Simply add our service.
    $this->urlParser = $url_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('postlight_parser.url_parser'),
    );
  }

  /**
   * Builds the response.
   */
  public function parser(Request $request, $parser): JsonResponse {
    $url = $request->query->get('url');
    $save_image = $request->query->get('save_image');
    $argument = [
      'url' => $url,
      'parser' => $parser,
      'save_image' => $save_image,
    ];
    $output = $this->urlParser->parser($argument);
    if (!empty($output['data'])) {
      return new JsonResponse($output['data'], 200);
    }
    return new JsonResponse([], 200);
  }

}
