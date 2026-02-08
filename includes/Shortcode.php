<?php
namespace JG_Mosaic;

use JG_Mosaic\Sources\SourceACF;
use JG_Mosaic\Sources\SourceIds;
use JG_Mosaic\Sources\SourceUrls;

if (!defined('ABSPATH')) exit;

final class Shortcode {

  public static function register(): void {
    add_shortcode('jg_mosaic', [__CLASS__, 'handle']);
  }

  public static function handle($atts = [], $content = null, $tag = ''): string {
    $atts = shortcode_atts([
      // источники
      'ids' => '',
      'urls' => '',
      'acf' => '',

      // настройки
      'gap' => null,
      'row_height' => null,
      'max_width' => null,
      'patterns' => '',
      'random' => null,
      'layout' => null,
      'lightbox' => null,

      'mobile' => null,
      'mobile_breakpoint' => null,
      'mobile_row_height' => null,
      'mobile_patterns' => '',

      'debug' => null,
    ], (array)$atts, 'jg_mosaic');

    // 1) Собрать items из источника по приоритету: ids > acf > urls
    $items = [];
    $source = '';

    if (!empty($atts['ids'])) {
      $items = (new SourceIds())->get_items(['ids' => $atts['ids']]);
      $source = 'ids';
    } elseif (!empty($atts['acf'])) {
      $items = (new SourceACF())->get_items(['field' => $atts['acf'], 'post_id' => get_the_ID()]);
      $source = 'acf';
    } elseif (!empty($atts['urls'])) {
      $items = (new SourceUrls())->get_items(['urls' => $atts['urls']]);
      $source = 'urls';
    }

    if (empty($items)) {
      // тихо ничего не выводим, чтобы не ломать вёрстку
      return '';
    }

    // 2) overrides из атрибутов
    $overrides = Settings::sanitize_atts($atts);

    // 3) итоговый config
    $config = Settings::merge($overrides);

    // 4) просим подключить ассеты
    Assets::enqueue();

    // 5) render
    $atts['__source'] = $source;
    return Renderer::render($items, $config, $atts);
  }
}
