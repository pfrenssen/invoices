<?php

declare (strict_types = 1);

namespace Drupal\business;

use Drupal\business\Entity\Business;
use Drupal\business\Entity\BusinessInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Trait for entities that are owned by a business.
 */
trait BusinessOwnedTrait {

  /**
   * {@inheritdoc}
   */
  public function getBusinessId() : ?int {
    $business_id = $this->get('business')->target_id;
    return !empty($business_id) ? (int) $business_id : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getBusiness(): ?BusinessInterface {
    return Business::load($this->getBusinessId());
  }

  /**
   * {@inheritdoc}
   */
  public function setBusinessId(int $business_id) : EntityInterface {
    $this->set('business', $business_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setBusiness(BusinessInterface $business) : EntityInterface {
    return $this->setBusinessId($business->id());
  }

}
