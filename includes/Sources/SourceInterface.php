<?php
namespace JG_Mosaic\Sources;

if (!defined('ABSPATH')) exit;

interface SourceInterface {
  /** @return array<int, array<string, mixed>> */
  public function get_items(array $args): array;
}
