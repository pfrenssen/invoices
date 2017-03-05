<?php

declare (strict_types = 1);

namespace Drupal\Tests\simpletest\Functional;

use Drupal\client\Entity\ClientInterface;
use Drupal\client\Tests\ClientTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\invoices\Tests\InvoicesFunctionalTestBase;
use libphonenumber\PhoneNumberFormat;

/**
 * Tests the client overview.
 *
 * @group client
 */
class ClientOverviewTest extends InvoicesFunctionalTestBase {

  use BaseTestHelper;
  use ClientTestHelper;

  /**
   * {@inheritdoc}
   */
  protected $usersToCreate = [
    'administrator',
    'business_owner',
  ];

  /**
   * An array of test clients.
   *
   * @var \Drupal\client\Entity\Client[]
   */
  protected $clients;

  /**
   * A business owned by the second business owner.
   *
   * @var \Drupal\business\Entity\Business
   */
  protected $business2;

  /**
   * A client owned by the second business owner.
   *
   * @var \Drupal\client\Entity\Client
   */
  protected $client2;

  /**
   * Tests the client overview.
   */
  public function testOverview() {
    // Create a second business owner with a business and client to test
    // negative cases.
    $this->users['business_owner2'] = $this->drupalCreateUserWithRole('business_owner');

    $this->business2 = $this->createBusiness();
    $this->business2->save();
    $this->addBusinessToUser($this->business2, $this->users['business_owner2']);

    $this->drupalLogin($this->users['business_owner2']);
    $this->client2 = $this->createUiClient();

    // Log in the test user and create a number of test clients.
    $this->drupalLogin($this->users['business_owner']);
    for ($i = 0; $i < 20; $i++) {
      // Make sure the client name starts with letters to avoid random test
      // failures to due differences in sorting of special characters between
      // PHP and the database. PHP uses the system locale to determine the
      // collation, while the database can be configured with an arbitrary
      // collation.
      $name = $this->randomMachineName(4) . $this->randomString();
      $client = $this->createUiClient(['name' => $name]);
      $this->clients[$client->id()] = $client;
    }

    // After creating a client we should be on the client overview, and we
    // should have access.
    $this->assertSession()->addressEquals('clients');
    $this->assertSession()->statusCodeEquals(200);

    // Check that the client of the other business owner is not visible.
    $this->assertSession()->pageTextNotContains(trim($this->client2->getName()));

    // Check that the "Add client" local action is present.
    $xpath = '//ul[@class="action-links"]/li/a[@href="/client/add" and contains(text(), :text)]';
    $this->assertXPathElements($xpath, 1, [':text' => (string) t('Add client')], 'The "Add client" local action is present.');

    // Check that the pager is not present. We added 20 clients which is the
    // maximum number that fits on one page.
    $this->assertNoPager();

    // Check that the clients are present in the overview in alphabetical order.
    uasort($this->clients, function (ClientInterface $a, ClientInterface $b) {
      return strcasecmp($a->getName(), $b->getName());
    });

    // Loop over the displayed table rows and compare them with each client in
    // order.
    $tablerows = $this->xpath('//div[contains(@class, "view-clients")]//table/tbody/tr');
    /* @var \Behat\Mink\Element\NodeElement $tablerow */
    foreach ($tablerows as $tablerow) {
      $client = array_shift($this->clients);

      $testcases = [
        [
          'message' => 'The first column contains the client name.',
          'expected' => $client->getName(),
          'actual' => $tablerow->find('css', 'td:nth-child(1)>a')->getText(),
        ],
        [
          'message' => 'The first column is linked to the client detail page.',
          'expected' => '/client/' . $client->id(),
          'actual' => $tablerow->find('css', 'td:nth-child(1)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The second column contains the email address.',
          'expected' => $client->getEmail(),
          'actual' => $tablerow->find('css', 'td:nth-child(2)>a')->getText(),
        ],
        [
          'message' => 'The second column is linked to the email address.',
          'expected' => 'mailto:' . $client->field_client_email->value(),
          'actual' => $tablerow->find('css', 'td:nth-child(2)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The third column contains the phone number.',
          'expected' => $client->getPhoneNumber()->getFormattedNumber(PhoneNumberFormat::INTERNATIONAL),
          'actual' => $tablerow->find('css', 'td:nth-child(3)>div>span')->getText(),
        ],
        [
          'message' => 'The third column is linked to the phone number.',
          'expected' => 'tel:' . $client->getPhoneNumber()->getFormattedNumber(),
          'actual' => $tablerow->find('css', 'td:nth-child(3)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The fourth column contains the website.',
          'expected' => $client->getWebsite(),
          'actual' => $tablerow->find('css', 'td:nth-child(4)>a')->getText(),
        ],
        [
          'message' => 'The fourth column is linked to the website.',
          'expected' => $client->getWebsite(),
          'actual' => $tablerow->find('css', 'td:nth-child(4)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The fifth column contains the "edit" action link.',
          'expected' => (string) t('edit'),
          'actual' => $tablerow->find('css', 'td:nth-child(5)>a')->getText(),
        ],
        [
          'message' => 'The fifth column is linked to the client edit page.',
          'expected' => '/client/' . $client->id() . '/edit',
          'actual' => $tablerow->find('css', 'td:nth-child(5)>a')->getAttribute('href'),
        ],
      ];

      foreach ($testcases as $testcase) {
        $this->assertEqual(trim($testcase['expected']), trim($testcase['actual']), $testcase['message']);
      }
    }

    // Check that all clients were displayed.
    $this->assertFalse($this->clients, 'All clients are shown in the table.');

    // Add one more client and assert that a pager now appears.
    $this->createUiClient();
    $this->drupalGet('clients');
    $this->assertPager();

    // Check that adding "/all" to the URL does not reveal the clients of the
    // other business owner.
    $this->drupalGet('clients/all');
    $this->assertSession()->pageTextNotContains(trim($this->client2->getName()));

    // @todo Also check that the bid cannot be added to the URL.
  }

  /**
   * Tests the client overview for administrators.
   */
  public function testClientOverview() {
    // Log in as administrator.
    $this->drupalLogin($this->users['administrator']);

    // Create 2 business owners, each owning a business with 2 clients.
    for ($i = 0; $i < 2; $i++) {
      $user = $this->drupalCreateUserWithRole('business_owner');
      $business = $this->createBusiness();
      $business->save();
      $this->addBusinessToUser($business, $user);
      $this->users[] = $user;
      for ($j = 0; $j < 2; $j++) {
        // Make sure the client name starts with letters to avoid random test
        // failures to due differences in sorting of special characters between
        // PHP and the database. PHP uses the system locale to determine the
        // collation, while the database can be configured with an arbitrary
        // collation.
        $name = $this->randomMachineName(4) . $this->randomString();
        $client = $this->createClient([
          'business' => $business->id(),
          'name' => $name,
        ]);
        $client->save();
        $this->clients[$client->id()] = $client;
      }
    }

    // Go to the client overview.
    $this->drupalGet('clients');

    // Check that the "Add client" local action is present.
    $xpath = '//nav[@class="action-links"]/li/a[@href="/client/add" and contains(text(), :text)]';
    $this->assertXPathElements($xpath, 1, [':text' => (string) t('Add client')], 'The "Add client" local action is present.');

    // Check that the clients are present in the overview in alphabetical order.
    uasort($this->clients, function (ClientInterface $a, ClientInterface $b) {
      return strcasecmp($a->getName(), $b->getName());
    });

    // Loop over the displayed table rows and compare them with each client in
    // order.
    $tablerows = $this->xpath('//div[contains(@class, "view-clients")]//table/tbody/tr');
    foreach ($tablerows as $tablerow) {
      $client = array_shift($this->clients);
      $business = $client->getBusiness();

      $testcases = [
        [
          'message' => 'The first column contains the business name.',
          'expected' => $business->getName(),
          'actual' => $tablerow->find('css', 'td:nth-child(1)>a')->getText(),
        ],
        [
          'message' => 'The first column is linked to the business.',
          'expected' => '/business/' . $business->id(),
          'actual' => $tablerow->find('css', 'td:nth-child(1)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The second column contains the client name.',
          'expected' => $client->getName(),
          'actual' => $tablerow->find('css', 'td:nth-child(2)>a')->getText(),
        ],
        [
          'message' => 'The second column is linked to the client detail page.',
          'expected' => '/client/' . $client->id(),
          'actual' => $tablerow->find('css', 'td:nth-child(2)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The third column contains the email address.',
          'expected' => $client->getEmail(),
          'actual' => $tablerow->find('css', 'td:nth-child(3)>a')->getText(),
        ],
        [
          'message' => 'The third column is linked to the email address.',
          'expected' => 'mailto:' . $client->getEmail(),
          'actual' => $tablerow->find('css', 'td:nth-child(3)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The fourth column contains the phone number.',
          'expected' => $client->getPhoneNumber()->getFormattedNumber(PhoneNumberFormat::INTERNATIONAL),
          'actual' => $tablerow->find('css', 'td:nth-child(4)>a')->getText(),
        ],
        [
          'message' => 'The fourth column is linked to the phone number.',
          'expected' => 'tel:' . $client->getPhoneNumber()->getFormattedNumber(),
          'actual' => $tablerow->find('css', 'td:nth-child(4)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The fifth column contains the website.',
          'expected' => $client->getWebsite(),
          'actual' => $tablerow->find('css', 'td:nth-child(5)>a')->getText(),
        ],
        [
          'message' => 'The fifth column is linked to the website.',
          'expected' => $client->getWebsite(),
          'actual' => $tablerow->find('css', 'td:nth-child(5)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The sixth column contains the "edit" action link.',
          'expected' => (string) t('edit'),
          'actual' => $tablerow->find('css', 'td:nth-child(6)>a')->getText(),
        ],
        [
          'message' => 'The sixth column is linked to the client edit page.',
          'expected' => '/client/' . $client->id() . '/edit',
          'actual' => $tablerow->find('css', 'td:nth-child(6)>a')->getAttribute('href'),
        ],
      ];

      foreach ($testcases as $testcase) {
        $this->assertEquals(trim($testcase['expected']), trim($testcase['actual']), $testcase['message']);
      }
    }

    // Check that all clients were displayed.
    $this->assertFalse($this->clients, 'All clients are shown in the table.');
  }

}
