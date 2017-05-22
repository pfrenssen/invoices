<?php

declare (strict_types = 1);

namespace Drupal\Tests\line_item\Kernel;

use Drupal\business\Tests\BusinessTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\invoices\Tests\InvoicesEntityKernelTestBase;
use Drupal\line_item\Tests\LineItemTestHelper;

/**
 * Tests for the LineItemManager service.
 *
 * @group line_item
 *
 * @coversDefaultClass \Drupal\line_item\LineItemManager
 */
class LineItemManagerTestEntity extends InvoicesEntityKernelTestBase {

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
//    'line_item',
//    'options',
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
   * Test LineItem objects.
   *
   * @var \Drupal\line_item\Entity\LineItemInterface[]
   *   An array of LineItem objects.
   */
  protected $lineItems;

  /**
   * Test TaxRate objects.
   *
   * @var \TaxRate[]
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
//    $this->installEntitySchema('line_item');
    //$this->installConfig(['business', 'line_item']);
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
      $this->users[$i] = $this->createUser();
      $user = entity_metadata_wrapper('user', $this->users[$i]);
      $user->field_user_businesses->set(array($this->businesses[$i]->id()));
      $user->save();

      // Create two line items for the business.
      for ($j = 0; $j < 2; $j++) {
        $values = ['bid' => $this->businesses[$i]];
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
            'bid' => $this->businesses[$i]->id(),
            'name' => $this->randomString(),
            'rate' => $this->randomDecimal(),
          ];
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
    $ownership = [
      0 => [0, 1],
      1 => [2, 3],
    ];

    // Test if line_item_is_owned_by_user() matches the expected ownership.
    foreach ($ownership as $user_key => $line_item_keys) {
      for ($i = 0; $i < 4; $i++) {
        $owned = in_array($i, $line_item_keys);
        $this->assertEqual($owned, line_item_is_owned_by_user($this->lineItems[$i], $this->users[$user_key]), format_string('Line item :item :owned by user :user.', [
          ':item' => $i,
          ':owned' => $owned ? 'is owned' : 'is not owned',
          ':user' => $user_key,
        ]));
      }
    }
  }

  /**
   * Tests line_item_tax_rate_is_owned_by_user().
   */
  public function doTestTaxRateIsOwnedByUser() {
    // Define a list of which tax rates are owned by which users. The first two
    // tax rates belong to the first user, the last two to the second user.
    $ownership = [
      0 => [0, 1],
      1 => [2, 3],
    ];

    // Test if line_item_tax_rate_is_owned_by_user() matches the expected
    // ownership.
    foreach ($ownership as $user_key => $tax_rate_keys) {
      for ($i = 0; $i < 4; $i++) {
        $owned = in_array($i, $tax_rate_keys);
        $string = 'Tax rate :tax_rate :owned by user :user.';
        $args = [
          ':tax_rate' => $i,
          ':owned' => $owned ? 'is owned' : 'is not owned',
          ':user' => $user_key,
        ];
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
