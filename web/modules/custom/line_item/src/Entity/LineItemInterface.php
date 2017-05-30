<?php

declare (strict_types = 1);

namespace Drupal\line_item\Entity;

use Drupal\business\BusinessOwnedInterface;
use Drupal\business\Entity\BusinessInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining line item entities.
 *
 * @ingroup line_item
 */
interface LineItemInterface extends BusinessOwnedInterface, EntityChangedInterface, ContentEntityInterface {

  /**
   * Gets the line item name.
   *
   * @return string
   *   Name of the line item.
   */
  public function getName();

  /**
   * Sets the line item name.
   *
   * @param string $name
   *   The line item name.
   *
   * @return \Drupal\line_item\Entity\LineItemInterface
   *   The called line item entity.
   */
  public function setName($name);

  /**
   * Gets the line item creation timestamp.
   *
   * @return int
   *   Creation timestamp of the line item.
   */
  public function getCreatedTime();

  /**
   * Sets the line item creation timestamp.
   *
   * @param int $timestamp
   *   The line item creation timestamp.
   *
   * @return \Drupal\line_item\Entity\LineItemInterface
   *   The called line item entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the tax rate of the line item as a percentage value.
   *
   * @return string
   *   The tax rate as a decimal value with 8 decimals.
   */
  public function getTaxRate();

}
