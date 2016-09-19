<?php

namespace Drupal\business\Tests;

use Drupal\business\Entity\Business;
use Drupal\business\Entity\BusinessInterface;

/**
 * Reusable test methods for testing businesses.
 */
trait BusinessTestHelper {

  /**
   * Check if the properties of the given business match the given values.
   *
   * @param \Drupal\business\Entity\Business $business
   *   The Business entity to check.
   * @param array $values
   *   An associative array of values to check, keyed by property name.
   * @param string $message
   *   The message to display along with the assertion.
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertBusinessProperties(Business $business, array $values, $message = '') {
    return $this->assertEntityFieldValues($business, $values);
  }

  /**
   * Check if the business database table is empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertBusinessTableEmpty($message = '') {
    $result = (bool) $this->connection
      ->select('business', 'b')
      ->fields('b')
      ->range(0, 1)
      ->execute()
      ->fetchAll();
    return $this->assertFalse($result, $message ?: 'The business database table is empty.');
  }

  /**
   * Check if the business database table is not empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertBusinessTableNotEmpty($message = '', $group = 'Other') {
    $result = (bool) $this->connection
      ->select('business', 'b')
      ->fields('b')
      ->range(0, 1)
      ->execute()
      ->fetchAll();
    return $this->assertTrue($result, $message ?: 'The business database table is not empty.');
  }

  /**
   * Creates a new business entity.
   *
   * The business only is created and returned, it is not saved.
   *
   * @param array $values
   *   An optional associative array of values, keyed by property name. Random
   *   values will be applied to all omitted properties.
   *
   * @return \Drupal\business\Entity\BusinessInterface
   *   A new business entity.
   */
  function createBusiness(array $values = array()) {
    // Provide some default values.
    $values += $this->randomBusinessValues();
    $business = Business::create();
    $this->updateEntity($business, $values);

    return $business;
  }

  /**
   * Creates a new business entity through the user interface.
   *
   * The saved business is retrieved by business name and email address. In
   * order to retrieve the correct business entity, these should be unique.
   *
   * @param array $values
   *   An optional associative array of values, keyed by property name. Random
   *   values will be applied to all omitted properties.
   *
   * @return \Business
   *   A new business entity.
   */
  function createUiBusiness(array $values = array()) {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    // Provide some default values.
    $values += $this->randomBusinessValues();

    // Convert the entity property values to form values and submit the form.
    $edit = $this->convertBusinessValuesToFormPostValues($values);
    $this->drupalPost('business/add', $edit, t('Save'));

    // Retrieve the saved business by name and email address and return it.
    $query = new \EntityFieldQuery();
    $query
      ->entityCondition('entity_type', 'business')
      ->entityCondition('bundle', 'business')
      ->fieldCondition('field_business_name', 'value', $values['field_business_name'])
      ->fieldCondition('field_business_email', 'email', $values['field_business_email'])
      ->range(0, 1);
    $result = $query->execute();
    $bids = array_keys($result['business']);
    $this->assertTrue($bids, 'Business was successfully created through the UI.');

    return business_load($bids[0]);
  }

  /**
   * Returns random values for all properties on the business entity.
   *
   * Intended to be used with the entity metadata wrapper.
   *
   * @returns array
   *   An associative array of random values, keyed by property name.
   */
  function randomBusinessValues() {
    return array(
      'name' => $this->randomString(),
      'created' => rand(0, 2000000000),
      'changed' => rand(0, 2000000000),
      'field_business_accountable' => $this->randomString(),
      'field_business_address' => $this->randomAddressField(),
      'field_business_bic' => $this->randomString(),
      'field_business_email' => $this->randomEmail(),
      'field_business_iban' => $this->randomString(),
      'field_business_mobile' => $this->randomPhoneNumberField(),
      'field_business_number' => $this->randomString(),
      'field_business_phone' => $this->randomPhoneNumberField(),
      'field_business_vat' => $this->randomString(),
    );
  }

  /**
   * Returns random field data for the fields in the business entity.
   *
   * @returns array
   *   An associative array of field data, keyed by field name.
   *
   * @deprecated
   *   Use \Drupal\business\Tests\BusinessTestHelper::randomBusinessValues()
   */
  public function randomBusinessFieldValues() {
    throw new \Exception(__METHOD__ . ' is deprecated.');
    $values = array();

    // @todo Add accountable and trade registry number.
    $values['field_business_name'][LANGUAGE_NONE][0]['value'] = $this->randomString();
    $values['field_business_address'][LANGUAGE_NONE][0] = $this->randomAddressField();
    $values['field_business_bic'][LANGUAGE_NONE][0]['value'] = $this->randomString();
    $values['field_business_email'][LANGUAGE_NONE][0]['email'] = $this->randomEmail();
    $values['field_business_iban'][LANGUAGE_NONE][0]['value'] = $this->randomString();
    $values['field_business_mobile'][LANGUAGE_NONE][0] = $this->randomPhoneNumberField();
    $values['field_business_phone'][LANGUAGE_NONE][0] = $this->randomPhoneNumberField();
    $values['field_business_vat'][LANGUAGE_NONE][0]['value'] = $this->randomString();

    return $values;
  }

  /**
   * Returns random data for the basic business properties.
   *
   * These are values for the properties that are present on every business
   * entity regardless of the bundle type.
   *
   * This excludes the Business ID ('bid') property which is immutable.
   *
   * @deprecated
   *   Use \Drupal\business\Tests\BusinessTestHelper::randomBusinessValues()
   *
   * @return array
   *   An associative array of property values, keyed by property name.
   */
  protected function randomBusinessPropertyValues() {
    throw new \Exception(__METHOD__ . ' is deprecated.');
    return array(
      'type' => $this->randomName(),
      'created' => rand(0, 2000000000),
      'changed' => rand(0, 2000000000),
    );
  }

  /**
   * Returns form post values from the given entity values.
   *
   * @param array $values
   *   An associative array of business values, keyed by property name, as
   *   returned by self::randomBusinessValues().
   *
   * @returns array
   *   An associative array of values, keyed by form field name, as used by
   *   parent::drupalPost().
   *
   * @see self::randomBusinessValues()
   */
  public function convertBusinessValuesToFormPostValues(array $values) {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    // @todo Add accountable and trade registry number.
    return array(
      'field_business_name[und][0][value]' => $values['field_business_name'],
      'field_business_email[und][0][email]' => $values['field_business_email'],
      // @todo Support other countries in addition to Belgium.
      'field_business_address[und][0][country]' => 'BE',
      'field_business_address[und][0][thoroughfare]' => $values['field_business_address']['thoroughfare'],
      'field_business_address[und][0][postal_code]' => $values['field_business_address']['postal_code'],
      'field_business_address[und][0][locality]' => $values['field_business_address']['locality'],
      'field_business_vat[und][0][value]' => $values['field_business_vat'],
      'field_business_phone[und][0][number]' => $values['field_business_phone']['number'],
      'field_business_mobile[und][0][number]' => $values['field_business_mobile']['number'],
      'field_business_bic[und][0][value]' => $values['field_business_bic'],
      'field_business_iban[und][0][value]' => $values['field_business_iban'],
    );
  }

  /**
   * Updates the given business with the given properties.
   *
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business entity to update.
   * @param array $values
   *   An associative array of values to apply to the entity, keyed by property
   *   name.
   *
   * @deprecated
   *   Use BaseTestHelper::updateEntity() instead.
   */
  function updateBusiness(BusinessInterface $business, array $values) {
    throw new \Exception(__METHOD__ . ' is deprecated.');
    $this->updateEntity($business, $values);
  }

  /**
   * Adds a business to a user, making the user the business owner.
   *
   * @param \Business $business
   *   The business to add to the user.
   * @param \stdClass $user
   *   The user the business should be added to.
   */
  function addBusinessToUser(\Business $business, \stdClass $user) {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    business_add_to_user($business, $user);
  }

  /**
   * Returns a random business from the database.
   *
   * @return \Business
   *   A random business.
   */
  function randomBusiness() {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $bid = db_select('business', 'b')
      ->fields('b', array('bid'))
      ->orderRandom()
      ->range(0, 1)
      ->execute()
      ->fetchColumn();

    return business_load($bid);
  }

}
