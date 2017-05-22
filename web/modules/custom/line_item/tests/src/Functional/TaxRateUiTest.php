<?php

/**
 * @file
 * Contains \TaxRateUITestCase.
 */

/**
 * Tests the tax rate user interface.
 */
class TaxRateUITestCase extends InvoicingIntegrationTestCase {

  use \Drupal\invoicing\Traits\BaseTestHelper;
  use \Drupal\invoicing\Traits\LineItemTestHelper;

  /**
   * {@inheritdoc}
   */
  protected $usersToCreate = [
    'business owner',
  ];

  /**
   * Returns test case metadata.
   */
  public static function getInfo() {
    return [
      'name' => 'Tax rate UI test',
      'description' => 'Tests the tax rate user interface.',
      'group' => 'Invoicing - Line item',
    ];
  }

  /**
   * Tests the tax rate user interface.
   */
  public function testUi() {
    // Log in as business owner.
    $this->drupalLogin($this->users['business owner']);

    // Create a tax rate through the UI using random values.
    $values = $this->randomTaxRateValues();
    $tax_rate = $this->createUiTaxRate($values);

    // Verify that the values are saved correctly.
    $loaded_tax_rate = line_item_tax_rate_load($tax_rate->tid);
    $this->assertTaxRateProperties($loaded_tax_rate, $values, 'The tax rate has been correctly saved to the database.');

    // Update the tax rate through the UI using new random values.
    $new_values = $this->randomTaxRateValues();
    $updated_tax_rate = $this->updateUiTaxRate($tax_rate, $new_values);

    // Verify that the values are updated correctly.
    $this->assertTaxRateProperties($updated_tax_rate, $new_values, 'The tax rate has been correctly updated to the database.');

    // Delete the tax rate through the UI.
    $this->deleteUiTaxRate($tax_rate);

    // Verify that the tax rate no longer is shown in the overview, thus the
    // overview is empty.
    $this->drupalGet('settings/tax-rates');
    $xpath = '//div[contains(@class, "view-empty") ]//a[@href="/settings/tax-rates/add"]';
    $this->assertXPathElements($xpath, 1, [], 'The no results message is shown.');
    $this->assertFalse($this->xpath('//div[contains(@class, "view-tax-rate")]//table'), 'The table containing tax rates is not shown.');
  }

}
