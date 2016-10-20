<?php

declare (strict_types = 1);

namespace Drupal\business;

use Drupal\business\Entity\Business;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Helper methods for managing businesses.
 */
class BusinessManager implements BusinessManagerInterface {

  /**
   * Static cache of business IDs, keyed by user ID.
   *
   * @var int[]
   */
  protected $bids = [];

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity storage handler for Business entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs the BusinessManager service.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user account.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->storage = $entity_type_manager->getStorage('business');
  }

  /**
   * {@inheritdoc}
   */
  public function getBusinessesFromUser(AccountInterface $account = NULL, bool $reset = FALSE) : array {
    if ($reset) {
      throw new \Exception('The $reset parameter is deprecated. Use ::resetCache().');
    }
    $bids = $this->getBusinessIdsFromUser($account);
    return $this->storage->loadMultiple($bids);
  }

  /**
   * {@inheritdoc}
   */
  public function getBusinessIdsFromUser(AccountInterface $account = NULL, bool $reset = FALSE) : array {
    if ($reset) {
      throw new \Exception('The $reset parameter is deprecated. Use ::resetCache().');
    }

    // Default to the logged in user.
    $account = $account ?: $this->currentUser;

    $uid = $account->id();

    // Check if the result has been statically cached.
    if (empty($this->bids[$uid])) {
      // Retrieve the business IDs.
      $result = $this->storage->getQuery()
        ->condition('uid', $uid)
        ->execute();
      $this->bids[$uid] = $result;
    }

    return $this->bids[$uid];
  }

  /**
   * {@inheritdoc}
   */
  public function businessIsOwnedByUser(Business $business, AccountInterface $account = NULL) : bool {
    $account = $account ?: $this->currentUser;
    return in_array($business->id(), $this->getBusinessIdsFromUser($account));
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache() {
    $this->bids = [];
  }

}
