<?php

namespace Drupal\Tests\business\Kernel;

use Drupal\business\Tests\BusinessTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * CRUD tests for the Business module.
 *
 * @group business
 */
class BusinessCrudTest extends EntityKernelTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['address', 'business', 'telephone'];

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity type manager.
   *
   * @var \Drupal\core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->connection = $this->container->get('database');
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->installEntitySchema('business');
    $this->installConfig(['business']);
  }

  /**
   * Tests creating, reading, updating and deleting of businesses.
   */
  public function testBusinessCrud() {
    // Check that the database table exists and is empty.
    $this->assertTrue($this->connection->schema()->tableExists('business'), 'The business database table exists.');
    $this->assertBusinessTableEmpty('The business database is initially empty.');

    // @todo Throw a failure when there are more fields available on the entity
    //   than are currently being tested - expand test coverage.
    // Check if a new business can be saved to the database.
    $values = $this->randomBusinessValues();
    $business = $this->createBusiness($values);
    $business->save();
    $this->assertBusinessTableNotEmpty('The business database table is no longer empty after creating a business.');

    // Check that the business data can be read from the database.
    $reloaded_business = $this->reloadEntity($business);
    $this->assertBusinessProperties($reloaded_business, $values, 'The business that was saved to the database can be read correctly.');

    // Update the business and check that the new values were written to the
    // database.
    $new_values = $this->randomBusinessValues();
    $this->updateEntity($business, $new_values);
    $business->save();
    $this->assertBusinessProperties($business, $new_values, 'The business has been updated correctly.');
    $reloaded_business = $this->reloadEntity($business);
    $this->assertBusinessProperties($reloaded_business, $new_values, 'The business has been updated correctly in the database.');

    // Delete the business. The database should be empty again.
    $business->delete();
    $this->assertBusinessTableEmpty('The business can be deleted from the database.');
  }

}
