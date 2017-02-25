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
   * A test business.
   *
   * @var \Drupal\business\Entity\BusinessInterface
   */
  protected $business;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'address',
    'business',
    'client',
    'entity_reference_validators',
    'libphonenumber',
    'link',
    'user',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Also install the entity schema of the Business entity, because the active
    // business needs to be retrieved from the database when a new Client entity
    // is created.
    $this->installEntitySchema('business');
    $this->installConfig(['business']);

    // Create a test business that can be associated with clients.
    $this->business = $this->createBusiness();
    $this->business->save();
  }

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
    return $this->randomClientValues() + ['business' => $this->business->id()];
  }

}
