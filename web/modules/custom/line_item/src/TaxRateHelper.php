<?php

declare (strict_types = 1);

namespace Drupal\line_item;

use Drupal\business\Entity\BusinessInterface;
use Drupal\line_item\Entity\TaxRateInterface;

/**
 * Helper methods for managing tax rates.
 */
class TaxRateHelper implements TaxRateHelperInterface {

  /**
   * {@inheritdoc}
   */
  public static function taxRateIsOwnedByBusiness(TaxRateInterface $taxRate, BusinessInterface $business) : bool {
    return $taxRate->getBusiness()->id() === $business->id();
  }

}
