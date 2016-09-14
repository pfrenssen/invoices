<?php

namespace Drupal\business;

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
  public function getBusinessesFromUser(AccountInterface $account = NULL);

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
  public function getBusinessIdsFromUser(AccountInterface $account = NULL, $reset = FALSE);

}
