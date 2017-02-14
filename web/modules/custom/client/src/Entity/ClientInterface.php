<?php

declare (strict_types = 1);

namespace Drupal\client\Entity;

use Drupal\business\Entity\BusinessInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Client entities.
 *
 * @ingroup client
 */
interface ClientInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, RevisionLogInterface {

  /**
   * Returns the client name.
   *
   * @return string
   *   The client name.
   */
  public function getName() : string;

  /**
   * Sets the client name.
   *
   * @param string $name
   *   The client name.
   *
   * @return \Drupal\client\Entity\ClientInterface
   *   The updated client.
   *
   * @todo type hint on self.
   */
  public function setName(string $name) : ClientInterface;

  /**
   * Returns the business this client belongs to.
   *
   * @return \Drupal\business\Entity\BusinessInterface
   *   The business this client belongs to.
   */
  public function getBusiness() : BusinessInterface;

  /**
   * Sets the business this client belongs to.
   *
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business this client belongs to.
   *
   * @return self
   *   The updated client.
   */
  public function setBusiness(BusinessInterface $business) : ClientInterface;

  /**
   * Returns the creation timestamp.
   *
   * @return int
   *   The creation timestamp.
   */
  public function getCreatedTime() : int;

  /**
   * Sets the creation timestamp.
   *
   * @param int $timestamp
   *   The creation timestamp.
   *
   * @return \Drupal\client\Entity\ClientInterface
   *   The updated client.
   */
  public function setCreatedTime(int $timestamp);

}
