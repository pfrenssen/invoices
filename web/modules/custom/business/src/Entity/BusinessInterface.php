<?php

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
   * Gets the Business name.
   *
   * @return string
   *   Name of the Business.
   */
  public function getName();

  /**
   * Sets the Business name.
   *
   * @param string $name
   *   The Business name.
   *
   * @return \Drupal\business\Entity\BusinessInterface
   *   The called Business entity.
   */
  public function setName($name);

  /**
   * Gets the Business creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Business.
   */
  public function getCreatedTime();

  /**
   * Sets the Business creation timestamp.
   *
   * @param int $timestamp
   *   The Business creation timestamp.
   *
   * @return \Drupal\business\Entity\BusinessInterface
   *   The called Business entity.
   */
  public function setCreatedTime($timestamp);

}