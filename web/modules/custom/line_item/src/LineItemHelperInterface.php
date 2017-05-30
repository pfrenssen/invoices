<?php

declare (strict_types = 1);

namespace Drupal\line_item;

use Drupal\business\Entity\BusinessInterface;
use Drupal\line_item\Entity\LineItemInterface;

/**
 * Interface for LineItemHelper services.
 */
interface LineItemHelperInterface {

  /**
   * Returns whether a given line item is owned by a given business.
   *
   * @param \Drupal\line_item\Entity\LineItemInterface $line_item
   *   The line item to check.
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business to check.
   *
   * @return bool
   *   TRUE if the line item is owned by the business, FALSE otherwise.
   */
  public static function lineItemIsOwnedByBusiness(LineItemInterface $line_item, BusinessInterface $business) : bool;

}
