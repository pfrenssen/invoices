<?php

declare (strict_types = 1);

namespace Drupal\Tests\simpletest\Functional;

use Drupal\client\Tests\ClientTestHelper;
use Drupal\invoices\Tests\BaseTestHelper;
use Drupal\invoices\Tests\InvoicesFunctionalTestBase;

/**
 * Tests the managing of clients through the user interface.
 *
 * @group client
 */
class ClientUITest extends InvoicesFunctionalTestBase {

  use BaseTestHelper;
  use ClientTestHelper {
    randomClientValues as traitRandomClientValues;
  }

  /**
   * {@inheritdoc}
   */
  protected $usersToCreate = ['business_owner'];

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
    parent::setup();

    $this->connection = $this->container->get('database');

    // Log in.
    $this->drupalLogin($this->users['business_owner']);
  }

  /**
   * Tests the client form.
   */
  public function testClientForm() {
    // Check that the database table exists and is empty.
    $this->assertTrue($this->connection->schema()->tableExists('client'), 'The client database table exists.');
    $this->assertClientTableEmpty('The client database is initially empty.');

    // Check that error messages are displayed about required fields when
    // creating a new client.
    $this->drupalPostForm('client/add', [], t('Save'));
    $required_fields = [
      'name[0][value]' => t('Client name'),
      'field_client_email[0][value]' => t('E-mail address'),
    ];
    $this->assertRequiredFieldMessages($required_fields);

    // Check form validation errors.
    $invalid_values = [
      'field_client_email[0][value]' => $this->randomString(),
      'field_client_website[0][uri]' => '/node/1',
    ];
    $messages = [
      'error' => [
        (string) t('@name field is required.', ['@name' => t('Client name')]),
        (string) t('The email address %mail is not valid.', ['%mail' => $invalid_values['field_client_email[0][value]']]),
        (string) t('Please enter a full website URL such as http://example.com.'),
      ],
    ];
    $this->drupalPostForm('client/add', $invalid_values, t('Save'));
    $this->assertFieldValidationFailed(array_keys($invalid_values));
    $this->assertStatusMessages($messages);

    // Fill in all the fields and check if they are saved correctly.
    $values = $this->randomClientValues();
    $client = $this->createUiClient($values);
    $messages = ['status' => [(string) t('New client %name has been added.', ['%name' => $values['name']])]];
    $this->assertStatusMessages($messages);
    $this->assertClientTableNotEmpty('The client database table is no longer empty after creating a client.');
    $this->assertClientProperties($client, $values);
    $this->assertSession()->addressEquals('clients');

    // Check that the form fields have correct values when the client is edited.
    $this->drupalGet('client/' . $client->id() . '/edit');
    $form_values = $this->convertClientValuesToFormPostValues($values);
    foreach ($form_values as $name => $value) {
      $this->assertSession()->fieldValueEquals($name, $value);
    }

    // Change the values and check that the entity is correctly updated.
    $new_values = $this->randomClientValues();
    $this->drupalPostForm('client/' . $client->id() . '/edit', $this->convertClientValuesToFormPostValues($new_values), t('Save'));
    $client = $this->loadUnchangedEntity('client', $client->id());
    $messages = ['status' => [(string) t('The changes have been saved.', ['%name' => $values['name']])]];
    $this->assertStatusMessages($messages);
    $this->assertClientProperties($client, $new_values);
    $this->assertSession()->addressEquals('clients');

    // Check that the user is redirected to the confirmation page when clicking
    // the 'Delete' button on the client edit page.
    $this->drupalPostForm('client/' . $client->id() . '/edit', [], t('Delete'));
    $this->assertSession()->addressEquals('client/' . $client->id() . '/delete');
    $this->assertRaw(t('Are you sure you want to delete %name?', ['%name' => $client->name]), 'The confirmation message is shown when deleting a user.');
    $this->assertRaw(t('This action cannot be undone.'), 'The disclaimer is shown when deleting a user.');

    // Check that the client can be deleted.
    $this->drupalPostForm('client/' . $client->id() . '/delete', [], t('Delete'));
    $messages = ['status' => [(string) t('Client %name has been deleted.', ['%name' => $client->name])]];
    $this->assertStatusMessages($messages);
    $this->assertClientTableEmpty('The client database is empty after the client has been deleted.');
    $this->assertSession()->addressEquals('clients');

    // @todo Check that the default field for the "Revision log message" is not
    //   visible.
    $this->markTestIncomplete('Check that the Revision Log Message field is not visible.');
  }

  /**
   * Tests the rendering of the client entity.
   */
  public function testClientViewEntity() {
    $client = $this->createUiClient();
    $this->drupalGet('client/' . $client->id());

    // Check that the entity is rendered.
    $this->assertXPathElements($this->getClientEntityXpath(), 1, [], 'The client entity is rendered.');

    // Check that the page title is set to the client name.
    // @todo: Change "Drupal" to the project name.
    // @see http://atrium.pocomas.be/invoicing/node/1169
    $this->assertTitle($client->name . ' | Drupal');

    $xpath = '//h1[@id = "page-title" and contains(text(), :name)]';
    $this->assertXPathElements($xpath, 1, [':name' => $client->name], 'The page title contains the client name.');

    // Check that all fields are rendered.
    $xpath = '//div[contains(@class, "field-name-field-client-address")]';
    $this->assertXPathElements($this->getClientEntityXpath($xpath), 1, [], 'The address field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-client-shipping-address")]';
    $this->assertXPathElements($this->getClientEntityXpath($xpath), 1, [], 'The shipping address field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-client-email")]';
    $this->assertXPathElements($this->getClientEntityXpath($xpath), 1, [], 'The email field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-client-notes")]';
    $this->assertXPathElements($this->getClientEntityXpath($xpath), 1, [], 'The notes field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-client-phone")]';
    $this->assertXPathElements($this->getClientEntityXpath($xpath), 1, [], 'The phone field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-client-vat")]';
    $this->assertXPathElements($this->getClientEntityXpath($xpath), 1, [], 'The vat field is rendered.');
    $xpath = '//div[contains(@class, "field-name-field-client-website")]';
    $this->assertXPathElements($this->getClientEntityXpath($xpath), 1, [], 'The website field is rendered.');

    // Check if the tabs are rendered.
    $xpath = '//ul[contains(@class, "tabs")]//a[@href=:href]';
    $url = url('client/' . $client->id());
    $this->assertXPathElements($xpath, 1, [':href' => $url], 'The client view tab is rendered.');
    $this->assertXPathElements($xpath, 1, [':href' => $url . '/edit'], 'The client edit tab is rendered.');
    $this->assertXPathElements($xpath, 1, [':href' => $url . '/delete'], 'The client delete tab is rendered.');
  }

  /**
   * Tests the revisions for clients.
   */
  public function testClientRevision() {
    // Check that the database table exists and is empty.
    $this->assertTrue(db_table_exists('client_revision'), 'The client revision database table exists.');
    $this->assertClientRevisionTableEmpty();

    // Check that when creating a client, a revision is made.
    $client = $this->createUiClient();
    $this->assertClientTableNotEmpty();
    $result = db_select('client_revision', 'cr')
      ->fields('cr')
      ->condition('cid', $client->id(), '=')
      ->execute()
      ->fetchAll();
    $this->assertEqual(1, $result[0]->vid, 'The first revision has been created.');

    // Check that when editing a client, a new revision is made.
    $this->drupalPostForm('client/' . $client->id() . '/edit', ['name' => $this->randomString()], t('Save'));
    $result = db_select('client_revision', 'cr')
      ->fields('cr')
      ->condition('cid', $client->id(), '=')
      ->execute()
      ->fetchAll();
    $this->assertEqual(2, $result[1]->vid, 'The second revision has been created.');
  }

  /**
   * Tests the "Add invoice" link on the client detail page.
   */
  public function testAddInvoiceLinkOnClientDetailPage() {
    // Create a client.
    $client = $this->createUiClient();

    // Verify that the "Add invoice" link is present on the client detail page.
    $this->drupalGet('client/' . $client->id());
    $this->markTestSkipped('Convert the Invoice module before continuing.');
    $url = url('invoice/add', ['query' => ['cid' => $client->id()]]);
    $this->assertTrue($this->xpath('//a[@href="' . $url . '"]'), 'The create invoice link is found with the query parameter.');

    // Click the link and verify that you land on the correct page. This is the
    // second link with this label, there is also one in the navigation menu.
    $this->clickLink(t('Add invoice'), 1);
    $this->assertSession()->addressEquals('invoice/add', ['query' => ['cid' => $client->id()]], 'We land on the correct url with the correct query parameters after clicking the Create invoice link.');

    // Check that the client is prefilled with the correct value.
    debug($client->name);
    $this->assertXPathElements('//div[contains(@class, "entity-client")]//h2/a[contains(text(), :name)]', 1, [':name' => $client->name], 'The client name is shown in the client summary.');
    $this->assertXPathElements('//div[contains(@class, "field-name-field-client-addres")]', 1, [], 'The client addres is shown in the client summary.');
    $email = field_get_items('client', $client, 'field_client_email');
    $this->assertXPathElements('//div[contains(@class, "field-name-field-client-email")]//a[text() = :email]', 1, [':email' => $email[0]['value']], 'The client email is shown in the client summary.');
    $vat = field_get_items('client', $client, 'field_client_vat');
    $this->assertXPathElements('//div[contains(@class, "field-name-field-client-vat")]//div[text() = :vat]', 1, [':vat' => $vat[0]['value']], 'The client vat number is shown in the client summary.');
  }

  /**
   * {@inheritdoc}
   */
  public function randomClientValues() {
    return $this->traitRandomClientValues();
  }

  /**
   * Constructs an XPath query to find an element on the client entity page.
   *
   * @param string $xpath
   *   The path selector to search for.
   *
   * @return string
   *   The XPath query.
   */
  protected function getClientEntityXpath($xpath = '') {
    return '//div[contains(@class, "entity-client")]' . $xpath;
  }

}
