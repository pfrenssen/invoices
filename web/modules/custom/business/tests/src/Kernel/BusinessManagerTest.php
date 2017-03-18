<?php

declare (strict_types = 1);

namespace Drupal\Tests\business\Kernel;

use Drupal\Core\Session\AccountInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\business\Entity\Business;
use Drupal\business\Tests\BusinessTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;

/**
 * Tests the BusinessManager service.
 *
 * @group business
 *
 * @coversDefaultClass \Drupal\business\BusinessManager
 */
class BusinessManagerTest extends EntityKernelTestBase {

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
   * Test user accounts.
   *
   * @var \Drupal\Core\Session\AccountInterface[]
   *   An array of user objects.
   */
  protected $users;

  /**
   * Test business entities.
   *
   * @var \Drupal\business\Entity\BusinessInterface[]
   *   An array of Business entities.
   */
  protected $businesses;

  /**
   * The business manager service. This is the system under test.
   *
   * @var \Drupal\business\BusinessManagerInterface
   */
  protected $businessManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('business');
    $this->installConfig(['business']);

    $this->businessManager = $this->container->get('business.manager');

    // Create some test users and businesses. Link two businesses to the first
    // user and one business to the second user. A third user is created without
    // any businesses.
    $this->businesses = [];
    $this->users = [];
    for ($user_key = 0; $user_key < 3; $user_key++) {
      $user = $this->createUser();
      for ($business_count = 2 - $user_key; $business_count > 0; $business_count--) {
        $business = $this->createBusiness();
        $business->save();
        $this->addBusinessToUser($business, $user);
        $this->businesses[] = $business;
      }
      $this->users[$user_key] = $user;
    }
  }

  /**
   * Tests the retrieval of the businesses associated with a given user.
   *
   * @covers ::getBusinessesByUser
   */
  public function testGetBusinessesFromUser() {
    $business_key = 0;
    for ($user_key = 0; $user_key < 2; $user_key++) {
      // Compile a list of business ids for the user with key $user_key.
      $bids = [];
      for ($business_count = 2 - $user_key; $business_count > 0; $business_count--) {
        $bids[] = $this->businesses[$business_key++]->id();
      }
      // Check that the right businesses are returned for each user.
      $this->assertBusinessGetBusinessesFromUser($this->users[$user_key], $bids);

      // Check that the businesses owned by the current user are returned by
      // default.
      $this->container->get('current_user')->setAccount($this->users[$user_key]);
      $this->assertBusinessGetBusinessesFromUser(NULL, $bids);
    }
  }

  /**
   * Tests checking if a business is owned by a user.
   *
   * @covers ::businessIsOwnedByUser
   */
  public function testBusinessIsOwnedByUser() {
    // Create a mapping of which user owns which businesses.
    $ownership = [
      0 => [0, 1],
      1 => [2],
      2 => [],
    ];
    foreach ($this->users as $user_key => $user) {
      foreach ($this->businesses as $business_key => $business) {
        $this->assertEquals(in_array($business_key, $ownership[$user_key]), $this->businessManager->businessIsOwnedByUser($business, $user));
      }
    }
  }

  /**
   * Checks the output of business_get_businesses_from_user().
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account to pass to the function.
   * @param array $bids
   *   An array of business ids which represent the businesses that are expected
   *   to be returned.
   */
  protected function assertBusinessGetBusinessesFromUser(AccountInterface $account = NULL, $bids = []) {
    $businesses = $this->businessManager->getBusinessesByUser($account);
    $this->assertEquals(count($bids), count($businesses), 'The user has been linked to correct number of businesses.');
    $keys = array_keys($businesses);
    $this->assertEquals($bids, $keys, 'The correct array keys are used.');

    foreach ($businesses as $business) {
      $this->assertTrue($business instanceof Business, 'An array of Business entities is returned.');
      $key = array_shift($bids);
      $this->assertEquals($key, $business->id(), 'The correct Business entity is returned.');
    }
  }

}
