<?php

namespace Drupal\Tests\simpletest\Functional;

use Behat\Mink\Element\NodeElement;
use Drupal\business\Entity\Business;
use Drupal\invoices\Tests\InvoicesFunctionalTestBase;
use Drupal\business\Tests\BusinessTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\node\Entity\Node;
use libphonenumber\PhoneNumberFormat;

/**
 * Tests the business overview.
 *
 * @group business
 */
class BusinessOverviewTest extends InvoicesFunctionalTestBase {

  use BaseTestHelper;
  use BusinessTestHelper;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'invoices';

  /**
   * An array of test businesses.
   *
   * @var \Drupal\business\Entity\Business[]
   */
  protected $businesses;

  /**
   * {@inheritdoc}
   */
  protected $usersToCreate = ['administrator'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a test user and log in.
    $this->drupalLogin($this->users['administrator']);

    // Create a number of test business.
    for ($i = 0; $i < 20; $i++) {
      // Make sure the business name starts with letters to avoid random test
      // failures to due differences in sorting of special characters between
      // PHP and the database. PHP uses the system locale to determine the
      // collation, while the database can be configured with an arbitrary
      // collation.
      $name = $this->randomMachineName(4) . $this->randomString();
      $business = $this->createBusiness(['name' => $name]);
      $business->save();
      $this->businesses[$business->id()] = $business;
    }
  }

  /**
   * Tests the business overview.
   */
  public function testOverview() {
    $this->drupalGet('businesses');

    // Check that the "Add business" local action is present.
    $xpath = '//nav[@class="action-links"]/li/a[@href="/business/add" and contains(text(), :text)]';
    $this->assertXPathElements($xpath, 1, [':text' => (string) t('Add business')], 'The "Add business" local action is present.');

    // Check that the pager is not present. We added 20 businesses which is the
    // maximum number that fits on one page.
    $this->assertNoPager();

    // Check that the businesses are present in the overview in alphabetical
    // order.
    uasort($this->businesses, function (Business $a, Business $b) {
      return strcasecmp($a->get('name')->getValue()[0]['value'], $b->get('name')->getValue()[0]['value']);
    });

    // Loop over the displayed table rows and compare them with each business in
    // order.
    $tablerows = $this->xpath('//div[contains(@class, "view-businesses")]//table/tbody/tr');
    /* @var \Behat\Mink\Element\NodeElement $tablerow */
    foreach ($tablerows as $tablerow) {
      $business = array_shift($this->businesses);

      $testcases = [
        [
          'message' => 'The first column contains the business name.',
          'expected' => $business->name->getValue()[0]['value'],
          'actual' => $tablerow->find('css', 'td:nth-child(1)>a')->getText(),
        ],
        [
          'message' => 'The first column is linked to the business detail page.',
          'expected' => '/business/' . $business->id(),
          'actual' => $tablerow->find('css', 'td:nth-child(1)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The second column contains the email address.',
          'expected' => $business->field_business_email->getValue()[0]['value'],
          'actual' => $tablerow->find('css', 'td:nth-child(2)>a')->getText(),
        ],
        [
          'message' => 'The second column is linked to the email address.',
          'expected' => 'mailto:' . $business->field_business_email->getValue()[0]['value'],
          'actual' => $tablerow->find('css', 'td:nth-child(2)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The third column contains the phone number.',
          'expected' => $business->get('field_business_phone')->first()->getFormattedNumber(PhoneNumberFormat::INTERNATIONAL),
          'actual' => $tablerow->find('css', 'td:nth-child(3)>a')->getText(),
        ],
        [
          'message' => 'The third column is linked to the phone number.',
          'expected' => 'tel:' . $business->get('field_business_phone')->first()->getFormattedNumber(),
          'actual' => $tablerow->find('css', 'td:nth-child(3)>a')->getAttribute('href'),
        ],
        [
          'message' => 'The fourth column contains the "edit" action link.',
          'expected' => t('edit'),
          'actual' => $tablerow->find('css', 'td:nth-child(4)>a')->getText(),
        ],
        [
          'message' => 'The fourth column is linked to the business edit page.',
          'expected' => '/business/' . $business->id() . '/edit?destination=/businesses',
          'actual' => $tablerow->find('css', 'td:nth-child(4)>a')->getAttribute('href'),
        ],
      ];

      foreach ($testcases as $testcase) {
        $this->assertEquals(trim($testcase['expected']), trim($testcase['actual']), $testcase['message']);
      }
    }

    // Check that all businesses were displayed.
    $this->assertFalse($this->businesses, 'All businesses are shown in the table.');

    // Create a business without a landline number, using only a mobile number.
    // In this case the mobile number should be shown in the overview.
    $phonenumber = '0486123456';
    $values = [
      // Ensure the business appears at the top of the overview.
      'name' => 'aaaaaaaaaaaaaaaa',
      'field_business_mobile' => ['raw_input' => $phonenumber],
      'field_business_phone' => NULL,
    ];
    $this->createBusiness($values)->save();
    $this->drupalGet('businesses');
    $tablerow = $this->xpath('//div[contains(@class, "view-businesses")]//table/tbody/tr[1]/td[3]')[0];

    // Check that the mobile number is shown.
    $this->assertEquals($this->formatPhoneNumber($phonenumber, PhoneNumberFormat::INTERNATIONAL), $tablerow->find('css', 'a')->getText());

    // Check that the number is formatted as a link to the mobile number.
    $this->assertEquals('tel:' . $this->formatPhoneNumber($phonenumber), $tablerow->find('css', 'a')->getAttribute('href'));

    // We now have 21 businesses. A pager should now appear.
    $this->assertPager();
  }

}
