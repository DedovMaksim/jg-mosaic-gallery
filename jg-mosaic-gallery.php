<?php
/**
 * Plugin Name: JG Mosaic Gallery
 * Description: Adaptive mosaic image gallery with modern patterns.
 * Version: 0.1.0
 * Author: Maksim Dedov
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jg-mosaic-gallery
 */

if (!defined('ABSPATH')) exit;

define('JG_MOSAIC_VERSION', '0.1.0');
define('JG_MOSAIC_PATH', plugin_dir_path(__FILE__));
define('JG_MOSAIC_URL', plugin_dir_url(__FILE__));

require_once JG_MOSAIC_PATH . 'includes/Plugin.php';

JG_Mosaic\Plugin::init();
