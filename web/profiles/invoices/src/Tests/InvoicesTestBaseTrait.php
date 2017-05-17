<?php

declare (strict_types = 1);

namespace Drupal\invoices\Tests;

/**
 * Reusable methods for test base classes.
 */
trait InvoicesTestBaseTrait {

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
   * Creates the requested user accounts.
   */
  protected function createUsers() {
    foreach ($this->usersToCreate as $role) {
      $this->users[$role] = $this->createUserWithRole($role);
    }

    // If a business user was created, also create a business.
    if (in_array('business_owner', $this->usersToCreate)) {
      $this->business = $this->createBusiness();
      $this->business->save();
      $this->addBusinessToUser($this->business, $this->users['business_owner']);
    }
  }

}
