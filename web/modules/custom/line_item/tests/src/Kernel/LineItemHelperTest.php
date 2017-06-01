<?php

declare (strict_types = 1);

namespace Drupal\Tests\line_item\Kernel;

use Drupal\business\Tests\BusinessTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\invoices\Tests\InvoicesEntityKernelTestBase;
use Drupal\line_item\Entity\TaxRate;
use Drupal\line_item\LineItemHelper;
use Drupal\line_item\TaxRateHelper;
use Drupal\line_item\Tests\LineItemTestHelper;

/**
 * Tests for the LineItemHelper service.
 *
 * @group line_item
 *
 * @coversDefaultClass \Drupal\line_item\LineItemHelper
 */
class LineItemHelperTest extends InvoicesEntityKernelTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;
  use LineItemTestHelper;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'address',
    'business',
    'entity_reference_validators',
    'libphonenumber',
    'line_item',
    'options',
    'views',
  ];

  /**
   * Test business entities.
   *
   * @var \Drupal\business\Entity\BusinessInterface[]
   *   An array of Business entities.
   */
  protected $businesses;

  /**
   * Test line items.
   *
   * @var \Drupal\line_item\Entity\LineItemInterface[]
   *   An array of LineItem objects.
   */
  protected $lineItems;

  /**
   * Test tax rates.
   *
   * @var \Drupal\line_item\Entity\TaxRateInterface[]
   *   An array of TaxRate objects.
   */
  protected $taxRates;

  /**
   * {@inheritdoc}
   */
  protected $usersToCreate = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setup();

    $this->installEntitySchema('business');
    $this->installEntitySchema('line_item');
    $this->installEntitySchema('tax_rate');
    $this->installConfig(['business', 'line_item']);
    $this->installConfig(['business']);

    // Create two test users, each owning one business.
    $this->businesses = [];

    // Create the requested user accounts.
    foreach ($this->usersToCreate as $role) {
      $this->users[$role] = $this->createUserWithRole($role);
    }

    for ($i = 0; $i < 2; $i++) {
      // Create a business.
      $this->businesses[$i] = $this->createBusiness();
      $this->businesses[$i]->save();

      // Create a user and link the business to it.
      $user = $this->createUser();
      $this->addBusinessToUser($this->businesses[$i], $user);
      $user->save();
      $this->users[$i] = $user;

      // Create two line items for the business.
      for ($j = 0; $j < 2; $j++) {
        $values = ['business' => $this->businesses[$i]];
        $line_item = $this->createLineItem(NULL, $values);
        $line_item->save();
        $this->lineItems[] = $line_item;
      }

      // Create two tax rates for the business, making sure the values are
      // unique.
      $names = $rates = [];
      for ($j = 0; $j < 2; $j++) {
        do {
          $values = [
            'business' => $this->businesses[$i]->id(),
            'name' => $this->randomString(),
            'rate' => $this->randomDecimal(),
          ];
        } while (in_array($values['name'], $names) || in_array($values['rate'], $rates));

        $tax_rate = TaxRate::create($values);
        $tax_rate->save();
        $this->taxRates[] = $tax_rate;
      }
    }
  }

  /**
   * Executes the tests.
   *
   * Since the test setUp() is expensive, it is faster to run all tests in a
   * single test run.
   */
  public function testRunner() {
    $this->doTestLineItemIsOwnedByBusiness();
    $this->doTestTaxRateIsOwnedByBusiness();
    $this->doTestLineItemTaxRateAutocomplete();
  }

  /**
   * @covers ::lineItemIsOwnedByBusiness
   */
  public function doTestLineItemIsOwnedByBusiness() {
    // Define a list of which line items are owned by which businesses. The
    // first two belong to the first business, the last two to the second.
    $ownership = [
      0 => [0, 1],
      1 => [2, 3],
    ];

    // Test if the result matches the expected ownership.
    foreach ($ownership as $business_key => $line_item_keys) {
      for ($i = 0; $i < 4; $i++) {
        $owned = in_array($i, $line_item_keys);
        $this->assertEquals($owned, LineItemHelper::lineItemIsOwnedByBusiness($this->lineItems[$i], $this->businesses[$business_key]));
      }
    }
  }

  /**
   * @covers ::taxRateIsOwnedByBusiness
   *
   * @todo Move this to a separate TaxRateHelperTest.
   */
  public function doTestTaxRateIsOwnedByBusiness() {
    // Define a list of which tax rates are owned by which businesses. The first
    // two tax rates belong to the first business, the last two to the second
    // business.
    $ownership = [
      0 => [0, 1],
      1 => [2, 3],
    ];

    // Test if the result matches the expected ownership.
    foreach ($ownership as $business_key => $tax_rate_keys) {
      for ($i = 0; $i < 4; $i++) {
        $owned = in_array($i, $tax_rate_keys);
        $this->assertEquals($owned, TaxRateHelper::taxRateIsOwnedByBusiness($this->taxRates[$i], $this->businesses[$business_key]));
      }
    }
  }

  /**
   * Tests line_item_tax_rate_autocomplete().
   *
   * @todo Move this somewhere else.
   */
  public function doTestLineItemTaxRateAutocomplete() {
    global $user;
    module_load_include('inc', 'line_item', 'line_item.pages');

    // Create a new user that does not have a business.
    $this->users[2] = $this->createUser();

    // Check that an exception is thrown when the logged in user does not have a
    // business. This can be done by overwriting the global $user.
    $user = $this->users[2];
    $string = $this->randomString();
    $message = 'An exception is thrown when tax rate autocomplete results are requested for a user that doesn\'t have a business.';
    try {
      line_item_tax_rate_autocomplete($string);
      $this->fail($message);
    }
    catch (Exception $e) {
      $this->pass($message);
    }
  }

}
