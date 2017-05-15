<?php

namespace Drupal\line_item\Entity;

use Drupal\business\Entity\BusinessInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining line item entities.
 *
 * @ingroup line_item
 */
interface LineItemInterface extends EntityChangedInterface, ContentEntityInterface {

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
   * Returns the ID of the business this client belongs to.
   *
   * @return int|null
   *   The ID of the business this client belongs to, or NULL if no business is
   *   set yet.
   */
  public function getBusinessId() : ?int;

  /**
   * Returns the business this client belongs to.
   *
   * @return \Drupal\business\Entity\BusinessInterface|null
   *   The business this client belongs to, or NULL if no business is set yet.
   */
  public function getBusiness() : ?BusinessInterface;

  /**
   * Sets the business ID this client belongs to.
   *
   * @param int $business_id
   *   The ID of the business this client belongs to.
   *
   * @return self
   *   The updated client.
   */
  public function setBusinessId(int $business_id) : LineItemInterface;

  /**
   * Sets the business this client belongs to.
   *
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business this client belongs to.
   *
   * @return self
   *   The updated client.
   */
  public function setBusiness(BusinessInterface $business) : LineItemInterface;

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
