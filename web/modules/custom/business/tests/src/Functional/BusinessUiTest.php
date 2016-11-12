<?php

declare (strict_types = 1);

namespace Drupal\Tests\simpletest\Functional;

use Drupal\business\Tests\BusinessTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\invoices\Tests\InvoicesFunctionalTestBase;

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
  public function _testBusinessForm() {
    // Check that the database table exists and is empty.
    $this->assertTrue($this->connection->schema()->tableExists('business'), 'The business database table exists.');
    $this->assertBusinessTableEmpty('The business database is initially empty.');

    // Check that error messages are displayed about required fields when
    // creating a new business.
    $this->drupalPostForm('business/add', [], t('Save'));
    $required_fields = ['name[0][value]' => t('Business name')];
    $this->assertRequiredFieldMessages($required_fields);

    // @todo test fails here because the address field is required. Fix this in
    //   the Address module.

    // Check form validation errors.
    $invalid_values = [
      'field_business_email[0][email]' => $this->randomString(),
    ];
    $messages = [
      'error' => [
        t('!name field is required.', ['!name' => t('Business name')]),
        t('"%mail" is not a valid email address', ['%mail' => $invalid_values['field_business_email[0][email]']]),
      ],
    ];
    $this->drupalPostForm('business/add', $invalid_values, t('Save'));
    $this->assertFieldValidationFailed(array_keys($invalid_values));
    $this->assertStatusMessages($messages);

    // Fill in all the fields and check if they are saved correctly.
    $values = $this->randomBusinessValues();
    $business = $this->createUiBusiness($values);
    $messages = ['status' => [t('New business %name has been added.', ['%name' => $values['name']])]];
    $this->assertStatusMessages($messages);
    $this->assertBusinessTableNotEmpty('The business database table is no longer empty after creating a business.');
    $this->assertBusinessProperties($business, $values, 'The business has been correctly saved to the database.');

    // Check that the form fields have correct values when the business is
    // edited.
    $this->drupalGet('business/' . $business->id() . '/edit');
    $form_values = $this->convertBusinessValuesToFormPostValues($values);
    foreach ($form_values as $name => $value) {
      $this->assertFieldByName($name, $value, format_string('When editing an existing business the %name field has the right value.', ['%name' => $name]));
    }

    // Change the values and check that the entity is correctly updated.
    $new_values = $this->randomBusinessValues();
    $this->drupalPostForm('business/' . $business->id() . '/edit', $this->convertBusinessValuesToFormPostValues($new_values), t('Save'));
    $business = business_load($business->id(), TRUE);
    $this->assertRaw(t('The changes have been saved.', ['%name' => $values['name']]), 'A message is shown informing the user that the business has been edited.');
    $this->assertBusinessProperties($business, $new_values, 'The updated business has been correctly saved to the database.');

    // Check that the user is redirected to the confirmation page when clicking
    // the 'Delete' button on the business edit page.
    $this->drupalPostForm('business/' . $business->id() . '/edit', [], t('Delete'));
    $this->assertUrl('business/' . $business->id() . '/delete', [], 'The user is redirected to the confirmation form when clicking the "Delete" button in the business edit form.');
    $this->assertRaw(t('Are you sure you want to delete %name?', ['%name' => $business->getName()]), 'The confirmation message is shown when deleting a user.');
    $this->assertRaw(t('This action cannot be undone.'), 'The disclaimer is shown when deleting a user.');

    // Check that the business can be deleted.
    $this->drupalPostForm('business/' . $business->id() . '/delete', [], t('Delete'));
    $this->assertRaw(t('Business %name has been deleted.', ['%name' => $business->getName()]), 'A message is shown informing the user that the business has been deleted.');
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
    $this->assertFieldById('edit-field-user-businesses-und-1-target-id', '', 'The field_user_businesses for a second business is shown on the page.');
    $this->drupalPostForm(NULL, ['field_user_businesses[1][target_id]' => $business2->getName() . ' (' . $business2->id() . ')'], t('Add another item'));
    $this->assertFieldById('edit-field-user-businesses-und-1-target-id', '', 'The field_user_businesses for a third business is shown on the page.');
    $this->drupalPostForm(NULL, [], t('Save'));
    $this->assertRaw('The changes have been saved.', 'The user was saved successfully.');

    // Check that the businesses are linked to the user.
    $user = user_load($this->users['administrator']->id());
    $this->assertEqual($user->field_user_businesses[0]['target_id'], $business1->id(), 'The first business has been linked to the user.');
    $this->assertEqual($user->field_user_businesses[1]['target_id'], $business2->id(), 'The second business has been linked to the user.');

    // Check that a validation error is displayed if a business is added twice
    // to the same user.
    $this->drupalPostForm(NULL, ['field_user_businesses[2][target_id]' => $business1->getName() . ' (' . $business1->id() . ')'], t('Add another item'));
    $this->drupalPostForm(NULL, [], t('Save'));
    // @todo Replace this with $this->assertFieldValidationFailed() and
    //   $this->assertStatusMessages() when we are using traits.
    // @see http://atrium.pocomas.be/invoicing/node/1161
    $this->assertRaw('field can contain only unique values');
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
    $this->assertTitle($business->getName() . ' | Drupal');

    $xpath = '//h1[@id = "page-title" and contains(text(), :name)]';
    $this->assertXPathElements($xpath, 1, [':name' => $business->getName()], 'The page title contains the business name.');

    // Check that all fields are rendered.
    $xpath = '//div[contains(@class, "field-name-field-business-address")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The address field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-business-email")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The email field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-business-phone")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The phone field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-business-vat")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The vat field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-business-iban")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The iban field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-business-bic")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The bic field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-business-mobile")]';
    $this->assertXPathElements($this->getBusinessEntityXpath($xpath), 1, [], 'The mobile number field is rendered.');

    // Check if the tabs are rendered.
    $xpath = '//ul[contains(@class, "tabs")]//a[@href=:href]';
    $url = url('business/' . $business->id());
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
