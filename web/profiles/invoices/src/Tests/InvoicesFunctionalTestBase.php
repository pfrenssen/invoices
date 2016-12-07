<?php

namespace Drupal\invoices\Tests;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\business\Tests\BusinessTestHelper;

/**
 * Base class for integration tests for the invoicing platform.
 */
class InvoicesFunctionalTestBase extends BrowserTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'invoices';

  /**
   * A test business.
   *
   * @var \Drupal\business\Entity\Business
   */
  protected $business;

  /**
   * Test user accounts.
   *
   * @var \Drupal\Core\Session\AccountInterface[]
   *   An array of user objects.
   */
  protected $users;

  /**
   * A list of roles for which to create a user.
   *
   * @var string[]
   *   The names of the roles for which to create a user.
   */
  protected $usersToCreate = array(
    'administrator',
    'authenticated user',
    'business_owner',
    'client',
  );

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
    foreach ($this->usersToCreate as $role) {
      $this->users[$role] = $this->drupalCreateUserWithRole($role);
    }

    // If a business user was created, also create a business.
    if (in_array('business owner', $this->usersToCreate)) {
      $this->business = $this->createBusiness();
      $this->business->save();
      $this->addBusinessToUser($this->business, $this->users['business owner']);
    }
  }

}
