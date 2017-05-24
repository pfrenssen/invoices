<?php

declare (strict_types = 1);

namespace Drupal\line_item;

use Drupal\business\Entity\BusinessInterface;
use Drupal\line_item\Entity\LineItemInterface;

/**
 * Interface for LineItemManager services.
 */
interface LineItemManagerInterface {

  /**
   * Returns whether a given line item is owned by a given business.
   *
   * @param \Drupal\line_item\Entity\LineItemInterface $line_item
   *   The line item to check.
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business to check.
   *
   * @return bool
   *   TRUE if the line item is owned by the user, FALSE otherwise.
   */
  public static function isOwnedByBusiness(LineItemInterface $line_item, BusinessInterface $business) : bool;

}
