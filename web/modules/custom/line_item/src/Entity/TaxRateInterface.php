<?php

declare (strict_types = 1);

namespace Drupal\line_item\Entity;

use Drupal\business\BusinessOwnedInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface for interacting with tax rates.
 *
 * @ingroup line_item
 */
interface TaxRateInterface extends BusinessOwnedInterface, ContentEntityInterface {

  /**
   * Returns the name of the tax rate.
   *
   * @return string
   *   The name of the tax rate.
   */
  public function getName() : string;

  /**
   * Sets the name of the tax rate.
   *
   * @param string $name
   *   The name of the tax rate.
   *
   * @return \Drupal\line_item\Entity\TaxRateInterface
   *   The updated tax rate entity.
   */
  public function setName(string $name) : TaxRateInterface;

  /**
   * Returns the actual tax rate.
   *
   * @return string
   *   The actual tax rate, which is a decimal number representing a percentage.
   */
  public function getRate() : string;

  /**
   * Sets the actual tax rate.
   *
   * @param string $rate
   *   The actual tax rate, which is a decimal number representing a percentage.
   *
   * @return \Drupal\line_item\Entity\TaxRateInterface
   *   The updated tax rate entity.
   */
  public function setRate(string $rate) : TaxRateInterface;

}
