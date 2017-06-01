<?php

declare (strict_types = 1);

namespace Drupal\Tests\line_item\Kernel;

use Drupal\business\Tests\BusinessTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\line_item\Entity\TaxRate;
use Drupal\line_item\Tests\TaxRateTestHelper;

/**
 * CRUD tests for the tax rate entity.
 *
 * @group line_item
 */
class TaxRateCRUDTest extends EntityKernelTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;
  use TaxRateTestHelper;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'address',
    'business',
    'entity_reference_validators',
    'options',
    'libphonenumber',
    'line_item',
    'views',
  ];

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
   * A test business.
   *
   * @var \Drupal\business\Entity\BusinessInterface
   */
  protected $business;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->connection = $this->container->get('database');
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->installEntitySchema('business');
    $this->installEntitySchema('tax_rate');
    $this->installConfig(['business', 'line_item']);

    // Create a business to reference.
    $this->business = $this->createBusiness();
    $this->business->save();
  }

  /**
   * Tests creating, reading, updating and deleting of tax rates.
   */
  public function testTaxRateCrud() {
    // Check that the database table exists and is empty.
    $this->assertTrue($this->connection->schema()->tableExists('tax_rate'), 'The tax_rates database table exists.');
    $this->assertTaxRatesTableEmpty();

    // Check if a new tax rate can be saved to the database.
    $values = $this->randomTaxRateValues();
    $tax_rate = $this->createTaxRate($values);
    $tax_rate->save();
    $this->assertTaxRatesTableNotEmpty();

    // Check that the tax rate data can be read from the database.
    $retrieved_tax_rate = $this->loadUnchangedTaxRate($tax_rate->id());
    $this->assertEntityFieldValues($retrieved_tax_rate, $values);

    // Update the tax rate and check that the new values were written to the
    // database.
    $new_values = $this->randomTaxRateValues();
    $tax_rate
      ->setName($new_values['name'])
      ->setRate($new_values['rate'])
      ->setBusinessId($new_values['business'])
      ->save();

    $retrieved_tax_rate = $this->loadUnchangedTaxRate($tax_rate->id());
    $this->assertEntityFieldValues($retrieved_tax_rate, $new_values);

    // Delete the tax rate. The database should be empty again.
    $retrieved_tax_rate->delete();
    $this->assertTaxRatesTableEmpty();
  }

}
