<?php

/**
 * @file
 * Administration UI for the Line item module.
 */

/**
 * Generates the line item type editing form.
 */
function line_item_type_form($form, &$form_state, $line_item_type, $op = 'edit') {
  // Append 'clone' to the name when the entity is being cloned.
  if ($op == 'clone') {
    $line_item_type->name .= ' (cloned)';
    $line_item_type->type = '';
  }

  // Human readable name.
  $form['name'] = array(
    '#title' => t('Type name'),
    '#type' => 'textfield',
    '#default_value' => isset($line_item_type->name) ? $line_item_type->name : '',
    '#description' => t('The human-readable name of this line item type.'),
    '#required' => TRUE,
    '#size' => 30,
  );

  // Machine readable name.
  $form['type'] = array(
    '#type' => 'machine_name',
    '#default_value' => isset($line_item_type->type) ? $line_item_type->type : '',
    '#maxlength' => 32,
    '#machine_name' => array(
      'exists' => 'line_item_load',
      'source' => array('name'),
    ),
    '#description' => t('A unique machine-readable name for this line item type. It must only contain lowercase letters, numbers, and underscores.'),
  );

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save line item type'),
    '#weight' => 40,
  );

  if ($op != 'add' && $op != 'clone') {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete line item type'),
      '#weight' => 45,
      '#limit_validation_errors' => array(),
      '#submit' => array('line_item_type_form_submit_delete'),
    );
  }

  return $form;
}

/**
 * Form API submit callback for the type form.
 */
function line_item_type_form_submit(&$form, &$form_state) {
  $line_item_type = entity_ui_form_submit_build_entity($form, $form_state);
  // Save and go back.
  $line_item_type->save();
  $form_state['redirect'] = 'admin/structure/line_items';
}

/**
 * Form API submit callback for the delete button.
 */
function line_item_type_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/structure/line_items/manage/' . $form_state['line_item_type']->type . '/delete';
}
