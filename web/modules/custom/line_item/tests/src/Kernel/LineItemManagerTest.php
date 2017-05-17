<?php

declare (strict_types = 1);

namespace Drupal\Tests\line_item\Kernel;

/**
 * Unit tests for the Line item module.
 */
class LineItemManagerTest extends InvoicingIntegrationTestCase {

  use \Drupal\invoicing\Traits\BaseTestHelper;
  use \Drupal\invoicing\Traits\BusinessTestHelper;
  use \Drupal\invoicing\Traits\LineItemTestHelper;

  /**
   * Test business entities.
   *
   * @var Business[]
   *   An array of Business entities.
   */
  protected $businesses;

  /**
   * Test LineItem objects.
   *
   * @var LineItem[]
   *   An array of LineItem objects.
   */
  protected $lineItems;

  /**
   * Test TaxRate objects.
   *
   * @var TaxRate[]
   *   An array of TaxRate objects.
   */
  protected $taxRates;

  /**
   * {@inheritdoc}
   */
  protected $usersToCreate = array();

  /**
   * Returns test case metadata.
   */
  public static function getInfo() {
    return array(
      'name' => 'Unit test',
      'description' => 'Unit tests for the line item module.',
      'group' => 'Invoicing - Line item',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setup();

    // Create two test users, each owning one business.
    $this->businesses = array();

    for ($i = 0; $i < 2; $i++) {
      // Create a business.
      $this->businesses[$i] = $this->createBusiness();
      $this->businesses[$i]->save();

      // Create a user and link the business to it.
      $user = $this->createUser();
      $this->addBusinessToUser($this->businesses[$i], $user);
      $this->users[$i] = $user;
      $user = entity_metadata_wrapper('user', $this->users[$i]);
      $user->field_user_businesses->set(array($this->businesses[$i]->id()));
      $user->save();

      // Create two line items for the business.
      for ($j = 0; $j < 2; $j++) {
        $values = array('bid' => $this->businesses[$i]);
        $line_item = $this->createLineItem(NULL, $values);
        $line_item->save();
        $this->lineItems[] = $line_item;
      }

      // Create two tax rates for the business, making sure the values are
      // unique.
      $names = $rates = array();
      for ($j = 0; $j < 2; $j++) {
        do {
          $values = array(
            'bid' => $this->businesses[$i]->id(),
            'name' => $this->randomString(),
            'rate' => $this->randomDecimal(),
          );
        } while (in_array($values['name'], $names) || in_array($values['rate'], $rates));

        $tax_rate = new TaxRate($values['bid'], $values['name'], $values['rate']);
        line_item_tax_rate_save($tax_rate);
        $this->taxRates[] = $tax_rate;
      }
    }
  }

  /**
   * Executes the unit tests.
   *
   * It is faster to run all unit tests in a single test run.
   */
  public function testRunner() {
    $this->doTestLineItemIsOwnedByUser();
    $this->doTestTaxRateIsOwnedByUser();
    $this->doTestLineItemTaxRateAutocomplete();
  }

  /**
   * Tests line_item_is_owned_by_user().
   */
  public function doTestLineItemIsOwnedByUser() {
    // Define a list of which line items are owned by which users. The first two
    // belong to the first user, the last two to the second.
    $ownership = array(
      0 => array(0, 1),
      1 => array(2, 3),
    );

    // Test if line_item_is_owned_by_user() matches the expected ownership.
    foreach ($ownership as $user_key => $line_item_keys) {
      for ($i = 0; $i < 4; $i++) {
        $owned = in_array($i, $line_item_keys);
        $this->assertEqual($owned, line_item_is_owned_by_user($this->lineItems[$i], $this->users[$user_key]), format_string('Line item :item :owned by user :user.', array(
          ':item' => $i,
          ':owned' => $owned ? 'is owned' : 'is not owned',
          ':user' => $user_key,
        )));
      }
    }
  }

  /**
   * Tests line_item_tax_rate_is_owned_by_user().
   */
  public function doTestTaxRateIsOwnedByUser() {
    // Define a list of which tax rates are owned by which users. The first two
    // tax rates belong to the first user, the last two to the second user.
    $ownership = array(
      0 => array(0, 1),
      1 => array(2, 3),
    );

    // Test if line_item_tax_rate_is_owned_by_user() matches the expected
    // ownership.
    foreach ($ownership as $user_key => $tax_rate_keys) {
      for ($i = 0; $i < 4; $i++) {
        $owned = in_array($i, $tax_rate_keys);
        $string = 'Tax rate :tax_rate :owned by user :user.';
        $args = array(
          ':tax_rate' => $i,
          ':owned' => $owned ? 'is owned' : 'is not owned',
          ':user' => $user_key,
        );
        $this->assertEqual($owned, line_item_tax_rate_is_owned_by_user($this->taxRates[$i], $this->users[$user_key]), format_string($string, $args));
      }
    }
  }

  /**
   * Tests line_item_tax_rate_autocomplete().
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
