<?php

namespace Drupal\invoices\Tests;

use Drupal\KernelTests\KernelTestBase;
use Drupal\business\Tests\BusinessTestHelper;

/**
 * Base class for kernel tests for the Invoices platform.
 */
class InvoicesKernelTestBase extends KernelTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;
  use InvoicesTestBaseTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create the requested user accounts.
    //$this->createUsers();
  }

}
