<?php

declare (strict_types = 1);

namespace Drupal\business;

use Drupal\business\Entity\Business;
use Drupal\business\Entity\BusinessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface for BusinessManager services.
 */
interface BusinessManagerInterface {

  /**
   * Returns the businesses from a given user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account of the user to get the businesses for. If omitted, the logged
   *   in user will be used.
   *
   * @return \Drupal\business\Entity\Business[]
   *   An array with all the businesses linked to this user. If no businesses
   *   were found an empty array will be returned.
   */
  public function getBusinessesByUser(AccountInterface $account = NULL) : array;

  /**
   * Get the business IDs from a given user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account of the user to get the business IDs for. If omitted, the
   *   logged in user will be used.
   * @param bool $reset
   *   Whether or not to reset the static cache. Defaults to FALSE.
   *
   * @return int[]
   *   An array with IDs of the businesses linked to this user. If no businesses
   *   are found an empty array will be returned.
   */
  public function getBusinessIdsByUser(AccountInterface $account = NULL, bool $reset = FALSE) : array;

  /**
   * Returns whether a given business is owned by a given user.
   *
   * @param \Drupal\business\Entity\Business $business
   *   The business to check.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Optional user account to check. Defaults to the currently logged in user.
   *
   * @return bool
   *   TRUE if the business is owned by the user, FALSE otherwise.
   */
  public function businessIsOwnedByUser(Business $business, AccountInterface $account = NULL) : bool;

  /**
   * Gets the ID of the active business for the current user.
   *
   * @todo This currently simply returns the first business owned by the logged
   *   in user. Once we are able to get the active business from the context
   *   (e.g. by using Spaces, PURL or Context) this should return that instead.
   *
   * @return int|null
   *   The ID of the active business for the current user, or NULL if no user is
   *   logged in, or the logged in user doesn't have any businesses.
   */
  public function getActiveBusinessId(): ?int;

  /**
   * Gets the active business for the current user.
   *
   * @todo This currently simply returns the first business owned by the logged
   *   in user. Once we are able to get the active business from the context
   *   (e.g. by using Spaces, PURL or Context) this should return that instead.
   *
   * @return \Drupal\business\Entity\BusinessInterface|null
   *   The active business for the current user, or NULL if no user is logged
   *   in, or the logged in user doesn't have any businesses.
   */
  public function getActiveBusiness(): ?BusinessInterface;

  /**
   * Resets the static cache.
   */
  public function resetCache();

}
