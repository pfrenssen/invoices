<?php
/**
 * @file
 * line_item.features.inc
 */

/**
 * Implements hook_default_line_item_type().
 */
function line_item_default_line_item_type() {
  $items = array();
  $items['product'] = entity_import('line_item_type', '{ "type" : "product", "name" : "Product" }');
  $items['service'] = entity_import('line_item_type', '{ "type" : "service", "name" : "Service" }');
  return $items;
}
