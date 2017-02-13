<?php

declare (strict_types = 1);

namespace Drupal\Tests\client\Kernel;

use Drupal\business\BusinessManager;
use Drupal\business\Tests\BusinessTestHelper;
use Drupal\client\Tests\ClientTestHelper;
use Drupal\invoice\Tests\InvoiceTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\line_item\Tests\LineItemTestHelper;

/**
 * Unit tests for the Client module.
 *
 * @group client
 */
class ClientManagerTest extends EntityKernelTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;
  use ClientTestHelper;
  use InvoiceTestHelper;
  use LineItemTestHelper;

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
   * Test business entities.
   *
   * @var \Drupal\business\Entity\BusinessInterface[]
   *   An array of Business entities.
   */
  protected $businesses;

  /**
   * Test client entities.
   *
   * @var \Drupal\client\Entity\ClientInterface[]
   *   An array of Client entities.
   */
  protected $clients;

  /**
   * Test invoice entities.
   *
   * @var InvoiceInterface[]
   *   An array of Invoice entities.
   */
  protected $invoices;

  /**
   * Test user accounts.
   *
   * @var \Drupal\Core\Session\AccountInterface[]
   *   An array of user objects.
   */
  protected $users;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setup();

    $this->installEntitySchema('business');
    $this->installEntitySchema('client');
    $this->installConfig(['business', 'client']);

    // Create two test users, each owning one business with two clients.
    $this->businesses = [];
    $this->clients = [];
    $this->invoices = [];
    $this->users = [];

    for ($i = 0; $i < 2; $i++) {
      // Create a business.
      $this->businesses[$i] = $this->createBusiness();
      $this->businesses[$i]->save();

      // Create a user and link the business to it.
      $user = $this->createUser();
      $this->addBusinessToUser($this->businesses[$i], $user);
      $this->users[$i] = $user;

      // Create two clients for the business.
      for ($j = 0; $j < 2; $j++) {
        $client = $this->createClient(['business' => $this->businesses[$i]->id()]);
        $client->save();
        $this->clients[] = $client;
      }
    }

    // Create a line item of each type so it can be referenced in invoices.
    $this->createLineItem('product')->save();
    $this->createLineItem('service')->save();

    // Create three invoices for the first client, two invoices for the second
    // client and a single invoice for the third client. The fourth doesn't get
    // any invoices.
    for ($i = 0; $i < 6; $i++) {
      $invoice = $this->createInvoice([
        'business' => $this->clients[$i % 4 % 3]->getBusiness(),
        'field_invoice_client' => $this->clients[$i % 4 % 3],
      ]);
      $invoice->save();
      $this->invoices[] = $invoice;
    }
  }

  /**
   * Executes the unit tests.
   *
   * It is faster to run all unit tests in a single test run.
   */
  public function testRunner() {
    $this->doTestClientIsOwnedByUser();
    $this->doTestClientHasInvoices();
    $this->doTestClientGetInvoiceIds();
  }

  /**
   * Tests client_is_owned_by_user().
   */
  public function doTestClientIsOwnedByUser() {
    // Define a list of which clients are owned by which users. The first two
    // clients belong to the first user, the last two to the second.
    $ownership = [
      0 => [0, 1],
      1 => [2, 3],
    ];

    // Test if client_is_owned_by_user() matches the expected ownership.
    foreach ($ownership as $user_key => $client_keys) {
      for ($i = 0; $i < 4; $i++) {
        $owned = in_array($i, $client_keys);
        $this->assertEqual($owned, client_is_owned_by_user($this->clients[$i], $this->users[$user_key]), format_string('Client :client :owned by user :user.', [
          ':client' => $i,
          ':owned' => $owned ? 'is owned' : 'is not owned',
          ':user' => $user_key,
        ]));
      }
    }
  }

  /**
   * Tests client_has_invoices().
   */
  public function doTestClientHasInvoices() {
    // A list of which clients have invoices. Only the last client doesn't have
    // any invoices.
    $ownership = [
      0 => TRUE,
      1 => TRUE,
      2 => TRUE,
      3 => FALSE,
    ];

    // Tests if client_has_invoices() returns the expected results.
    foreach ($ownership as $client_key => $can_has_invoice) {
      $this->assertEqual(client_has_invoices($this->clients[$client_key]), $can_has_invoice, format_string('Client :client :has invoice(s).', [
        ':client' => $client_key,
        ':has' => $can_has_invoice ? 'has' : 'does not have',
      ]));
    }
  }

  /**
   * Tests client_get_invoice_ids().
   */
  public function doTestClientGetInvoiceIds() {
    // Define which clients have which invoices. The first client has 3
    // invoices, the second has 2, the third has 1, the fourth has none.
    $ownership = [
      0 => [0, 3, 4],
      1 => [1, 5],
      2 => [2],
      3 => [],
    ];

    // Test if client_get_invoice_ids() matches the expected ownership.
    foreach ($ownership as $client_key => $invoice_keys) {
      // Get the invoice ids from our stored invoices.
      $expected_ids = [];
      foreach ($invoice_keys as $key) {
        $expected_ids[] = $this->invoices[$key]->identifier();
      }

      // Compare with the ids that are returned by client_get_invoice_ids().
      $actual_ids = client_get_invoice_ids($this->clients[$client_key]);
      $this->assertEqual($expected_ids, $actual_ids, format_string('Client :client has the expected invoices.', [':client' => $client_key]));
    }
  }

}
