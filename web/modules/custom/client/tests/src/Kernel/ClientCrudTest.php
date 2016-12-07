<?php

declare (strict_types = 1);

namespace Drupal\Tests\client\Kernel;

use Drupal\business\Tests\BusinessTestHelper;
use Drupal\client\Tests\ClientTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * CRUD tests for the Client module.
 *
 * @group client
 */
class ClientCrudTest extends EntityKernelTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;
  use ClientTestHelper;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'address',
    'business',
    'client',
    'entity_reference_validators',
    'libphonenumber',
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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->connection = $this->container->get('database');
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->installEntitySchema('business');
    $this->installConfig(['business']);

    $this->installEntitySchema('client');
    $this->installConfig(['client']);

    // Create a business to reference.
    $this->business = $this->createBusiness();
    $this->business->save();
  }

  /**
   * Tests creating, reading, updating and deleting of clients.
   */
  public function testClientCrud() {
    // Check that the database table exists and is empty.
    $this->assertTrue($this->connection->schema()->tableExists('client'), 'The client database table exists.');
    $this->assertClientTableEmpty('The client database is initially empty.');

    // Check if a new client can be saved to the database.
    $values = $this->randomClientValues();
    $values['bid'] = $this->randomBusiness();
    $client = $this->createClient($values);
    $client->save();
    $this->assertClientTableNotEmpty('The client database table is no longer empty after creating a client.');

    // Check that the client data can be read from the database.
    $retrieved_client = client_load($client->cid);
    $this->assertClientProperties($retrieved_client, $values, 'The client that was saved to the database can be read correctly.');

    // Update the client and check that the new values were written to the
    // database.
    $new_values = $this->randomClientValues();
    $this->updateClient($client, $new_values);
    $client->save();
    $this->assertClientProperties($client, $new_values, 'The client has been updated correctly.');

    // Delete the client. The database should be empty again.
    $client->delete();
    $this->assertClientTableEmpty('The client can be deleted from the database.');
  }

}
