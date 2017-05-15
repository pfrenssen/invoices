<?php

declare (strict_types = 1);

/**
 * @file
 * Contains \TaxRateSubContext.
 */

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;

/**
 * Behat step definitions for testing tax rates.
 */
class TaxRateSubContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  use Drupal\invoicing\Traits\BaseTestHelper;
  use Drupal\invoicing\Traits\BusinessTestHelper;
  use Drupal\invoicing\Traits\LineItemTestHelper;

  /**
   * Navigates to the tax rate overview.
   *
   * @When (I )visit the tax rate overview
   * @When I am on the tax rate overview
   */
  public function iAmOnTaxRateOverview() {
    $this->visitPath('/settings/tax-rates');
  }

  /**
   * Checks that the tax rate overview contains a given number of items.
   *
   * @param int $number
   *   The number of tax rates that should be seen.
   *
   * @Then I should see :number tax rate(s) in the overview
   */
  public function assertTaxRateCountInOverview($number) {
    $this->assertSession()->elementsCount('css', '.view-tax-rates tbody tr', intval($number));
  }

  /**
   * Creates tax rates with data provided in a table.
   *
   * Table format:
   * | name | rate  |
   * | VAT  | 21.00 |
   * | ...  | ...   |
   *
   * @param TableNode $tax_rates_table
   *   The tax rate data.
   *
   * @throws \Exception
   *   Thrown when no user is logged in, or when the logged in user has no
   *   business.
   *
   * @Given tax rate(s):
   */
  public function createTaxRates(TableNode $tax_rates_table) {
    if (!$this->getuser()->uid) {
      throw new \Exception('There is no current logged in user to create a tax rate for.');
    }

    $user = entity_metadata_wrapper('user', user_load($this->getUser()->uid));
    $businesses = $user->field_user_businesses->value();

    if (empty($businesses)) {
      throw new \Exception('Cannot create tax rates, the currently logged in user has no business.');
    }

    foreach ($tax_rates_table->getHash() as $tax_rate_hash) {
      $tax_rate = $this->createTaxRate(array(
        'bid' => $businesses[0]->id(),
        'name' => $tax_rate_hash['name'],
        'rate' => $tax_rate_hash['rate'],
      ));
      line_item_tax_rate_save($tax_rate);
    }
  }

}
