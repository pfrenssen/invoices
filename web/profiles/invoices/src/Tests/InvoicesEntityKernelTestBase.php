<?php

namespace Drupal\invoices\Tests;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\business\Tests\BusinessTestHelper;

/**
 * Base class for kernel tests for the Invoices platform.
 */
class InvoicesEntityKernelTestBase extends EntityKernelTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;
  use InvoicesTestBaseTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->connection = $this->container->get('database');

    // Create the requested user accounts.
    $this->createUsers();
  }

}
