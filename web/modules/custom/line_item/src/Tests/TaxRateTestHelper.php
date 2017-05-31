<?php

declare (strict_types = 1);

namespace Drupal\line_item\Tests;

use Drupal\line_item\Entity\TaxRate;
use Drupal\line_item\Entity\TaxRateInterface;

/**
 * Reusable test methods for testing tax rates.
 */
trait TaxRateTestHelper {

  /**
   * Checks if the tax rates database table is empty.
   */
  function assertTaxRatesTableEmpty() : void {
    $result = (bool) $this->connection->select('tax_rate', 'tr')
      ->fields('tr')
      ->execute()
      ->fetchAll();
    $this->assertFalse($result);
  }

  /**
   * Checks if the tax rates database table is not empty.
   */
  function assertTaxRatesTableNotEmpty() : void {
    $result = (bool) $this->connection->select('tax_rate', 'tr')
      ->fields('tr')
      ->execute()
      ->fetchAll();
    $this->assertTrue($result);
  }

  /**
   * Returns random values for all properties on the tax rate entity.
   *
   * @returns array
   *   An associative array of random values, keyed by property name.
   */
  function randomTaxRateValues() : array {
    return [
      'business' => $this->randomBusiness()->id(),
      'name' => $this->randomString(),
      'rate' => $this->randomDecimal(),
    ];
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
   * @return \Drupal\line_item\Entity\TaxRateInterface
   *   A new tax rate object.
   */
  function createTaxRate(array $values = []) : TaxRateInterface {
    // Provide default values.
    $values += $this->randomTaxRateValues();

    return TaxRate::create($values);
  }

  /**
   * Checks if the properties of the given tax rate match the given values.
   *
   * @param \Drupal\line_item\Entity\TaxRateInterface $tax_rate
   *   The tax rate to check.
   * @param array $values
   *   An associative array of values to check, keyed by property name.
   *
   * @deprecated Use BaseTestHelper::assertEntityFieldValues() instead.
   */
  function assertTaxRateProperties(TaxRateInterface $tax_rate, array $values) : void {
    $this->assertEntityFieldValues($tax_rate, $values);
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
   * @return \Drupal\line_item\Entity\TaxRateInterface
   *   A new TaxRate object.
   */
  function createUiTaxRate(array $values = []) : TaxRateInterface {
    // Provide some default values.
    $values += $this->randomTaxRateValues();

    if (isset($values['business'])) {
      unset($values['business']);
    }

    // Convert the property values to form values and submit the form.
    $this->drupalPostForm('settings/tax-rates/add', $values, t('Save'));

    // Check that a success message is displayed.
    $this->assertRaw(t('New tax rate has been added.'));

    // Check target Url after redirection.
    $this->assertUrl('settings/tax-rates', [], 'The user is redirected to the tax rates overview after adding a new tax rate.');

    // Retrieve the saved tax rate by ID number and return it.
    $result = db_select('tax_rate', 'tr')
      ->fields('tr')
      ->condition('tr.name', $values['name'])
      ->condition('tr.rate', $values['rate'])
      ->orderBy('tr.tid', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchAllAssoc('tid');

    $this->assertEqual(count($result), 1, 'Tax rate was successfully created through the UI.');

    $result = reset($result);

    return new \TaxRate($result->business, $result->name, $result->rate, $result->tid);
  }

  /**
   * Updates an existing tax rate through the user interface.
   *
   * The target tax rate is retrieved by the tax rate id.
   *
   * @param \Drupal\line_item\Entity\TaxRateInterface $tax_rate
   *   The TaxRate object that has to be updated.
   * @param array $values
   *   An optional associative array of values, keyed by property name.
   *
   * @return \Drupal\line_item\Entity\TaxRateInterface
   *   The updated TaxRate object.
   */
  function updateUiTaxRate(TaxRateInterface $tax_rate, array $values = []) : TaxRateInterface {
    // Unset the values that cannot be changed through the UI.
    unset($values['business']);
    unset($values['tid']);

    // Convert the new values to form values and submit the form.
    $this->drupalPostForm('settings/tax-rates/' . $tax_rate->tid . '/edit', $values, t('Save'));

    // Check that a success message is displayed.
    $this->assertRaw(t('The changes have been saved.'));

    // Retrieve the updated tax rate by id number and return it.
    $result = db_select('tax_rate', 'tr')
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

    return new \TaxRate($result->business, $result->name, $result->rate, $result->tid);
  }

  /**
   * Deletes an existing tax rate through the user interface.
   *
   * The target tax rate is selected by the tax rate ID.
   *
   * @param \Drupal\line_item\Entity\TaxRateInterface $tax_rate
   *   The TaxRate object that has to be deleted.
   */
  function deleteUiTaxRate(TaxRateInterface $tax_rate) : void {
    // Get the specific tax rate delete form and delete the tax rate instance.
    $this->drupalPostForm('settings/tax-rates/' . $tax_rate->tid . '/delete', [], t('Delete'));

    // Attempt to retrieve the deleted tax rate by ID number.
    $result = db_select('tax_rate', 'tr')
      ->fields('tr', ['tid'])
      ->range(0, 1)
      ->execute()
      ->fetchAllAssoc('tid');

    // Verify that the tax rate has been deleted from the database.
    $this->assertFalse(count($result), 'Tax rate was successfully deleted from the database through the UI.');
  }

  /**
   * Returns the unchanged, i.e. not modified, tax raye from the database.
   *
   * @param int $id
   *   The ID of the tax rate to return.
   *
   * @return \Drupal\line_item\Entity\TaxRateInterface
   *   The tax rate.
   */
  function loadUnchangedTaxRate(int $id) : TaxRateInterface {
    return $this->loadUnchangedEntity('tax_rate', $id);
  }

}
