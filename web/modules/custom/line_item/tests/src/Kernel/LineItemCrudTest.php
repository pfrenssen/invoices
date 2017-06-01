<?php

declare (strict_types = 1);

namespace Drupal\Tests\line_item\Kernel;

use Drupal\business\Tests\BusinessTestHelper;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\line_item\Tests\LineItemTestHelper;

/**
 * CRUD tests for the Line Item module.
 *
 * @group line_item
 */
class LineItemCRUDTest extends EntityKernelTestBase {

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
    $this->installEntitySchema('line_item');
    $this->installConfig(['business', 'line_item']);

    // Create a business to reference.
    $this->business = $this->createBusiness();
    $this->business->save();
  }

  /**
   * Tests creating, reading, updating and deleting of line items.
   */
  public function testLineItemCrud() {
    // Check that the database table exists and is empty.
    $this->assertTrue($this->connection->schema()->tableExists('line_item'), 'The line_item database table exists.');
    $this->assertLineItemTableEmpty();

    // Check if a new line item can be saved to the database.
    $values = $this->randomLineItemValues();
    $line_item = $this->createLineItem($values['type'], $values);
    $line_item->save();
    $this->assertLineItemTableNotEmpty();

    // Check that the line item data can be read from the database.
    $retrieved_line_item = $this->loadUnchangedLineItem($line_item->id());
    $this->assertLineItemProperties($retrieved_line_item, $values);

    // Update the line item and check that the new values were written to the
    // database.
    $new_values = $this->randomLineItemValues($values['type']);
    unset($new_values['type']);
    $this->updateEntity($line_item, $new_values);
    $line_item->save();
    $this->assertLineItemProperties($line_item, $new_values);

    // Delete the line item. The database should be empty again.
    $line_item->delete();
    $this->assertLineItemTableEmpty();

    // Test that an exception is thrown when trying to save a line item without
    // the required properties 'business' and 'type'.
    foreach (array('business', 'type') as $property) {
      $arguments = ['%property' => $property];
      $message = new FormattableMarkup('An exception is thrown when trying to save a line item without the required property %property.', $arguments);

      $line_item = $this->createLineItem($values['type'], $values);
      unset($line_item->$property);

      try {
        $line_item->save();
        $this->fail($message);
      }
      catch (EntityStorageException $e) {
        $this->assertTrue(TRUE, $message);
      }
    }
  }

  /**
   * Tests creating, reading, updating and deleting of tax rates.
   */
  public function testTaxRateCrud() {
    // Check that the database table exists and is empty.
    $this->assertTrue(db_table_exists('tax_rates'), 'The tax_rates database table exists.');
    $this->assertTaxRatesTableEmpty('The tax rates database is initially empty.');

    // Check if a new tax rate can be saved to the database.
    $values = $this->randomTaxRateValues();
    $tax_rate = new TaxRate($values['bid'], $values['name'], $values['rate']);
    $tid = line_item_tax_rate_save($tax_rate);
    $this->assertTaxRatesTableNotEmpty('The tax rate database table is no longer empty after creating a tax rate.');

    // Check that the tax rate data can be read from the database.
    $retrieved_tax_rate = line_item_tax_rate_load($tid);
    $this->assertTaxRateProperties($retrieved_tax_rate, $values, 'The tax rate that was saved to the database can be read correctly.');

    // Update the tax rate and check that the new values were written to the
    // database.
    $new_values = $this->randomTaxRateValues();
    $new_values['tid'] = $retrieved_tax_rate->tid;
    $new_tax_rate = new TaxRate($new_values['bid'], $new_values['name'], $new_values['rate'], $new_values['tid']);
    $tid = line_item_tax_rate_save($new_tax_rate);

    $retrieved_tax_rate = line_item_tax_rate_load($tid);
    $this->assertTaxRateProperties($retrieved_tax_rate, $new_values, 'The tax rate has been updated correctly.');

    // Delete the tax rate. The database should be empty again.
    line_item_tax_rate_delete($retrieved_tax_rate);
    $this->assertTaxRatesTableEmpty('The tax rate can be deleted from the database.');
  }

}
