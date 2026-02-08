<?php
namespace JG_Mosaic\Sources;

if (!defined('ABSPATH')) exit;

final class SourceACF implements SourceInterface {

  public function get_items(array $args): array {
    if (!function_exists('get_field')) return [];

    $field = $args['field'] ?? '';
    $post_id = $args['post_id'] ?? get_the_ID();

    if (!$field) return [];

    $value = get_field($field, $post_id);
    if (empty($value)) return [];

    // ACF gallery обычно возвращает массив изображений (array) или IDs (в зависимости от настроек поля)
    $ids = [];

    if (is_array($value)) {
      foreach ($value as $row) {
        if (is_numeric($row)) {
          $ids[] = (int)$row;
        } elseif (is_array($row) && !empty($row['ID'])) {
          $ids[] = (int)$row['ID'];
        }
      }
    } elseif (is_numeric($value)) {
      $ids[] = (int)$value;
    }

    if (empty($ids)) return [];

    $idsSource = new SourceIds();
    return $idsSource->get_items(['ids' => $ids]);
  }
}
