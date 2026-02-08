<?php
namespace JG_Mosaic;

if (!defined('ABSPATH')) exit;

final class Plugin {

  public static function init(): void {
    self::autoload();
    add_action('plugins_loaded', [__CLASS__, 'boot']);
  }

  private static function autoload(): void {
    spl_autoload_register(function ($class) {
      // Only our namespace
      if (strpos($class, __NAMESPACE__ . '\\') !== 0) return;

      $relative = substr($class, strlen(__NAMESPACE__ . '\\'));
      $relative = str_replace('\\', DIRECTORY_SEPARATOR, $relative);

      $file = JG_MOSAIC_PATH . 'includes' . DIRECTORY_SEPARATOR . $relative . '.php';
      if (file_exists($file)) require_once $file;
    });
  }

  public static function boot(): void {
    Assets::register_hooks();
    Shortcode::register();
    // Позже можно добавить ACF Block integration
    // ✅ Админка настроек
    add_action('admin_menu', [Settings::class, 'add_settings_page']);
    add_action('admin_init', [Settings::class, 'register_admin']);
  }
}
