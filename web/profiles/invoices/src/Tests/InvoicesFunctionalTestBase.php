<?php

namespace Drupal\invoices\Tests;

use Drupal\Tests\BrowserTestBase;
use Drupal\business\Tests\BusinessTestHelper;

/**
 * Base class for integration tests for the Invoices platform.
 */
class InvoicesFunctionalTestBase extends BrowserTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;
  use InvoicesTestBaseTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'invoices';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');

    // Create the requested user accounts.
    $this->createUsers();
  }

}
