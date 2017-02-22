<?php

declare (strict_types = 1);

namespace Drupal\Tests\simpletest\Functional;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Url;
use Drupal\business\Tests\BusinessTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\invoices\Tests\InvoicesFunctionalTestBase;
use Drupal\user\Entity\User;

/**
 * Tests the managing of businesses through the user interface.
 *
 * @group business
 */
class BusinessUiTest extends InvoicesFunctionalTestBase {

  use BaseTestHelper;
  use BusinessTestHelper {
    randomBusinessValues as traitRandomBusinessValues;
  }

  /**
   * {@inheritdoc}
   */
  protected $usersToCreate = ['administrator'];

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->connection = $this->container->get('database');

    // Create a test user and log in.
    $this->drupalLogin($this->users['administrator']);
  }

  /**
   * Tests the business form.
   */
  public function testBusinessForm() {
    // Check that the database table exists and is empty.
    $this->assertTrue($this->connection->schema()->tableExists('business'), 'The business database table exists.');
    $this->assertBusinessTableEmpty('The business database is initially empty.');

    // Check that error messages are displayed about required fields when
    // creating a new business.
    $this->drupalPostForm('business/add', [], t('Save'));
    $required_fields = ['name[0][value]' => (string) t('Business name')];
    $this->assertRequiredFieldMessages($required_fields);

    // Check form validation errors.
    $invalid_values = [
      'field_business_email[0][value]' => $this->randomString(),
    ];
    $messages = [
      'error' => [
        (string) t('@name field is required.', ['@name' => t('Business name')]),
        (string) t('The email address %mail is not valid.', ['%mail' => $invalid_values['field_business_email[0][value]']]),
      ],
    ];
    $this->drupalPostForm('business/add', $invalid_values, t('Save'));
    $this->assertFieldValidationFailed(array_keys($invalid_values));
    $this->assertStatusMessages($messages);

    // Fill in all the fields and check if they are saved correctly.
    $values = $this->randomBusinessValues();

    // @todo Also test countries other than Belgium.
    $values['field_business_address']['country_code'] = 'BE';
    $business = $this->createUiBusiness($values);
    $messages = ['status' => [(string) t('Created new business %name.', ['%name' => $values['name']])]];
    $this->assertStatusMessages($messages);
    $this->assertBusinessTableNotEmpty('The business database table is no longer empty after creating a business.');
    // Check that the values have been correctly saved, ignoring the created
    // and changed date, since these cannot be set through the UI.
    unset($values['created']);
    unset($values['changed']);
    $this->assertBusinessProperties($business, $values);

    // Check that the form fields have correct values when the business is
    // edited.
    $this->drupalGet('business/' . $business->id() . '/edit');
    $form_values = $this->convertBusinessValuesToFormPostValues($values);
    foreach ($form_values as $name => $value) {
      $this->assertSession()->fieldValueEquals($name, $value);
    }

    // Change the values and check that the entity is correctly updated.
    $new_values = $this->randomBusinessValues();

    // @todo Also test countries other than Belgium.
    $new_values['field_business_address']['country_code'] = 'BE';

    // The created and changed dates cannot be changed through the UI.
    unset($new_values['created']);
    unset($new_values['changed']);

    $this->drupalPostForm('business/' . $business->id() . '/edit', $this->convertBusinessValuesToFormPostValues($new_values), t('Save'));
    $business = $this->loadUnchangedBusiness($business->id());
    // Check that a message is shown informing the user that the business has
    // been edited.
    $this->assertSession()->responseContains(t('The changes have been saved.', ['%name' => $values['name']]));
    $this->assertBusinessProperties($business, $new_values);

    // Check that the user is redirected to the confirmation page when clicking
    // the 'Delete' button on the business edit page.
    $this->drupalGet('business/' . $business->id() . '/edit');
    $this->clickLink(t('Delete'));
    $this->assertSession()->addressEquals('business/' . $business->id() . '/delete');
    // Check that the confirmation message and disclaimer are shown.
    $this->assertSession()->responseContains(t('Are you sure you want to delete the business %name?', ['%name' => $business->getName()]));
    $this->assertSession()->responseContains(t('This action cannot be undone.'));

    // Check that the business can be deleted.
    $this->drupalPostForm('business/' . $business->id() . '/delete', [], t('Delete'));
    $this->assertSession()->responseContains(t('Business %name has been deleted.', ['%name' => $business->getName()]));
    $this->assertBusinessTableEmpty('The business database is empty after the business has been deleted.');
  }

  /**
   * Tests the business field in the user edit form.
   */
  public function testBusinessesFieldForUser() {
    // Create two businesses.
    $business1 = $this->createUiBusiness();
    $business2 = $this->createUiBusiness();

    // Check that the field to add businesses is shown in the user edit form for
    // administrators.
    $this->drupalGet('user/' . $this->users['administrator']->id() . '/edit');
    $this->assertSession()->fieldExists('edit-field-user-businesses-0-target-id');

    // Add the businesses to the user.
    $this->drupalPostForm(NULL, ['field_user_businesses[0][target_id]' => $business1->getName() . ' (' . $business1->id() . ')'], t('Add another item'));
    $this->assertSession()->fieldExists('edit-field-user-businesses-1-target-id');
    $this->drupalPostForm(NULL, ['field_user_businesses[1][target_id]' => $business2->getName() . ' (' . $business2->id() . ')'], t('Add another item'));
    $this->assertSession()->fieldExists('edit-field-user-businesses-2-target-id');
    $this->drupalPostForm(NULL, [], t('Save'));
    $this->assertSession()->responseContains('The changes have been saved.');

    // Check that the businesses are linked to the user.
    $user = User::load($this->users['administrator']->id());
    $this->assertEquals($business1->id(), $user->field_user_businesses->get(0)->getValue()['target_id'], 'The first business has been linked to the user.');
    $this->assertEquals($business2->id(), $user->field_user_businesses->get(1)->getValue()['target_id'], 'The second business has been linked to the user.');

    // Check that a validation error is displayed if a business is added twice
    // to the same user.
    $this->drupalPostForm(NULL, ['field_user_businesses[2][target_id]' => $business1->getName() . ' (' . $business1->id() . ')'], t('Add another item'));
    $this->drupalPostForm(NULL, [], t('Save'));

    // Check that both fields containing the duplicated value are marked as
    // invalid.
    $this->assertFieldValidationFailed([
      'field_user_businesses[0][target_id]',
      'field_user_businesses[2][target_id]',
    ]);
    // Check that a warning message is shown to the user.
    $this->assertStatusMessages([
      'error' => [
        (string) new FormattableMarkup('The value %value has been entered multiple times.', ['%value' => Tags::encode($business1->getName() . ' (' . $business1->id() . ')')]),
      ],
    ]);
  }

  /**
   * Tests the rendering of the business entity.
   */
  public function testBusinessViewEntity() {
    $business = $this->createUiBusiness();
    $this->drupalGet('business/' . $business->id());

    // Check that the entity is rendered.
    $this->assertXPathElements($this->getBusinessEntityXpath(), 1, [], 'The business entity is rendered.');

    // Check that the page title is set to the business name.
    // @todo: Change "Drupal" to the project name.
    // @see http://atrium.pocomas.be/invoicing/node/1169
    $this->assertSession()->titleEquals($business->getName() . ' | Drupal');

    $xpath = '//h1[contains(concat(" ", @class, " "), "page-title")]/div[contains(text(), :name)]';
    $this->assertXPathElements($xpath, 1, [':name' => $business->getName()], 'The page title contains the business name.');

    // Check that all fields are rendered.
    $xpath = '//div[contains(@class, "field--name-field-business-address")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The address field is rendered.');
    $xpath = '//div[contains(@class, "field--name-field-business-email")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The email field is rendered.');
    $xpath = '//div[contains(@class, "field--name-field-business-phone")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The phone field is rendered.');
    $xpath = '//div[contains(@class, "field--name-field-business-vat")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The vat field is rendered.');
    $xpath = '//div[contains(@class, "field--name-field-business-iban")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The iban field is rendered.');
    $xpath = '//div[contains(@class, "field--name-field-business-bic")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The bic field is rendered.');
    $xpath = '//div[contains(@class, "field--name-field-business-mobile")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The mobile number field is rendered.');

    // Check if the tabs are rendered.
    $xpath = '//ul[contains(@class, "tabs")]//a[@href=:href]';
    $url = Url::fromUri('base:business/' . $business->id())->toString();
    $this->assertXPathElements($xpath, 1, [':href' => $url], 'The business view tab is rendered.');
    $this->assertXPathElements($xpath, 1, [':href' => $url . '/edit'], 'The business edit tab is rendered.');
    $this->assertXPathElements($xpath, 1, [':href' => $url . '/delete'], 'The business delete tab is rendered.');
  }

  /**
   * Constructs an XPath query to find an element on the business entity page.
   *
   * @param string $xpath
   *   The path selector to search for.
   *
   * @return string
   *   The XPath query.
   */
  protected function getBusinessEntityXpath(string $xpath = '') : string {
    return '//div[contains(concat(" ", @class, " "), " business ")]' . $xpath;
  }

}
