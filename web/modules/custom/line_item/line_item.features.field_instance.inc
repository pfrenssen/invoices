<?php
/**
 * @file
 * line_item.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function line_item_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance: 'line_item-product-field_line_item_description'.
  $field_instances['line_item-product-field_line_item_description'] = array(
    'bundle' => 'product',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Short description of the line item.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'line_item',
    'field_name' => 'field_line_item_description',
    'label' => 'Description',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 1,
    ),
  );

  // Exported field_instance: 'line_item-product-field_line_item_discount'.
  $field_instances['line_item-product-field_line_item_discount'] = array(
    'bundle' => 'product',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'The discount to apply on the line item, as a percentage.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'number',
        'settings' => array(
          'decimal_separator' => '.',
          'prefix_suffix' => TRUE,
          'scale' => 2,
          'thousand_separator' => ' ',
        ),
        'type' => 'number_decimal',
        'weight' => 4,
      ),
    ),
    'entity_type' => 'line_item',
    'field_name' => 'field_line_item_discount',
    'label' => 'Discount',
    'required' => 0,
    'settings' => array(
      'max' => '',
      'min' => '',
      'prefix' => '',
      'suffix' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'number',
      'settings' => array(),
      'type' => 'number',
      'weight' => 5,
    ),
  );

  // Exported field_instance: 'line_item-product-field_line_item_quantity'.
  $field_instances['line_item-product-field_line_item_quantity'] = array(
    'bundle' => 'product',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'The number of units sold.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'number',
        'settings' => array(
          'decimal_separator' => '.',
          'prefix_suffix' => TRUE,
          'scale' => 2,
          'thousand_separator' => ' ',
        ),
        'type' => 'number_decimal',
        'weight' => 2,
      ),
    ),
    'entity_type' => 'line_item',
    'field_name' => 'field_line_item_quantity',
    'label' => 'Quantity',
    'required' => 0,
    'settings' => array(
      'max' => '',
      'min' => '',
      'prefix' => '',
      'suffix' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'number',
      'settings' => array(),
      'type' => 'number',
      'weight' => 3,
    ),
  );

  // Exported field_instance: 'line_item-product-field_line_item_tax'.
  $field_instances['line_item-product-field_line_item_tax'] = array(
    'bundle' => 'product',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'The tax rate to apply on the line item.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'number',
        'settings' => array(
          'decimal_separator' => '.',
          'prefix_suffix' => TRUE,
          'scale' => 2,
          'thousand_separator' => ' ',
        ),
        'type' => 'number_decimal',
        'weight' => 3,
      ),
    ),
    'entity_type' => 'line_item',
    'field_name' => 'field_line_item_tax',
    'label' => 'Tax',
    'required' => 0,
    'settings' => array(
      'max' => '',
      'min' => '',
      'prefix' => '',
      'suffix' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'line_item',
      'settings' => array(
        'size' => 7,
      ),
      'type' => 'line_item_tax_rate',
      'weight' => 4,
    ),
  );

  // Exported field_instance: 'line_item-product-field_line_item_unit_cost'.
  $field_instances['line_item-product-field_line_item_unit_cost'] = array(
    'bundle' => 'product',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'The price per unit for the line item.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'number',
        'settings' => array(
          'decimal_separator' => '.',
          'prefix_suffix' => TRUE,
          'scale' => 2,
          'thousand_separator' => ' ',
        ),
        'type' => 'number_decimal',
        'weight' => 1,
      ),
    ),
    'entity_type' => 'line_item',
    'field_name' => 'field_line_item_unit_cost',
    'label' => 'Unit cost',
    'required' => 0,
    'settings' => array(
      'max' => '',
      'min' => '',
      'prefix' => '',
      'suffix' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'number',
      'settings' => array(),
      'type' => 'number',
      'weight' => 2,
    ),
  );

  // Exported field_instance: 'line_item-service-field_line_item_description'.
  $field_instances['line_item-service-field_line_item_description'] = array(
    'bundle' => 'service',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Short description of the line item.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'line_item',
    'field_name' => 'field_line_item_description',
    'label' => 'Description',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 2,
    ),
  );

  // Exported field_instance: 'line_item-service-field_line_item_discount'.
  $field_instances['line_item-service-field_line_item_discount'] = array(
    'bundle' => 'service',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'The discount to apply on the line item, as a percentage.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'number',
        'settings' => array(
          'decimal_separator' => '.',
          'prefix_suffix' => TRUE,
          'scale' => 2,
          'thousand_separator' => ' ',
        ),
        'type' => 'number_decimal',
        'weight' => 5,
      ),
    ),
    'entity_type' => 'line_item',
    'field_name' => 'field_line_item_discount',
    'label' => 'Discount',
    'required' => 0,
    'settings' => array(
      'max' => '',
      'min' => '',
      'prefix' => '',
      'suffix' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'number',
      'settings' => array(),
      'type' => 'number',
      'weight' => 11,
    ),
  );

  // Exported field_instance: 'line_item-service-field_line_item_quantity'.
  $field_instances['line_item-service-field_line_item_quantity'] = array(
    'bundle' => 'service',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'The number of units sold.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'number',
        'settings' => array(
          'decimal_separator' => '.',
          'prefix_suffix' => TRUE,
          'scale' => 2,
          'thousand_separator' => ' ',
        ),
        'type' => 'number_decimal',
        'weight' => 3,
      ),
    ),
    'entity_type' => 'line_item',
    'field_name' => 'field_line_item_quantity',
    'label' => 'Quantity',
    'required' => 0,
    'settings' => array(
      'max' => '',
      'min' => '',
      'prefix' => '',
      'suffix' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'number',
      'settings' => array(),
      'type' => 'number',
      'weight' => 7,
    ),
  );

  // Exported field_instance: 'line_item-service-field_line_item_tax'.
  $field_instances['line_item-service-field_line_item_tax'] = array(
    'bundle' => 'service',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'The tax rate to apply on the line item.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'number',
        'settings' => array(
          'decimal_separator' => '.',
          'prefix_suffix' => TRUE,
          'scale' => 2,
          'thousand_separator' => ' ',
        ),
        'type' => 'number_decimal',
        'weight' => 4,
      ),
    ),
    'entity_type' => 'line_item',
    'field_name' => 'field_line_item_tax',
    'label' => 'Tax',
    'required' => 0,
    'settings' => array(
      'max' => '',
      'min' => '',
      'prefix' => '',
      'suffix' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'line_item',
      'settings' => array(
        'size' => 7,
      ),
      'type' => 'line_item_tax_rate',
      'weight' => 9,
    ),
  );

  // Exported field_instance: 'line_item-service-field_line_item_time_unit'.
  $field_instances['line_item-service-field_line_item_time_unit'] = array(
    'bundle' => 'service',
    'default_value' => array(
      0 => array(
        'value' => 'minutes',
      ),
    ),
    'deleted' => 0,
    'description' => 'The time unit for the line item.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'list',
        'settings' => array(),
        'type' => 'list_default',
        'weight' => 2,
      ),
    ),
    'entity_type' => 'line_item',
    'field_name' => 'field_line_item_time_unit',
    'label' => 'Time unit',
    'required' => 1,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(),
      'type' => 'options_buttons',
      'weight' => 5,
    ),
  );

  // Exported field_instance: 'line_item-service-field_line_item_unit_cost'.
  $field_instances['line_item-service-field_line_item_unit_cost'] = array(
    'bundle' => 'service',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'The price per unit for the line item.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'number',
        'settings' => array(
          'decimal_separator' => '.',
          'prefix_suffix' => TRUE,
          'scale' => 2,
          'thousand_separator' => ' ',
        ),
        'type' => 'number_decimal',
        'weight' => 1,
      ),
    ),
    'entity_type' => 'line_item',
    'field_name' => 'field_line_item_unit_cost',
    'label' => 'Unit cost',
    'required' => 0,
    'settings' => array(
      'max' => '',
      'min' => '',
      'prefix' => '',
      'suffix' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'number',
      'settings' => array(),
      'type' => 'number',
      'weight' => 4,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Description');
  t('Discount');
  t('Quantity');
  t('Short description of the line item.');
  t('Tax');
  t('The discount to apply on the line item, as a percentage.');
  t('The number of units sold.');
  t('The price per unit for the line item.');
  t('The tax rate to apply on the line item.');
  t('The time unit for the line item.');
  t('Time unit');
  t('Unit cost');

  return $field_instances;
}
