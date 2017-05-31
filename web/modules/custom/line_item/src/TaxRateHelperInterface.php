<?php

declare (strict_types = 1);

namespace Drupal\line_item;

use Drupal\business\Entity\BusinessInterface;
use Drupal\line_item\Entity\TaxRateInterface;

/**
 * Interface for TaxRateHelper services.
 */
interface TaxRateHelperInterface {

  /**
   * Returns whether a given tax rate is owned by a given business.
   *
   * @param \Drupal\line_item\Entity\TaxRateInterface $taxRate
   *   The tax rate to check.
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business to check.
   *
   * @return bool
   *   TRUE if the tax rate is owned by the business, FALSE otherwise.
   */
  public static function taxRateIsOwnedByBusiness(TaxRateInterface $taxRate, BusinessInterface $business) : bool;

}
