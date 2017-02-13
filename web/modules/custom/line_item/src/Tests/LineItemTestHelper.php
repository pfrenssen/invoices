<?php

declare (strict_types = 1);

namespace Drupal\line_item\Tests;

/**
 * Reusable test methods for testing line items.
 */
trait LineItemTestHelper {

  /**
   * Check if the properties of the given line item match the given values.
   *
   * @param \LineItem $line_item
   *   The line item entity to check.
   * @param array $values
   *   An associative array of values to check, keyed by property name.
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertLineItemProperties(\LineItem $line_item, array $values, $message = '', $group = 'Other') {
    if (isset($values['type'])) {
      unset($values['type']);
    }
    return $this->assertEntityProperties('line_item', $line_item, $values, $message, $group);
  }

  /**
   * Check if the line item database table is empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertLineItemTableEmpty($message = '', $group = 'Other') {
    $result = (bool) db_select('line_item', 'li')
      ->fields('li')
      ->execute()
      ->fetchAll();
    return $this->assertFalse($result, $message ?: 'The line item database table is empty.', $group);
  }

  /**
   * Check if the line item database table is not empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertLineItemTableNotEmpty($message = '', $group = 'Other') {
    $result = (bool) db_select('line_item', 'li')
      ->fields('li')
      ->execute()
      ->fetchAll();
    return $this->assertTrue($result, $message ?: 'The line item database table is not empty.', $group);
  }

  /**
   * Returns a newly created line item entity without saving it.
   *
   * This is intended for unit tests. It will set a random business ID. If you
   * are doing a functionality test use $this->createUiLineItem() instead.
   *
   * @param string $type
   *   Optional line item type, either 'product' or 'service'.
   * @param array $values
   *   An optional associative array of values, keyed by property name. Random
   *   values will be applied to all omitted properties.
   *
   * @return \LineItem
   *   A new line item entity.
   */
  function createLineItem($type = NULL, array $values = []) {
    // Provide some default values.
    $values += $this->randomLineItemValues($type);
    $line_item = line_item_create(['type' => $values['type']]);
    $this->updateLineItem($line_item, $values);

    return $line_item;
  }

  /**
   * Returns random values for all properties on the line item entity.
   *
   * @param string $type
   *   Optional line item type. If omitted a random line item type will be used.
   *
   * @returns array
   *   An associative array of random values, keyed by property name.
   */
  function randomLineItemValues($type = NULL) {
    $type = $type ?: $this->randomLineItemType();

    $values = [
      'bid' => $this->randomBusiness(),
      'field_line_item_description' => $this->randomString(),
      'field_line_item_discount' => $this->randomDecimal(),
      'field_line_item_quantity' => $this->randomDecimal(),
      'field_line_item_tax' => $this->randomDecimal(),
      'field_line_item_unit_cost' => $this->randomDecimal(),
      'type' => $type,
    ];

    if ($type == 'service') {
      $values['field_line_item_time_unit'] = array_rand([
        'minutes' => 'minutes',
        'hours' => 'hours',
        'days' => 'days',
        'weeks' => 'weeks',
        'months' => 'months',
        'years' => 'years',
      ]);
    }

    return $values;
  }

  /**
   * Generate the type for the line item.
   *
   * @return string
   *   The line item type.
   */
  function randomLineItemType() {
    return array_rand($this->getLineItemTypes());
  }

  /**
   * Returns the supported line item types.
   *
   * @return array
   *   An associative array of line item names, keyed by bundle name.
   */
  public function getLineItemTypes() {
    return [
      'product' => t('Product'),
      'service' => t('Service'),
    ];
  }

  /**
   * Generate a random decimal number.
   *
   * @return string
   *   A random generated decimal number.
   */
  function randomDecimal() {
    return rand(0, 99) . '.' . rand(0, 99);
  }

  /**
   * Updates the given line item with the given properties.
   *
   * @param \LineItem $line_item
   *   The line item entity to update.
   * @param array $values
   *   An associative array of values to apply to the entity, keyed by property
   *   name.
   */
  function updateLineItem(\LineItem $line_item, array $values) {
    unset($values['type']);
    $wrapper = entity_metadata_wrapper('line_item', $line_item);
    foreach ($values as $property => $value) {
      $wrapper->$property->set($value);
    }
  }

  /**
   * Returns a random line item from the database.
   *
   * @param string $type
   *   Optional line item type. Either 'product' or 'service'.
   *
   * @return \LineItem
   *   A random line item.
   */
  function randomLineItem($type = NULL) {
    $query = db_select('line_item', 'li')
      ->fields('li', ['lid'])
      ->orderRandom()
      ->range(0, 1);

    if ($type) {
      $query->condition('type', $type);
    }

    $lid = $query->execute()
      ->fetchColumn();

    return line_item_load($lid);
  }

  /**
   * Check if the tax rates database table is empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertTaxRatesTableEmpty($message = '', $group = 'Other') {
    $result = (bool) db_select('tax_rates', 'tr')
      ->fields('tr')
      ->execute()
      ->fetchAll();
    return $this->assertFalse($result, $message ?: 'The tax rates database table is empty.', $group);
  }

  /**
   * Check if the tax rates database table is not empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertTaxRatesTableNotEmpty($message = '', $group = 'Other') {
    $result = (bool) db_select('tax_rates', 'tr')
      ->fields('tr')
      ->execute()
      ->fetchAll();
    return $this->assertTrue($result, $message ?: 'The tax rates database table is not empty.', $group);
  }

  /**
   * Returns random values for all properties on the tax rate entity.
   *
   * @returns array
   *   An associative array of random values, keyed by property name.
   */
  function randomTaxRateValues() {
    $values = [
      'bid' => $this->randomBusiness()->identifier(),
      'name' => $this->randomString(),
      'rate' => $this->randomDecimal(),
    ];

    return $values;
  }

  /**
   * Returns a newly created tax rate without saving it.
   *
   * This is intended for unit tests. If you are doing a functionality test use
   * $this->createUiTaxRate() instead.
   *
   * @param array $values
   *   An optional associative array of values, keyed by property name. Random
   *   values will be applied to all omitted properties.
   *
   * @return \TaxRate
   *   A new tax rate object.
   */
  function createTaxRate(array $values = []) {
    // Provide default values.
    $values += $this->randomTaxRateValues();

    return new \TaxRate($values['bid'], $values['name'], $values['rate']);
  }

  /**
   * Check if the properties of the given tax rate match the given values.
   *
   * @param \TaxRate $tax_rate
   *   The tax rate to check.
   * @param array $values
   *   An associative array of values to check, keyed by property name.
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertTaxRateProperties(\TaxRate $tax_rate, array $values, $message = '', $group = 'Other') {
    $result = TRUE;
    $result &= $this->assertEqual($values['bid'], $tax_rate->bid);
    $result &= $this->assertEqual($values['name'], $tax_rate->name);
    return $result &= $this->assertEqual($values['rate'], $tax_rate->rate);
  }

  /**
   * Creates a new tax rate through the user interface.
   *
   * The saved tax rate is retrieved by the tax rate ID.
   *
   * @param array $values
   *   An optional associative array of values, keyed by property name. Random
   *   values will be applied to all omitted properties.
   *
   * @return \TaxRate
   *   A new TaxRate object.
   */
  function createUiTaxRate(array $values = []) {
    // Provide some default values.
    $values += $this->randomTaxRateValues();

    if (isset($values['bid'])) {
      unset($values['bid']);
    }

    // Convert the property values to form values and submit the form.
    $this->drupalPostForm('settings/tax-rates/add', $values, t('Save'));

    // Check that a success message is displayed.
    $this->assertRaw(t('New tax rate has been added.'));

    // Check target Url after redirection.
    $this->assertUrl('settings/tax-rates', [], 'The user is redirected to the tax rates overview after adding a new tax rate.');

    // Retrieve the saved tax rate by ID number and return it.
    $result = db_select('tax_rates', 'tr')
      ->fields('tr')
      ->condition('tr.name', $values['name'])
      ->condition('tr.rate', $values['rate'])
      ->orderBy('tr.tid', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchAllAssoc('tid');

    $this->assertEqual(count($result), 1, 'Tax rate was successfully created through the UI.');

    $result = reset($result);

    return new \TaxRate($result->bid, $result->name, $result->rate, $result->tid);
  }

  /**
   * Updates an existing tax rate through the user interface.
   *
   * The target tax rate is retrieved by the tax rate id.
   *
   * @param \TaxRate $tax_rate
   *   The TaxRate object that has to be updated.
   * @param array $values
   *   An optional associative array of values, keyed by property name.
   *
   * @return \TaxRate
   *   The updated TaxRate object.
   */
  function updateUiTaxRate(\TaxRate $tax_rate, array $values = []) {
    // Unset the values that cannot be changed through the UI.
    unset($values['bid']);
    unset($values['tid']);

    // Convert the new values to form values and submit the form.
    $this->drupalPostForm('settings/tax-rates/' . $tax_rate->tid . '/edit', $values, t('Save'));

    // Check that a success message is displayed.
    $this->assertRaw(t('The changes have been saved.'));

    // Retrieve the updated tax rate by id number and return it.
    $result = db_select('tax_rates', 'tr')
      ->fields('tr')
      ->condition('tr.name', $values['name'])
      ->condition('tr.rate', $values['rate'])
      ->orderBy('tr.tid', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchAllAssoc('tid');

    // Verify that the values are updated correctly.
    $this->assertEqual(count($result), 1, 'Tax rate was successfully updated through the UI.');

    $result = reset($result);

    return new \TaxRate($result->bid, $result->name, $result->rate, $result->tid);
  }

  /**
   * Deletes an existing tax rate through the user interface.
   *
   * The target tax rate is selected by the tax rate ID.
   *
   * @param \TaxRate $tax_rate
   *   The TaxRate object that has to be deleted.
   */
  function deleteUiTaxRate(\TaxRate $tax_rate) {
    // Get the specific tax rate delete form and delete the tax rate instance.
    $this->drupalPostForm('settings/tax-rates/' . $tax_rate->tid . '/delete', [], t('Delete'));

    // Attempt to retrieve the deleted tax rate by ID number.
    $result = db_select('tax_rates', 'tr')
      ->fields('tr', ['tid'])
      ->range(0, 1)
      ->execute()
      ->fetchAllAssoc('tid');

    // Verify that the tax rate has been deleted from the database.
    $this->assertFalse(count($result), 'Tax rate was successfully deleted from the database through the UI.');
  }

}
