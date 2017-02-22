<?php

declare (strict_types = 1);

namespace Drupal\business;

use Drupal\business\Entity\Business;
use Drupal\business\Entity\BusinessInterface;
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
  public function getBusinessesByUser(AccountInterface $account = NULL, bool $reset = FALSE) : array {
    if ($reset) {
      throw new \Exception('The $reset parameter is deprecated. Use ::resetCache().');
    }
    $bids = $this->getBusinessIdsByUser($account);
    return $this->storage->loadMultiple($bids);
  }

  /**
   * {@inheritdoc}
   */
  public function getBusinessIdsByUser(AccountInterface $account = NULL, bool $reset = FALSE) : array {
    if ($reset) {
      throw new \Exception('The $reset parameter is deprecated. Use ::resetCache().');
    }

    // Default to the logged in user.
    $account = $account ?: $this->currentUser;

    $uid = $account->id();

    // Check if the result has been statically cached.
    if (empty($this->bids[$uid])) {
      // Retrieve the business IDs.
      $query = \Drupal::database()->select('business', 'b');
      $query->join('user__field_user_businesses', 'u', 'b.bid = u.field_user_businesses_target_id');
      $query
        ->fields('b', ['bid'])
        ->condition('u.entity_id', $uid);
      $result = $query
        ->execute()
        ->fetchCol();

      // The entity storage always returns IDs as strings. Cast them to
      // integers, so we adhere to strict typing.
      $result = array_map(function ($value) {
        return (int) $value;
      }, $result);

      $this->bids[$uid] = $result;
    }

    return $this->bids[$uid];
  }

  /**
   * {@inheritdoc}
   */
  public function businessIsOwnedByUser(Business $business, AccountInterface $account = NULL) : bool {
    $account = $account ?: $this->currentUser;
    return in_array($business->id(), $this->getBusinessIdsByUser($account));
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveBusinessId(): ?int {
    $business_ids = $this->getBusinessIdsByUser();
    return $business_ids ? reset($business_ids) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveBusiness(): ?BusinessInterface {
    $business_id = $this->getActiveBusinessId();
    return $business_id ? $this->storage->load($business_id) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache() {
    $this->bids = [];
  }

}
