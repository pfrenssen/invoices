<?php

declare (strict_types = 1);

namespace Drupal\Tests\business\Kernel;

use Drupal\business\Tests\BusinessTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\invoices\Tests\ContentEntityTestBase;

/**
 * Tests for the Business entity.
 *
 * @group business
 */
class BusinessEntityTest extends ContentEntityTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'address',
    'business',
    'entity_reference_validators',
    'libphonenumber',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeId() {
    return 'business';
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityBundleIds() {
    return ['business'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getValues($type) {
    return $this->randomBusinessValues();
  }

}
