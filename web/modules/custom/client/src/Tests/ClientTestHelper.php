<?php

declare (strict_types = 1);

namespace Drupal\client\Tests;

use Drupal\client\Entity\Client;
use Drupal\client\Entity\ClientInterface;

/**
 * Reusable test methods for testing clients.
 */
trait ClientTestHelper {

  /**
   * Check if the properties of the given client match the given values.
   *
   * @param \Drupal\client\Entity\ClientInterface $client
   *   The Client entity to check.
   * @param array $values
   *   An associative array of values to check, keyed by property name.
   */
  function assertClientProperties(ClientInterface $client, array $values) : void {
    $this->assertEntityFieldValues($client, $values);
  }

  /**
   * Check if the client database table is empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   */
  function assertClientTableEmpty(string $message) : void {
    $result = (bool) $this->connection
      ->select('client', 'c')
      ->fields('c')
      ->range(0, 1)
      ->execute()
      ->fetchAll();
    $this->assertFalse($result, $message ?: 'The client database table is empty.');
  }

  /**
   * Check if the client database table is not empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   */
  function assertClientTableNotEmpty(string $message = '') : void {
    $result = (bool) $this->connection
      ->select('client', 'c')
      ->fields('c')
      ->range(0, 1)
      ->execute()
      ->fetchAll();
    $this->assertTrue($result, $message ?: 'The client database table is not empty.');
  }

  /**
   * Check if the client revision database table is empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   */
  function assertClientRevisionTableEmpty(string $message = '') : void {
    $result = (bool) $this->connection
      ->select('client_revision', 'cr')
      ->fields('cr')
      ->range(0, 1)
      ->execute()
      ->fetchAll();
    $this->assertFalse($result, $message ?: 'The client revision database table is empty.');
  }

  /**
   * Check if the client revision database table is not empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   */
  function assertClientRevisionTableNotEmpty(string $message = '') : void {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $result = (bool) db_select('client_revision', 'cr')->fields('cr')->execute()->fetchAll();
    $this->assertTrue($result, $message ?: 'The client revision database table is not empty.');
  }

  /**
   * Returns a newly created client entity without saving it.
   *
   * This is intended for unit tests. It will not set a business ID. If you are
   * doing a functionality test use $this->createUiClient() instead.
   *
   * @param array $values
   *   An optional associative array of values, keyed by property name. Random
   *   values will be applied to all omitted properties.
   *
   * @return \Drupal\client\Entity\ClientInterface
   *   A new client entity.
   *
   * @throws \Exception
   *   Thrown if the required business ID parameter is not set.
   */
  function createClient(array $values = []) : ClientInterface {
    // Check if the business ID is set, this is a required parameter.
    if (!isset($values['business'])) {
      throw new \Exception('The "business" property is required.');
    }

    // Provide some default values.
    $values += $this->randomClientValues();

    $client = Client::create();
    $this->updateEntity($client, $values);

    return $client;
  }

  /**
   * Creates a new client entity through the user interface.
   *
   * The saved client is retrieved by client name and email address. In order to
   * retrieve the correct client entity, these should be unique.
   *
   * @param array $values
   *   An optional associative array of values, keyed by property name. Random
   *   values will be applied to all omitted properties.
   *
   * @return \Drupal\client\Entity\ClientInterface
   *   A new client entity.
   */
  function createUiClient(array $values = []) : ClientInterface {
    // Provide some default values.
    $values += $this->randomClientValues();

    // Convert the entity property values to form values and submit the form.
    $edit = $this->convertClientValuesToFormPostValues($values);
    $this->drupalPostForm('client/add', $edit, t('Save'));

    // Retrieve the saved client by name and email address and return it.
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = $this->container->get('entity.query')->get('client');
    $query
      ->condition('name', $values['name'])
      ->condition('field_client_email', $values['field_client_email'])
      ->range(0, 1);
    $result = $query->execute();
    $this->assertTrue($result, 'Client was successfully created through the UI.');

    return Client::load(reset($result));
  }

  /**
   * Returns random values for all editable properties on the client entity.
   *
   * @returns array
   *   An associative array of random values, keyed by property name.
   */
  function randomClientValues() : array {
    return [
      'name' => $this->randomString(),
      'field_client_address' => $this->randomAddressField(),
      'field_client_shipping_address' => $this->randomAddressField(),
      'field_client_email' => $this->randomEmail(),
      'field_client_notes' => $this->randomString(),
      'field_client_phone' => $this->randomPhoneNumberField(),
      'field_client_vat' => $this->randomString(),
      // @todo Which is correct?
      'field_client_website' => 'http://www.test.be',
      //'field_client_website' => ['uri' => 'http://www.test.be'],
    ];
  }

  /**
   * Returns random data for the basic client properties.
   *
   * These are values for the properties that are present on every client entity
   * regardless of the bundle type.
   *
   * This excludes the client ID ('cid') which is immutable.
   *
   * @return array
   *   An associative array of property values, keyed by property name.
   */
  protected function randomClientPropertyValues() : array {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    return [
      'name' => $this->randomString(),
      'type' => $this->randomName(),
      'business' => $this->randomBusiness()->identifier(),
      'created' => rand(0, 2000000000),
      'changed' => rand(0, 2000000000),
    ];
  }

  /**
   * Returns random field data for the fields in the client entity.
   *
   * @returns array
   *   An associative array of field data, keyed by field name.
   */
  public function randomClientFieldValues() : array {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $values = [];

    $values['field_client_address'][0] = $this->randomAddressField();
    $values['field_client_shipping_address'][0] = $this->randomAddressField();
    $values['field_client_email'][0]['value'] = $this->randomEmail();
    $values['field_client_notes'][0]['value'] = $this->randomString();
    $values['field_client_phone'][0] = $this->randomPhoneNumberField();
    $values['field_client_vat'][0]['value'] = $this->randomString();
    $values['field_client_website'][0]['uri'] = 'http://www.test.be';

    return $values;
  }

  /**
   * Returns form post values from the given entity values.
   *
   * @param array $values
   *   An associative array of client values, keyed by property name, as
   *   returned by self::randomClientValues().
   *
   * @returns array
   *   An associative array of values, keyed by form field name, as used by
   *   parent::drupalPostForm().
   *
   * @see self::randomClientValues()
   */
  public function convertClientValuesToFormPostValues(array $values) : array {
    return [
      'name[0][value]' => $values['name'],
      'field_client_email[0][value]' => $values['field_client_email'],
      'field_client_address[0][address][country_code]' => $values['field_client_address']['country_code'],
      'field_client_address[0][address][address_line1]' => $values['field_client_address']['address_line1'],
      'field_client_address[0][address][postal_code]' => $values['field_client_address']['postal_code'],
      'field_client_address[0][address][locality]' => $values['field_client_address']['locality'],
      'field_client_shipping_address[0][address][country_code]' => $values['field_client_shipping_address']['country_code'],
      'field_client_shipping_address[0][address][address_line1]' => $values['field_client_shipping_address']['address_line1'],
      'field_client_shipping_address[0][address][postal_code]' => $values['field_client_shipping_address']['postal_code'],
      'field_client_shipping_address[0][address][locality]' => $values['field_client_shipping_address']['locality'],
      'field_client_vat[0][value]' => $values['field_client_vat'],
      'field_client_phone[0][raw_input]' => $values['field_client_phone']['raw_input'],
      'field_client_notes[0][value]' => $values['field_client_notes'],
      // @todo Which is correct?
      'field_client_website[0][uri]' => $values['field_client_website'],
      // 'field_client_website[0][uri]' => $values['field_client_website']['uri'],
    ];
  }

  /**
   * Updates the given client with the given properties.
   *
   * @param \Drupal\client\Entity\ClientInterface $client
   *   The client entity to update.
   * @param array $values
   *   An associative array of values to apply to the entity, keyed by property
   *   name.
   *
   * @deprecated
   *   Use BaseTestHelper::updateEntity() instead.
   */
  function updateClient(ClientInterface $client, array $values) : void {
    throw new \Exception(__METHOD__ . ' is deprecated.');
    $wrapper = entity_metadata_wrapper('client', $client);
    foreach ($values as $property => $value) {
      $wrapper->$property->set($value);
    }
  }

  /**
   * Returns a random client from the database.
   *
   * @return \Drupal\client\Entity\ClientInterface
   *   A random client.
   */
  function randomClient() : ClientInterface {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $cid = db_select('client', 'c')
      ->fields('c', ['cid'])
      ->orderRandom()
      ->range(0, 1)
      ->execute()
      ->fetchColumn();

    return Client::load($cid);
  }

}
