<?php

declare (strict_types = 1);

namespace Drupal\client\Entity;

use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Client entities.
 *
 * @ingroup client
 */
interface ClientInterface extends RevisionableInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Client name.
   *
   * @return string
   *   Name of the Client.
   */
  public function getName() : string;

  /**
   * Sets the Client name.
   *
   * @param string $name
   *   The Client name.
   *
   * @return \Drupal\client\Entity\ClientInterface
   *   The called Client entity.
   */
  public function setName(string $name) : ClientInterface;

  /**
   * Gets the Client creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Client.
   */
  public function getCreatedTime() : int;

  /**
   * Sets the Client creation timestamp.
   *
   * @param int $timestamp
   *   The Client creation timestamp.
   *
   * @return \Drupal\client\Entity\ClientInterface
   *   The called Client entity.
   */
  public function setCreatedTime(int $timestamp);

}
