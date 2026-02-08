<?php
namespace JG_Mosaic;

if (!defined('ABSPATH')) exit;

final class Assets {

  public static function register_hooks(): void {
    add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets'], 5);
  }

  public static function register_assets(): void {
    $js_core   = JG_MOSAIC_URL . 'assets/dist/jg-mosaic-core.js';
    $js_init   = JG_MOSAIC_URL . 'assets/dist/jg-mosaic-wp-init.js';
    $css_core  = JG_MOSAIC_URL . 'assets/dist/jg-mosaic-core.css';
    $js_lb  = JG_MOSAIC_URL . 'assets/dist/jg-mosaic-lightbox.js';
    $css_lb = JG_MOSAIC_URL . 'assets/dist/jg-mosaic-lightbox.css';

    wp_register_style('jg-mosaic-core', $css_core, [], JG_MOSAIC_VERSION);

    wp_register_script('jg-mosaic-core', $js_core, [], JG_MOSAIC_VERSION, true);

    wp_register_script('jg-mosaic-wp-init', $js_init, ['jg-mosaic-core'], JG_MOSAIC_VERSION, true);

    wp_register_style('jg-mosaic-lightbox', $css_lb, [], JG_MOSAIC_VERSION);

    wp_register_script('jg-mosaic-lightbox', $js_lb, [], JG_MOSAIC_VERSION, true);
  }

  public static function enqueue(): void {
    // На случай, если кто-то вызвал шорткод до wp_enqueue_scripts (редко, но бывает)
    if (!wp_style_is('jg-mosaic-core', 'registered') || !wp_script_is('jg-mosaic-wp-init', 'registered')) {
      self::register_assets();
    }

    wp_enqueue_style('jg-mosaic-core');
    wp_enqueue_script('jg-mosaic-core');
    wp_enqueue_script('jg-mosaic-wp-init');
    wp_enqueue_style('jg-mosaic-lightbox');
    wp_enqueue_script('jg-mosaic-lightbox');
  }
}
