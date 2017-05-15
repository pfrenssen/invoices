<?php

/**
 * @file
 * Contains line_item.page.inc.
 *
 * Page callback for Line item entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Line item templates.
 *
 * Default template: line_item.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_line_item(array &$variables) {
  // Fetch LineItem Entity Object.
  $line_item = $variables['elements']['#line_item'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
