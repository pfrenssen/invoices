<?php

declare (strict_types = 1);

namespace Drupal\client\Tests;

use Drupal\client\Entity\Client;

/**
 * Reusable test methods for testing clients.
 */
trait ClientTestHelper {

  /**
   * Check if the properties of the given client match the given values.
   *
   * @param \Client $client
   *   The Client entity to check.
   * @param array $values
   *   An associative array of values to check, keyed by property name.
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertClientProperties(Client $client, array $values, string $message = '', string $group = 'Other') : bool {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    return $this->assertEntityProperties('client', $client, $values, $message, $group);
  }

  /**
   * Check if the client database table is empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertClientTableEmpty(string $message = '', string $group = 'Other') : bool {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $result = (bool) db_select('client', 'c')->fields('c')->execute()->fetchAll();
    return $this->assertFalse($result, $message ?: 'The client database table is empty.', $group);
  }

  /**
   * Check if the client database table is not empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertClientTableNotEmpty(string $message = '', string $group = 'Other') : bool {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $result = (bool) db_select('client', 'c')->fields('c')->execute()->fetchAll();
    return $this->assertTrue($result, $message ?: 'The client database table is not empty.', $group);
  }

  /**
   * Check if the client revision database table is empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertClientRevisionTableEmpty(string $message = '', string $group = 'Other') : bool {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $result = (bool) db_select('client_revision', 'cr')->fields('cr')->execute()->fetchAll();
    return $this->assertFalse($result, $message ?: 'The client revision database table is empty.', $group);
  }

  /**
   * Check if the client revision database table is not empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertClientRevisionTableNotEmpty(string $message = '', string $group = 'Other') : bool {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $result = (bool) db_select('client_revision', 'cr')->fields('cr')->execute()->fetchAll();
    return $this->assertTrue($result, $message ?: 'The client revision database table is not empty.', $group);
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
   * @return \Client
   *   A new client entity.
   *
   * @throws \Exception
   *   Thrown if the required business ID parameter is not set.
   */
  function createClient(array $values = []) : Client {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    // Check if the business ID is set, this is a required parameter.
    if (!isset($values['bid'])) {
      throw new \Exception('The "bid" property is required.');
    }

    // Provide some default values.
    $values += $this->randomClientValues();

    // Creating an empty client and then updating the properties through the
    // entity metadata wrapper is easier than $field[LANGUAGE_NONE][0][...].
    $client = client_create();
    $this->updateClient($client, $values);

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
   * @return \Client
   *   A new client entity.
   */
  function createUiClient(array $values = []) : Client {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    // Provide some default values.
    $values += $this->randomClientValues();

    // Convert the entity property values to form values and submit the form.
    $edit = $this->convertClientValuesToFormPostValues($values);
    $this->drupalPost('client/add', $edit, t('Save'));

    // Retrieve the saved client by name and email address and return it.
    $query = new \EntityFieldQuery();
    $query
      ->entityCondition('entity_type', 'client')
      ->entityCondition('bundle', 'client')
      ->propertyCondition('name', $values['name'])
      ->fieldCondition('field_client_email', 'email', $values['field_client_email'])
      ->range(0, 1);
    $result = $query->execute();
    $cids = array_keys($result['client']);
    $this->assertTrue($cids, 'Client was successfully created through the UI.');

    return client_load($cids[0]);
  }

  /**
   * Returns random values for all editable properties on the client entity.
   *
   * Intended to be used with the entity metadata wrapper.
   * These include the fields from the 'client' bundle.
   *
   * @returns array
   *   An associative array of random values, keyed by property name.
   */
  function randomClientValues() : array {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    return [
      'name' => $this->randomString(),
      'field_client_address' => $this->randomAddressField(),
      'field_client_shipping_address' => $this->randomAddressField(),
      'field_client_email' => $this->randomEmail(),
      'field_client_notes' => $this->randomString(),
      'field_client_phone' => $this->randomPhoneNumberField(),
      'field_client_vat' => $this->randomString(),
      'field_client_website' => ['url' => 'http://www.test.be'],
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
      'bid' => $this->randomBusiness()->identifier(),
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

    $values['field_client_address'][LANGUAGE_NONE][0] = $this->randomAddressField();
    $values['field_client_shipping_address'][LANGUAGE_NONE][0] = $this->randomAddressField();
    $values['field_client_email'][LANGUAGE_NONE][0]['email'] = $this->randomEmail();
    $values['field_client_notes'][LANGUAGE_NONE][0]['value'] = $this->randomString();
    $values['field_client_phone'][LANGUAGE_NONE][0] = $this->randomPhoneNumberField();
    $values['field_client_vat'][LANGUAGE_NONE][0]['value'] = $this->randomString();
    $values['field_client_website'][LANGUAGE_NONE][0]['url'] = 'http://www.test.be';

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
   *   parent::drupalPost().
   *
   * @see self::randomClientValues()
   */
  public function convertClientValuesToFormPostValues(array $values) : array {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    return [
      'name' => $values['name'],
      'field_client_email[und][0][email]' => $values['field_client_email'],
      // @todo Support other countries in addition to Belgium.
      'field_client_address[und][0][country]' => 'BE',
      'field_client_address[und][0][thoroughfare]' => $values['field_client_address']['thoroughfare'],
      'field_client_address[und][0][postal_code]' => $values['field_client_address']['postal_code'],
      'field_client_address[und][0][locality]' => $values['field_client_address']['locality'],
      // @todo Support other countries in addition to Belgium.
      'field_client_shipping_address[und][0][country]' => 'BE',
      'field_client_shipping_address[und][0][thoroughfare]' => $values['field_client_shipping_address']['thoroughfare'],
      'field_client_shipping_address[und][0][postal_code]' => $values['field_client_shipping_address']['postal_code'],
      'field_client_shipping_address[und][0][locality]' => $values['field_client_shipping_address']['locality'],
      'field_client_vat[und][0][value]' => $values['field_client_vat'],
      'field_client_phone[und][0][number]' => $values['field_client_phone']['number'],
      'field_client_notes[und][0][value]' => $values['field_client_notes'],
      'field_client_website[und][0][url]' => $values['field_client_website']['url'],
    ];
  }

  /**
   * Updates the given client with the given properties.
   *
   * @param \Client $client
   *   The client entity to update.
   * @param array $values
   *   An associative array of values to apply to the entity, keyed by property
   *   name.
   */
  function updateClient(Client $client, array $values) {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $wrapper = entity_metadata_wrapper('client', $client);
    foreach ($values as $property => $value) {
      $wrapper->$property->set($value);
    }
  }

  /**
   * Returns a random client from the database.
   *
   * @return \Client
   *   A random client.
   */
  function randomClient() : Client {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $cid = db_select('client', 'c')
      ->fields('c', ['cid'])
      ->orderRandom()
      ->range(0, 1)
      ->execute()
      ->fetchColumn();

    return client_load($cid);
  }

}
