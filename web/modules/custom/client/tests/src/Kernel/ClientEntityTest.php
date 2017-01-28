<?php

declare (strict_types = 1);

namespace Drupal\Tests\client\Kernel;

use Drupal\business\Tests\BusinessTestHelper;
use Drupal\client\Tests\ClientTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\invoices\Tests\ContentEntityTestBase;

/**
 * Tests for the Client entity.
 *
 * @group client
 */
class ClientEntityTest extends ContentEntityTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;
  use ClientTestHelper;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'address',
    'business',
    'client',
    'entity_reference_validators',
    'libphonenumber',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeId() {
    return 'client';
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityBundleIds() {
    return ['client'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getValues($type) {
    return $this->randomClientValues();
  }

}
