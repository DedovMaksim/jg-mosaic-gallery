<?php
namespace JG_Mosaic;

if (!defined('ABSPATH')) exit;

final class Renderer {

  public static function render(array $items, array $config, array $attrs = []): string {
    $id = 'jg-' . wp_generate_uuid4();

    $payload = [
      'items'  => array_values($items),
      'config' => $config,
      'meta'   => [
        'id' => $id,
        'source' => $attrs['__source'] ?? '',
      ],
    ];

    $json = wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $classes = ['jg', 'jg-mosaic'];
    if (!empty($config['debug'])) $classes[] = 'jg--debug';

    $html  = '<div id="' . esc_attr($id) . '" class="' . esc_attr(implode(' ', $classes)) . '"';
    $html .= ' data-jg="' . esc_attr($json) . '">';
    $html .= '<div class="jg__track"></div>';
    $html .= '</div>';

    return $html;
  }
}
