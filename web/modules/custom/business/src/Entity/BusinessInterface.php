<?php

declare (strict_types = 1);

namespace Drupal\business\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Business entities.
 *
 * @ingroup business
 */
interface BusinessInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Returns the name of the business.
   *
   * @return string
   *   The name of the Business.
   */
  public function getName() : string;

  /**
   * Sets the name of the business.
   *
   * @param string $name
   *   The business name to set.
   *
   * @return \Drupal\business\Entity\BusinessInterface
   *   The updated business entity.
   */
  public function setName(string $name) : BusinessInterface;

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
   *   The creation timestamp to set.
   *
   * @return \Drupal\business\Entity\BusinessInterface
   *   The updated business entity.
   */
  public function setCreatedTime(int $timestamp) : BusinessInterface;

}
