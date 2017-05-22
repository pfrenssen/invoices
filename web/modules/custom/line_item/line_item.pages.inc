<?php

/**
 * @file
 * Page callbacks for adding, editing and deleting line items.
 */

/**
 * Page callback: Choose for which line item type to create a new line item for.
 */
function line_item_type_add_page() {
  $types = line_item_get_types();

  $items = [];
  foreach ($types as $type) {
    $items[] = l($type->name, 'line_item/add/' . $type->type);
  }

  return theme('item_list', ['items' => $items, 'title' => NULL]);
}

/**
 * Page callback: Returns the line item add/edit form to add a new line item.
 *
 * @param object $line_item_type
 *   The type of line_item to create.
 *
 * @return array
 *   The line item add/edit form render array.
 *
 * @see line_item_edit_form()
 */
function line_item_add_page($line_item_type) {
  $line_item = entity_create('line_item', ['type' => $line_item_type->type]);

  return drupal_get_form('line_item_edit_form', $line_item);
}

/**
 * Page callback: Returns the add/edit form to edit an existing line item.
 *
 * @param LineItem $line_item
 *   The line item to edit.
 *
 * @return array
 *   The line item add/edit form render array.
 *
 * @see line_item_edit_form()
 */
function line_item_edit_page(LineItem $line_item) {
  drupal_set_title(t('Edit line item'));

  return drupal_get_form('line_item_edit_form', $line_item);
}

/**
 * Form constructor for the line item add/edit form.
 *
 * @param LineItem $line_item
 *   The line item that is being added/edited.
 *
 * @ingroup forms
 */
function line_item_edit_form($form, &$form_state, LineItem $line_item) {
  // During initial form build, add the entity to the form state for use during
  // form building and processing. During a rebuild, use what is in the form
  // state.
  if (!isset($form_state['line_item'])) {
    $form_state['line_item'] = $line_item;
  }
  else {
    $line_item = $form_state['line_item'];
  }

  // Attach all form elements for the fields.
  field_attach_form('line_item', $line_item, $form, $form_state, entity_language('line_item', $line_item));

  // Add the buttons.
  $form['actions'] = ['#type' => 'actions'];

  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => t('Save'),
    '#weight' => 5,
    '#submit' => ['line_item_edit_form_submit'],
  ];

  if (!empty($line_item->id()) && line_item_access('delete', $line_item)) {
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#weight' => 15,
      '#submit' => ['line_item_edit_form_delete_submit'],
    ];
  }

  return $form;
}

/**
 * Form validation handler for line_item_edit_form().
 */
function line_item_edit_form_validate($form, &$form_state) {
  field_attach_form_validate('line_item', $form_state['line_item'], $form, $form_state);
}

/**
 * Form submission handler for line_item_edit_form().
 *
 * Saves a line item and outputs a status message to the user.
 */
function line_item_edit_form_submit($form, &$form_state) {
  $line_item = $form_state['line_item'];
  entity_form_submit_build_entity('line_item', $line_item, $form, $form_state);

  $operation = !empty($line_item->is_new) ? 'add' : 'edit';

  if (line_item_save($line_item)) {
    $message = $operation == 'add' ? 'New line item has been added.' : 'The changes have been saved.';
    // @codingStandardsIgnoreStart
    drupal_set_message(t($message));
    // @codingStandardsIgnoreEnd
  }
  else {
    drupal_set_message(t('The line item could not be saved.'), 'error');
    watchdog('line_item', 'Line item could not be saved.', WATCHDOG_ERROR);
  }
}

/**
 * Form submission handler for line_item_edit_form().
 *
 * Handles the 'Delete' button on the node form.
 *
 * @see line_item_edit_form()
 * @see line_item_delete_confirm_form()
 */
function line_item_edit_form_delete_submit($form, &$form_state) {
  $destination = [];
  if (isset($_GET['destination'])) {
    $destination = drupal_get_destination();
    unset($_GET['destination']);
  }
  $line_item = $form_state['line_item'];
  $form_state['redirect'] = ['line_item/' . $line_item->id() . '/delete', ['query' => $destination]];
}

/**
 * Form constructor for the line item deletion confirmation form.
 *
 * @see line_item_delete_confirm_form_submit()
 */
function line_item_delete_confirm_form($form, &$form_state, LineItem $line_item) {
  $form_state['line_item'] = $line_item;
  return confirm_form($form,
    t('Are you sure you want to delete the line item?'),
    'line_item/' . $line_item->id(),
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );
}

/**
 * Form submission handler for line_item_delete_confirm_form().
 *
 * Deletes the line item.
 *
 * @see line_item_delete_confirm_form()
 */
function line_item_delete_confirm_form_submit($form, &$form_state) {
  $line_item = $form_state['line_item'];
  line_item_delete($line_item);
  drupal_set_message(t('The line item has been deleted.'));
  $form_state['redirect'] = '<front>';
}

/**
 * Page callback for the line item page.
 */
function line_item_detail_page($line_item) {
  $output = entity_view('line_item', [$line_item]);

  // Prevent the line item title from being rendered twice.
  $output['line_item'][$line_item->id()]['#page'] = TRUE;

  return $output;
}

/**
 * Form constructor for the tax rate add/edit form.
 *
 * @param TaxRate $tax_rate
 *   The tax rate that is being added/edited.
 *
 * @ingroup forms
 */
function line_item_edit_tax_rate_form($form, &$form_state, TaxRate $tax_rate = NULL) {
  $form_state['tax_rate'] = $tax_rate;

  if (!empty($tax_rate)) {
    drupal_set_title(t('Edit %name', ['%name' => $tax_rate->name]), PASS_THROUGH);
  }
  else {
    drupal_set_title(t('Add new tax rate'));
  }

  $form['name'] = [
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#default_value' => !empty($tax_rate->name) ? $tax_rate->name : '',
    '#size' => 60,
    '#maxlength' => 128,
    '#required' => TRUE,
  ];

  $form['rate'] = [
    '#type' => 'textfield',
    '#title' => t('Rate'),
    '#default_value' => !empty($tax_rate->rate) ? $tax_rate->rate : '0.00',
    '#size' => 60,
    '#maxlength' => 128,
    '#required' => TRUE,
    '#element_validate' => ['element_validate_number'],
  ];

  // Add the buttons.
  $form['actions'] = ['#type' => 'actions'];

  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => t('Save'),
    '#weight' => 5,
    '#submit' => ['line_item_edit_tax_rate_form_submit'],
  ];

  if (!empty($tax_rate->tid) && line_item_tax_rate_access('delete', $tax_rate)) {
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#weight' => 15,
      '#submit' => ['line_item_edit_tax_rate_form_delete_submit'],
    ];
  }

  return $form;
}

/**
 * Validate handler for the tax rate add/edit form.
 *
 * @see line_item_edit_tax_rate_form()
 */
function line_item_edit_tax_rate_form_validate($form, &$form_state) {
  // The tax rate should be between 0.00 and 100.00.
  if ($form_state['values']['rate'] < 0 || $form_state['values']['rate'] > 100) {
    form_set_error('rate', t('The rate should be between 0.00 and 100.00'));
  }
}

/**
 * Form submission handler for line_item_edit_tax_rate_form().
 *
 * Saves a tax rate and outputs a status message to the user.
 *
 * @see line_item_edit_tax_rate_form()
 *
 * @throws Exception
 *   Thrown when the tax rate is submitted by a user that doesn't have a
 *   business.
 */
function line_item_edit_tax_rate_form_submit($form, &$form_state) {
  $values['name'] = $form_state['values']['name'];
  $values['rate'] = $form_state['values']['rate'];

  $business = business_get_active_business();
  if ($business instanceof Business) {
    $values += ['bid' => $business->bid];
  }
  else {
    throw new Exception('Tax rate cannot be submitted without a business.');
  }

  if (!empty($form_state['tax_rate']->tid)) {
    $values['tid'] = $form_state['tax_rate']->tid;
    $tax_rate = new TaxRate($values['bid'], $values['name'], $values['rate'], $values['tid']);
    $message = 'The changes have been saved.';
  }
  else {
    $tax_rate = new TaxRate($values['bid'], $values['name'], $values['rate']);
    $message = 'New tax rate has been added.';
  }

  if (line_item_tax_rate_save($tax_rate)) {
    // @codingStandardsIgnoreStart
    drupal_set_message(t($message));
    // @codingStandardsIgnoreEnd
  }
  else {
    drupal_set_message(t('The tax rate could not be saved.'), 'error');
    watchdog('tax_rate', 'Tax rate could not be saved.', WATCHDOG_ERROR);
  }
  $form_state['redirect'] = 'settings/tax-rates';
}

/**
 * Form submission handler for edit_tax_rate_form().
 *
 * Handles the 'Delete' button on the tax rate form.
 *
 * @see tax_rate_edit_form()
 * @see tax_rate_delete_confirm_form()
 */
function line_item_edit_tax_rate_form_delete_submit($form, &$form_state) {
  $destination = [];
  if (isset($_GET['destination'])) {
    $destination = drupal_get_destination();
    unset($_GET['destination']);
  }
  $tax_rate = $form_state['tax_rate'];
  $form_state['redirect'] = ['settings/tax-rates/' . $tax_rate->tid . '/delete', ['query' => $destination]];
}

/**
 * Form constructor for the tax rate deletion confirmation form.
 *
 * @param TaxRate $tax_rate
 *   The tax rate that needs to be deleted.
 *
 * @see tax_rate_delete_confirm_form_submit()
 */
function line_item_delete_tax_rate_confirm_form($form, &$form_state, TaxRate $tax_rate) {
  $form_state['tax_rate'] = $tax_rate;
  return confirm_form($form,
    t('Are you sure you want to delete %name?', ['%name' => $tax_rate->name]),
    'settings/tax-rates',
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );
}

/**
 * Form submission handler for tax_rate_delete_confirm_form().
 *
 * Deletes the tax rate.
 *
 * @see tax_rate_delete_confirm_form()
 */
function line_item_delete_tax_rate_confirm_form_submit($form, &$form_state) {
  $tax_rate = $form_state['tax_rate'];
  line_item_tax_rate_delete($tax_rate);
  drupal_set_message(t('The tax rate %name has been deleted.', ['%name' => $tax_rate->name]));
  $form_state['redirect'] = 'settings/tax-rates';
}

/**
 * Menu callback for TaxRate autocomplete.
 *
 * @param string $string
 *   The string containing the initial value to match with.
 *
 * @throws Exception
 *   Thrown when the current user does not own any business.
 */
function line_item_tax_rate_autocomplete($string = '') {
  $matches = [];

  // Fetch businesses from current user.
  if (!$bids = array_keys(business_get_businesses_from_user())) {
    throw new Exception('Current user does not own any business.');
  }

  if (!empty($string)) {
    $result = db_select('tax_rates')
      ->fields('tax_rates', ['tid', 'rate'])
      ->condition('rate', db_like($string) . '%', 'LIKE')
      ->range(0, 10)
      ->condition('bid', $bids, 'IN')
      ->execute()
      ->fetchAll();

    foreach ($result as $tax_rate) {
      $matches[$tax_rate->rate] = check_plain($tax_rate->rate);
    }
  }

  drupal_json_output($matches);
}
