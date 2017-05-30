<?php

declare (strict_types = 1);

namespace Drupal\business;

use Drupal\business\Entity\BusinessInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for methods that get or set the business that owns an entity.
 */
interface BusinessOwnedInterface {

  /**
   * Returns the ID of the business this entity belongs to.
   *
   * @return int|null
   *   The ID of the business this entity belongs to, or NULL if no business is
   *   set yet.
   */
  public function getBusinessId() : ?int;

  /**
   * Returns the business this entity belongs to.
   *
   * @return \Drupal\business\Entity\BusinessInterface|null
   *   The business this entity belongs to, or NULL if no business is set yet.
   */
  public function getBusiness() : ?BusinessInterface;

  /**
   * Sets the ID of the business this entity belongs to.
   *
   * @param int $business_id
   *   The ID of the business this entity belongs to.
   *
   * @return self
   *   The updated entity.
   */
  public function setBusinessId(int $business_id) : EntityInterface;

  /**
   * Sets the business this entity belongs to.
   *
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business this entity belongs to.
   *
   * @return self
   *   The updated entity.
   */
  public function setBusiness(BusinessInterface $business) : EntityInterface;

}
