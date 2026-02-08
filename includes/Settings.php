<?php
namespace JG_Mosaic;

if (!defined('ABSPATH')) exit;

final class Settings {

  public const OPTION_KEY = 'jg_mosaic_options';
  public const PAGE_SLUG  = 'jg-mosaic-gallery';

  /**
   * Дефолты (fallback, если опций нет)
   */
  public static function defaults(): array {
    return [
      'layout'    => 'mosaic', // mosaic|justify|masonry (mosaic сейчас реализован)
      'gap'       => 8,
      'rowHeight' => 220,
      'maxWidth'  => 1720,
      'random'    => true,

      // Разрешённые паттерны. Эти ключи должны совпадать с тем, что ты используешь в JS.
      // Сейчас мы заведём p2,p3l,p3r,p4,p5,p5b
      'patterns' => ['p2','p3l','p3r','p4','p5','p5b'],

      'mobile' => [
        'enabled'    => true,
        'breakpoint' => 560,
        'rowHeight'  => 180,
        'patterns'   => ['p2','p3l','p3r','p4'],
      ],

      'lightbox' => 'fancybox', // fancybox|link|none (пока просто конфиг, JS ты уже ставишь data-fancybox)
      'debug' => false,
    ];
  }

  /**
   * Разрешённые значения
   */
  public static function allowed_layouts(): array {
    return ['mosaic','justify','masonry'];
  }

  public static function allowed_patterns(): array {
    return [
      'p2'  => '2 фото',
      'p3l' => '3 фото: большая слева',
      'p3r' => '3 фото: большая справа',
      'p4'  => '4 фото: большая слева + 3 справа',
      'p5'  => '5 фото: 2–1–2',
      'p5b' => '5 фото: 2 большие по краям + 3 по центру',
    ];
  }

  /**
   * Глобальные настройки (defaults + сохранённые)
   */
  public static function get_global(): array {
    $saved = get_option(self::OPTION_KEY, []);
    if (!is_array($saved)) $saved = [];

    $base = self::defaults();

    // аккуратно смержим mobile и массивы
    if (isset($saved['mobile']) && is_array($saved['mobile'])) {
      $base['mobile'] = array_merge($base['mobile'], $saved['mobile']);
      unset($saved['mobile']);
    }

    foreach (['patterns'] as $k) {
      if (array_key_exists($k, $saved)) {
        $base[$k] = is_array($saved[$k]) ? array_values($saved[$k]) : $base[$k];
        unset($saved[$k]);
      }
    }

    return array_merge($base, $saved);
  }

  /**
   * Мёрдж глобальных и локальных (из шорткода/блока)
   */
  public static function merge(array $overrides): array {
    $base = self::get_global();

    // patterns перезаписываем целиком
    if (array_key_exists('patterns', $overrides)) {
      $base['patterns'] = $overrides['patterns'];
      unset($overrides['patterns']);
    }

    // mobile мержим внутрь
    if (isset($overrides['mobile']) && is_array($overrides['mobile'])) {
      $base['mobile'] = array_merge($base['mobile'], $overrides['mobile']);
      unset($overrides['mobile']);
    }

    return array_merge($base, $overrides);
  }

  /**
   * Санитизация атрибутов шорткода → overrides
   */
  public static function sanitize_atts(array $atts): array {
    $out = [];

    // layout
    if (!empty($atts['layout'])) {
      $layout = strtolower(trim((string)$atts['layout']));
      if (in_array($layout, self::allowed_layouts(), true)) {
        $out['layout'] = $layout;
      }
    }

    if (isset($atts['gap']) && $atts['gap'] !== null && $atts['gap'] !== '') {
      $out['gap'] = max(0, (int)$atts['gap']);
    }

    if (isset($atts['row_height']) && $atts['row_height'] !== null && $atts['row_height'] !== '') {
      $out['rowHeight'] = max(1, (int)$atts['row_height']);
    }

    if (isset($atts['max_width']) && $atts['max_width'] !== null && $atts['max_width'] !== '') {
      $out['maxWidth'] = max(0, (int)$atts['max_width']);
    }

    if (isset($atts['random']) && $atts['random'] !== null && $atts['random'] !== '') {
      $out['random'] = self::to_bool($atts['random']);
    }

    if (isset($atts['debug']) && $atts['debug'] !== null && $atts['debug'] !== '') {
      $out['debug'] = self::to_bool($atts['debug']);
    }

    if (!empty($atts['patterns'])) {
      $out['patterns'] = self::sanitize_patterns(self::csv_list((string)$atts['patterns']));
    }

    // mobile
    $mobile = [];
    if (isset($atts['mobile']) && $atts['mobile'] !== null && $atts['mobile'] !== '') {
      $mobile['enabled'] = self::to_bool($atts['mobile']);
    }
    if (isset($atts['mobile_breakpoint']) && $atts['mobile_breakpoint'] !== null && $atts['mobile_breakpoint'] !== '') {
      $mobile['breakpoint'] = max(0, (int)$atts['mobile_breakpoint']);
    }
    if (isset($atts['mobile_row_height']) && $atts['mobile_row_height'] !== null && $atts['mobile_row_height'] !== '') {
      $mobile['rowHeight'] = max(1, (int)$atts['mobile_row_height']);
    }
    if (!empty($atts['mobile_patterns'])) {
      $mobile['patterns'] = self::sanitize_patterns(self::csv_list((string)$atts['mobile_patterns']));
    }
    if (!empty($mobile)) $out['mobile'] = $mobile;

    // lightbox (на будущее)
    if (!empty($atts['lightbox'])) {
      $lb = strtolower(trim((string)$atts['lightbox']));
      if (in_array($lb, ['fancybox','link','none'], true)) {
        $out['lightbox'] = $lb;
      }
    }

    return $out;
  }

  private static function sanitize_patterns(array $list): array {
    $allowed = array_keys(self::allowed_patterns());
    $list = array_values(array_unique(array_filter($list, fn($p) => in_array($p, $allowed, true))));
    return $list ?: self::defaults()['patterns'];
  }

  private static function csv_list(string $value): array {
    $parts = array_map('trim', explode(',', $value));
    $parts = array_values(array_filter($parts, fn($x) => $x !== ''));
    return $parts;
  }

  private static function to_bool($value): bool {
    if (is_bool($value)) return $value;
    $v = strtolower(trim((string)$value));
    return in_array($v, ['1','true','yes','y','on'], true);
  }

  /* =========================
   * Админка: Settings API
   * ========================= */

  public static function add_settings_page(): void {
    add_options_page(
      'JG Mosaic Gallery',
      'JG Mosaic Gallery',
      'manage_options',
      self::PAGE_SLUG,
      [__CLASS__, 'render_settings_page']
    );
  }

  public static function register_admin(): void {
    register_setting(
      self::PAGE_SLUG,
      self::OPTION_KEY,
      [
        'type'              => 'array',
        'sanitize_callback' => [__CLASS__, 'sanitize_options'],
        'default'           => self::defaults(),
      ]
    );

    // Секция: Основные
    add_settings_section(
      'jg_mosaic_main',
      'Основные настройки',
      function () {
        echo '<p>Задайте значения по умолчанию для всех галерей. Шорткод может переопределять эти настройки.</p>';
      },
      self::PAGE_SLUG
    );

    add_settings_field('layout', 'Layout', [__CLASS__, 'field_layout'], self::PAGE_SLUG, 'jg_mosaic_main');
    add_settings_field('gap', 'Gap (px)', [__CLASS__, 'field_gap'], self::PAGE_SLUG, 'jg_mosaic_main');
    add_settings_field('rowHeight', 'Row height (px)', [__CLASS__, 'field_row_height'], self::PAGE_SLUG, 'jg_mosaic_main');
    add_settings_field('maxWidth', 'Max width (px)', [__CLASS__, 'field_max_width'], self::PAGE_SLUG, 'jg_mosaic_main');
    add_settings_field('random', 'Random patterns', [__CLASS__, 'field_random'], self::PAGE_SLUG, 'jg_mosaic_main');

    // Секция: Паттерны
    add_settings_section(
      'jg_mosaic_patterns',
      'Паттерны',
      function () {
        echo '<p>Выберите паттерны, которые разрешены для использования.</p>';
      },
      self::PAGE_SLUG
    );
    add_settings_field('patterns', 'Allowed patterns', [__CLASS__, 'field_patterns'], self::PAGE_SLUG, 'jg_mosaic_patterns');

    // Секция: Mobile
    add_settings_section(
      'jg_mosaic_mobile',
      'Mobile',
      function () {
        echo '<p>Настройки для мобильной логики (порог и разрешённые паттерны).</p>';
      },
      self::PAGE_SLUG
    );
    add_settings_field('mobile_enabled', 'Enable mobile mode', [__CLASS__, 'field_mobile_enabled'], self::PAGE_SLUG, 'jg_mosaic_mobile');
    add_settings_field('mobile_breakpoint', 'Mobile breakpoint (px)', [__CLASS__, 'field_mobile_breakpoint'], self::PAGE_SLUG, 'jg_mosaic_mobile');
    add_settings_field('mobile_rowHeight', 'Mobile row height (px)', [__CLASS__, 'field_mobile_row_height'], self::PAGE_SLUG, 'jg_mosaic_mobile');
    add_settings_field('mobile_patterns', 'Mobile patterns', [__CLASS__, 'field_mobile_patterns'], self::PAGE_SLUG, 'jg_mosaic_mobile');

    // Секция: Advanced
    add_settings_section(
      'jg_mosaic_advanced',
      'Advanced',
      function () {
        echo '<p>Опции для отладки.</p>';
      },
      self::PAGE_SLUG
    );
    add_settings_field('debug', 'Debug', [__CLASS__, 'field_debug'], self::PAGE_SLUG, 'jg_mosaic_advanced');
  }

  /**
   * sanitize_callback для options массива
   */
  public static function sanitize_options($input): array {
    $defaults = self::defaults();
    if (!is_array($input)) return $defaults;

    $out = $defaults;

    // layout
    if (!empty($input['layout'])) {
      $layout = strtolower(trim((string)$input['layout']));
      if (in_array($layout, self::allowed_layouts(), true)) {
        $out['layout'] = $layout;
      }
    }

    $out['gap'] = isset($input['gap']) ? max(0, (int)$input['gap']) : $defaults['gap'];
    $out['rowHeight'] = isset($input['rowHeight']) ? max(1, (int)$input['rowHeight']) : $defaults['rowHeight'];
    $out['maxWidth'] = isset($input['maxWidth']) ? max(0, (int)$input['maxWidth']) : $defaults['maxWidth'];

    $out['random'] = !empty($input['random']);
    $out['debug']  = !empty($input['debug']);

    // patterns
    if (isset($input['patterns']) && is_array($input['patterns'])) {
      $out['patterns'] = self::sanitize_patterns(array_values($input['patterns']));
    }

    // mobile
    $mobile = $defaults['mobile'];
    if (isset($input['mobile']) && is_array($input['mobile'])) {
      $mobile['enabled'] = !empty($input['mobile']['enabled']);
      $mobile['breakpoint'] = isset($input['mobile']['breakpoint']) ? max(0, (int)$input['mobile']['breakpoint']) : $mobile['breakpoint'];
      $mobile['rowHeight']  = isset($input['mobile']['rowHeight']) ? max(1, (int)$input['mobile']['rowHeight']) : $mobile['rowHeight'];

      if (isset($input['mobile']['patterns']) && is_array($input['mobile']['patterns'])) {
        $mobile['patterns'] = self::sanitize_patterns(array_values($input['mobile']['patterns']));
      }
    }
    $out['mobile'] = $mobile;

    return $out;
  }

  /* =========================
   * Render page + fields
   * ========================= */

  public static function render_settings_page(): void {
    if (!current_user_can('manage_options')) return;

    echo '<div class="wrap">';
    echo '<h1>JG Mosaic Gallery</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields(self::PAGE_SLUG);
    do_settings_sections(self::PAGE_SLUG);
    submit_button();
    echo '</form>';

    echo '<hr />';
    echo '<h2>Пример шорткода</h2>';
    echo '<code>[jg_mosaic ids="1,2,3" layout="mosaic" gap="8" row_height="220" random="1" patterns="p2,p3l,p3r,p4,p5" mobile_breakpoint="560" mobile_patterns="p2,p3l,p3r,p4"]</code>';

    echo '</div>';
  }

  private static function opt(): array {
    return self::get_global();
  }

  private static function field_name(string $key): string {
    return self::OPTION_KEY . '[' . $key . ']';
  }

  public static function field_layout(): void {
    $opt = self::opt();
    $cur = $opt['layout'] ?? 'mosaic';

    echo '<select name="' . esc_attr(self::field_name('layout')) . '">';
    foreach (self::allowed_layouts() as $layout) {
      printf(
        '<option value="%s" %s>%s</option>',
        esc_attr($layout),
        selected($cur, $layout, false),
        esc_html($layout)
      );
    }
    echo '</select>';
    echo '<p class="description">mosaic — паттерны (как Я.Диск), justify/masonry — на будущее.</p>';
  }

  public static function field_gap(): void {
    $opt = self::opt();
    $val = (int)($opt['gap'] ?? 8);
    echo '<input type="number" min="0" step="1" name="' . esc_attr(self::field_name('gap')) . '" value="' . esc_attr($val) . '"/>';
  }

  public static function field_row_height(): void {
    $opt = self::opt();
    $val = (int)($opt['rowHeight'] ?? 220);
    echo '<input type="number" min="1" step="1" name="' . esc_attr(self::field_name('rowHeight')) . '" value="' . esc_attr($val) . '"/>';
  }

  public static function field_max_width(): void {
    $opt = self::opt();
    $val = (int)($opt['maxWidth'] ?? 1720);
    echo '<input type="number" min="0" step="1" name="' . esc_attr(self::field_name('maxWidth')) . '" value="' . esc_attr($val) . '"/>';
  }

  public static function field_random(): void {
    $opt = self::opt();
    $val = !empty($opt['random']);
    echo '<label><input type="checkbox" name="' . esc_attr(self::field_name('random')) . '" value="1" ' . checked($val, true, false) . '> Включить</label>';
    echo '<p class="description">Если включено, паттерны выбираются случайно (рекомендуем позже сделать “seed”, чтобы не прыгало на ресайзе).</p>';
  }

  public static function field_patterns(): void {
    $opt = self::opt();
    $cur = $opt['patterns'] ?? self::defaults()['patterns'];
    if (!is_array($cur)) $cur = [];

    $allowed = self::allowed_patterns();

    foreach ($allowed as $key => $label) {
      $checked = in_array($key, $cur, true);
      printf(
        '<label style="display:block;margin:2px 0;"><input type="checkbox" name="%s[]" value="%s" %s> %s <code>%s</code></label>',
        esc_attr(self::field_name('patterns')),
        esc_attr($key),
        checked($checked, true, false),
        esc_html($label),
        esc_html($key)
      );
    }
  }

  public static function field_mobile_enabled(): void {
    $opt = self::opt();
    $val = !empty($opt['mobile']['enabled']);
    echo '<label><input type="checkbox" name="' . esc_attr(self::OPTION_KEY . '[mobile][enabled]') . '" value="1" ' . checked($val, true, false) . '> Включить</label>';
  }

  public static function field_mobile_breakpoint(): void {
    $opt = self::opt();
    $val = (int)($opt['mobile']['breakpoint'] ?? 560);
    echo '<input type="number" min="0" step="1" name="' . esc_attr(self::OPTION_KEY . '[mobile][breakpoint]') . '" value="' . esc_attr($val) . '"/>';
  }

  public static function field_mobile_row_height(): void {
    $opt = self::opt();
    $val = (int)($opt['mobile']['rowHeight'] ?? 180);
    echo '<input type="number" min="1" step="1" name="' . esc_attr(self::OPTION_KEY . '[mobile][rowHeight]') . '" value="' . esc_attr($val) . '"/>';
  }

  public static function field_mobile_patterns(): void {
    $opt = self::opt();
    $cur = $opt['mobile']['patterns'] ?? self::defaults()['mobile']['patterns'];
    if (!is_array($cur)) $cur = [];

    $allowed = self::allowed_patterns();

    foreach ($allowed as $key => $label) {
      $checked = in_array($key, $cur, true);
      printf(
        '<label style="display:block;margin:2px 0;"><input type="checkbox" name="%s[]" value="%s" %s> %s <code>%s</code></label>',
        esc_attr(self::OPTION_KEY . '[mobile][patterns]'),
        esc_attr($key),
        checked($checked, true, false),
        esc_html($label),
        esc_html($key)
      );
    }

    echo '<p class="description">На мобилке обычно лучше выключать тяжёлые паттерны p5/p5b.</p>';
  }

  public static function field_debug(): void {
    $opt = self::opt();
    $val = !empty($opt['debug']);
    echo '<label><input type="checkbox" name="' . esc_attr(self::field_name('debug')) . '" value="1" ' . checked($val, true, false) . '> Включить</label>';
  }
}
