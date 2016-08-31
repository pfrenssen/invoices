<?php

namespace Drupal\Tests\business\Kernel;

use Drupal\business\Tests\BusinessTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\KernelTests\KernelTestBase;

/**
 * CRUD tests for the Business module.
 *
 * @group business
 */
class BusinessCrudTest extends KernelTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;

  /**
   * {@inheritdoc}
   */
  public static $modules = [];

  /**
   * Tests creating, reading, updating and deleting of businesses.
   */
  public function testBusinessCrud() {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    // Check that the database table exists and is empty.
    $this->assertTrue(db_table_exists('business'), 'The business database table exists.');
    $this->assertBusinessTableEmpty('The business database is initially empty.');

    // @todo Throw a failure when there are more fields available on the entity
    //   than are currently being tested - expand test coverage.
    // Check if a new business can be saved to the database.
    $values = $this->randomBusinessValues();
    $business = $this->createBusiness($values);
    $business->save();
    $this->assertBusinessTableNotEmpty('The business database table is no longer empty after creating a business.');

    // Check that the business data can be read from the database.
    $retrieved_business = business_load($business->bid);
    $this->assertBusinessProperties($retrieved_business, $values, 'The business that was saved to the database can be read correctly.');

    // Update the business and check that the new values were written to the
    // database.
    $new_values = $this->randomBusinessValues();
    $this->updateBusiness($business, $new_values);
    $business->save();
    $this->assertBusinessProperties($business, $new_values, 'The business has been updated correctly.');

    // Delete the business. The database should be empty again.
    $business->delete();
    $this->assertBusinessTableEmpty('The business can be deleted from the database.');
  }

}
