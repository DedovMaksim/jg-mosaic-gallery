<?php
namespace JG_Mosaic\Sources;

if (!defined('ABSPATH')) exit;

final class SourceIds implements SourceInterface {

  public function get_items(array $args): array {
    $ids = $args['ids'] ?? [];
    if (is_string($ids)) $ids = array_map('intval', array_filter(array_map('trim', explode(',', $ids))));
    if (!is_array($ids)) return [];

    $items = [];

    foreach ($ids as $id) {
      $id = (int)$id;
      if ($id <= 0) continue;

      $img = wp_get_attachment_image_src($id, 'full');
      if (!$img || empty($img[0])) continue;

      $src = $img[0];
      $w   = isset($img[1]) ? (int)$img[1] : null;
      $h   = isset($img[2]) ? (int)$img[2] : null;

      $alt = (string) get_post_meta($id, '_wp_attachment_image_alt', true);
      $cap = (string) wp_get_attachment_caption($id);

      $items[] = [
        'src' => $src,
        'w' => $w,
        'h' => $h,
        'alt' => $alt,
        'caption' => $cap,
        'href' => $src,
        'id' => $id,
      ];
    }

    return $items;
  }
}
