<?php

declare (strict_types = 1);

/**
 * Tests the tax rate overview.
 */
class TaxRatesOverviewTest extends InvoicingIntegrationTestCase {

  use \Drupal\invoicing\Traits\BaseTestHelper;
  use \Drupal\invoicing\Traits\LineItemTestHelper;

  /**
   * Test businesses.
   *
   * @var Business[]
   */
  protected $businesses;

  /**
   * An array of test tax rates.
   *
   * @var array
   */
  protected $taxRates;

  /**
   * {@inheritdoc}
   */
  protected $usersToCreate = [
    'business owner',
  ];

  /**
   * Returns test case metadata.
   */
  public static function getInfo() {
    return [
      'name' => 'Tax rates overview test',
      'description' => 'Tests the tax rates overview.',
      'group' => 'Invoicing - Line item',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a business to reference.
    $this->business = $this->createBusiness();
    $this->business->save();
  }

  /**
   * Tests the tax rate overview.
   */
  public function testOverview() {
    // Log in as business owner.
    $this->drupalLogin($this->users['business owner']);

    // Create a number of test tax rates.
    for ($i = 0; $i < 20; $i++) {
      $tax_rate = $this->createUiTaxRate();
      $this->taxRates[$tax_rate->tid] = $tax_rate;
    }

    // Go to the tax rates overview.
    $this->drupalGet('settings/tax-rates');

    // Check that the pager is not present. We added 20 tax rates which is the
    // maximum number that fits on one page.
    $this->assertNoPager();

    // Loop over the displayed table rows and compare them with each tax rate in
    // order.
    $tablerows = $this->xpath('//div[contains(@class, "view-tax-rate")]//table//tbody/tr');
    foreach ($tablerows as $tablerow) {
      /* @var $tablerow SimpleXMLElement */
      $tax_rate = array_shift($this->taxRates);
      $testcases = [
        [
          'message' => 'The first column contains the tax rate name.',
          'expected' => $tax_rate->name,
          'actual' => (string) $tablerow->td[0],
        ],
        [
          'message' => 'The second column contains the rate.',
          'expected' => $tax_rate->rate,
          'actual' => (string) $tablerow->td[1],
        ],
        [
          'message' => 'The third column contains the edit link.',
          'expected' => 'edit',
          'actual' => (string) $tablerow->td[2]->ul->li[0]->a[0],
        ],
        [
          'message' => 'The third column links to the edit link.',
          'expected' => url('settings/tax-rates/' . $tax_rate->tid . '/edit'),
          'actual' => (string) $tablerow->td[2]->ul->li[0]->a[0]['href'],
        ],
        [
          'message' => 'The third column contains the delete link.',
          'expected' => 'delete',
          'actual' => (string) $tablerow->td[2]->ul->li[1]->a[0],
        ],
        [
          'message' => 'The third column links to the delete link.',
          'expected' => url('settings/tax-rates/' . $tax_rate->tid . '/delete'),
          'actual' => (string) $tablerow->td[2]->ul->li[1]->a[0]['href'],
        ],
      ];

      foreach ($testcases as $testcase) {
        $this->assertEqual(trim($testcase['expected']), trim($testcase['actual']), $testcase['message']);
      }
    }

    // Check that all tax rates were displayed.
    $this->assertFalse($this->taxRates, 'All tax rates are shown in the table.');

    // Add one more tax rate and assert that a pager now appears.
    $this->createUITaxRate();
    $this->drupalGet('settings/tax-rates');
    $this->assertPager();

    // Create a different user with the role business owner and verify it does
    // not see the tax rates created by another business owner.
    $business_owner = $this->createUserWithRole('business owner');
    $business = $this->createBusiness();
    $business->save();
    $this->addBusinessToUser($business, $business_owner);
    $this->drupalLogin($business_owner);

    $this->drupalGet('settings/tax-rates');
    // Check that the "Add tax rate" link is present.
    $xpath = '//div[contains(@class, "view-tax-rates")]//div[@class="view-empty"]//a[@href="/settings/tax-rates/add"]';
    $this->assertXPathElements($xpath, 1, [], 'The no results message is shown.');
    $this->assertFalse($this->xpath('//div[contains(@class, "view-tax-rate")]//table'), 'The table containing tax rates is not shown when no tax rate is linked to the user.');
  }

}
