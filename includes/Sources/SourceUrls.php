<?php
namespace JG_Mosaic\Sources;

if (!defined('ABSPATH')) exit;

final class SourceUrls implements SourceInterface {

  public function get_items(array $args): array {
    $urls = $args['urls'] ?? [];
    if (is_string($urls)) $urls = array_filter(array_map('trim', explode(',', $urls)));
    if (!is_array($urls)) return [];

    $items = [];
    foreach ($urls as $url) {
      $url = esc_url_raw($url);
      if (!$url) continue;

      $items[] = [
        'src' => $url,
        'w' => null,
        'h' => null,
        'alt' => '',
        'caption' => '',
        'href' => $url,
      ];
    }

    return $items;
  }
}
