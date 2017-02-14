<?php

declare (strict_types = 1);

namespace Drupal\Tests\simpletest\Functional;

use Drupal\business\Entity\Business;
use Drupal\client\Tests\ClientTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\invoices\Tests\InvoicesFunctionalTestBase;

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
      $name = $this->randomName(4) . $this->randomString();
      $client = $this->createUiClient(['name' => $name]);
      $this->clients[$client->cid] = $client;
    }

    // Load the client overview.
    $this->drupalGet('clients');
    $this->assertResponse(200, 'The client overview is accessible.');

    // Check that the client of the other business owner is not visible.
    $this->assertNoText(trim(check_plain($this->client2->name)), 'A client from another business owner is not visible.');

    // Check that the "Add client" local action is present.
    $xpath = '//ul[@class="action-links"]/li/a[@href="/client/add" and contains(text(), :text)]';
    $this->assertXPathElements($xpath, 1, [':text' => t('Add client')], 'The "Add client" local action is present.');

    // Check that the pager is not present. We added 20 clients which is the
    // maximum number that fits on one page.
    $this->assertNoPager();

    // Check that the clients are present in the overview in alphabetical order.
    uasort($this->clients, function ($a, $b) {
      return strcasecmp($a->name, $b->name);
    });

    // Loop over the displayed table rows and compare them with each client in
    // order.
    $tablerows = $this->xpath('//div[contains(@class, "view-clients")]//table/tbody/tr');
    foreach ($tablerows as $tablerow) {
      /* @var $tablerow SimpleXMLElement */
      /* @var $client EntityDrupalWrapper */
      $client = entity_metadata_wrapper('client', array_shift($this->clients));

      $website = $client->field_client_website->value();

      // Format the phone number as an international number.
      $phone = $client->field_client_phone->value();
      $number = phone_libphonenumber_format($phone['number'], $phone['countrycode'], $phone['extension']);

      $testcases = [
        [
          'message' => 'The first column contains the client name.',
          'expected' => $client->name->value(),
          'actual' => (string) $tablerow->td[0]->a,
        ],
        [
          'message' => 'The first column is linked to the client detail page.',
          'expected' => '/client/' . $client->getIdentifier(),
          'actual' => (string) $tablerow->td[0]->a['href'],
        ],
        [
          'message' => 'The second column contains the email address.',
          'expected' => $client->field_client_email->value(),
          'actual' => (string) $tablerow->td[1]->a,
        ],
        [
          'message' => 'The second column is linked to the email address.',
          'expected' => 'mailto:' . $client->field_client_email->value(),
          'actual' => (string) $tablerow->td[1]->a['href'],
        ],
        [
          'message' => 'The third column contains the phone number.',
          'expected' => $number,
          'actual' => (string) $tablerow->td[2]->div->span,
        ],
        [
          'message' => 'The fourth column is linked to the website.',
          'expected' => $website['uri'],
          'actual' => (string) $tablerow->td[3]->a[0]['href'],
        ],
        [
          'message' => 'The fifth column contains the "edit" action link.',
          'expected' => t('edit'),
          'actual' => (string) $tablerow->td[4]->a[0],
        ],
        [
          'message' => 'The fifth column is linked to the client edit page.',
          'expected' => '/client/' . $client->getIdentifier() . '/edit',
          'actual' => (string) $tablerow->td[4]->a[0]['href'],
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
    $this->assertNoText(trim(check_plain($this->client2->name)), 'A client from another business owner is not visible when adding "/all" to the URL.');
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
      $this->businesses[] = $business;
      for ($j = 0; $j < 2; $j++) {
        // Make sure the client name starts with letters to avoid random test
        // failures to due differences in sorting of special characters between
        // PHP and the database. PHP uses the system locale to determine the
        // collation, while the database can be configured with an arbitrary
        // collation.
        $name = $this->randomName(4) . $this->randomString();
        $client = $this->createClient([
          'business' => $business->id(),
          'name' => $name,
        ]);
        $client->save();
        $this->clients[$client->cid] = $client;
      }
    }

    // Go to the client overview.
    $this->drupalGet('clients');

    // Check that the "Add client" local action is present.
    $xpath = '//ul[@class="action-links"]/li/a[@href="/client/add" and contains(text(), :text)]';
    $this->assertXPathElements($xpath, 1, [':text' => t('Add client')], 'The "Add client" local action is present.');

    // Check that the clients are present in the overview in alphabetical order.
    uasort($this->clients, function ($a, $b) {
      return strcasecmp($a->name, $b->name);
    });

    // Loop over the displayed table rows and compare them with each client in
    // order.
    $tablerows = $this->xpath('//div[contains(@class, "view-clients")]//table/tbody/tr');
    foreach ($tablerows as $tablerow) {
      /* @var $tablerow SimpleXMLElement */
      /* @var $client EntityDrupalWrapper */
      /* @var $business Business */
      $client = array_shift($this->clients);
      $business = $client->getBusiness();

      $website = $client->field_client_website->value();

      // Format the phone number as an international number.
      $phone = $client->field_client_phone->value();
      $number = phone_libphonenumber_format($phone['number'], $phone['countrycode'], $phone['extension']);

      $testcases = [
        [
          'message' => 'The first column contains the business name.',
          'expected' => $business->getName(),
          'actual' => (string) $tablerow->td[0]->a,
        ],
        [
          'message' => 'The first column is linked to the business.',
          'expected' => '/business/' . $business->id(),
          'actual' => (string) $tablerow->td[0]->a['href'],
        ],
        [
          'message' => 'The second column contains the client name.',
          'expected' => $client->name->value(),
          'actual' => (string) $tablerow->td[1]->a,
        ],
        [
          'message' => 'The second column is linked to the client detail page.',
          'expected' => '/client/' . $client->id(),
          'actual' => (string) $tablerow->td[1]->a['href'],
        ],
        [
          'message' => 'The third column contains the email address.',
          'expected' => $client->field_client_email->value(),
          'actual' => (string) $tablerow->td[2]->a,
        ],
        [
          'message' => 'The third column is linked to the email address.',
          'expected' => 'mailto:' . $client->field_client_email->value(),
          'actual' => (string) $tablerow->td[2]->a['href'],
        ],
        [
          'message' => 'The fourth column contains the phone number.',
          'expected' => $number,
          'actual' => (string) $tablerow->td[3]->div->span,
        ],
        [
          'message' => 'The fifth column is linked to the website.',
          'expected' => $website['uri'],
          'actual' => (string) $tablerow->td[4]->a[0]['href'],
        ],
        [
          'message' => 'The sixth column contains the "edit" action link.',
          'expected' => t('edit'),
          'actual' => (string) $tablerow->td[5]->a[0],
        ],
        [
          'message' => 'The sixth column is linked to the client edit page.',
          'expected' => '/client/' . $client->getIdentifier() . '/edit',
          'actual' => (string) $tablerow->td[5]->a[0]['href'],
        ],
      ];

      foreach ($testcases as $testcase) {
        $this->assertEqual(trim($testcase['expected']), trim($testcase['actual']), $testcase['message']);
      }
    }

    // Check that all clients were displayed.
    $this->assertFalse($this->clients, 'All clients are shown in the table.');
  }

}
