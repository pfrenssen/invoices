<?php

declare (strict_types = 1);

namespace Drupal\line_item;

use Drupal\business\Entity\BusinessInterface;
use Drupal\line_item\Entity\LineItemInterface;

/**
 * Helper methods for managing line items.
 */
class LineItemHelper implements LineItemHelperInterface {

  /**
   * {@inheritdoc}
   */
  public static function lineItemIsOwnedByBusiness(LineItemInterface $line_item, BusinessInterface $business) : bool {
    return $line_item->getBusiness()->id() === $business->id();
  }

}
