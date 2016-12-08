<?php

declare (strict_types = 1);

namespace Drupal\business\Tests;

use Drupal\business\Entity\Business;
use Drupal\business\Entity\BusinessInterface;
use Drupal\user\UserInterface;

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
   *
   * @todo Declare as void return type.
   */
  public function assertBusinessProperties(Business $business, array $values) {
    $this->assertEntityFieldValues($business, $values);
  }

  /**
   * Checks if the business table exists.
   *
   * @todo Declare as void return type.
   */
  public function assertBusinessTableExists() {
    $this->assertTrue($this->connection->schema()->tableExists('business'), 'The business database table exists.');
  }

  /**
   * Checks if the business database table is empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   *
   * @todo Declare as void return type.
   */
  public function assertBusinessTableEmpty(string $message = '') {
    $result = (bool) $this->connection
      ->select('business', 'b')
      ->fields('b')
      ->range(0, 1)
      ->execute()
      ->fetchAll();
    $this->assertFalse($result, $message ?: 'The business database table is empty.');
  }

  /**
   * Checks if the business database table is not empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   *
   * @todo Declare as void return type.
   */
  public function assertBusinessTableNotEmpty(string $message = '') {
    $result = (bool) $this->connection
      ->select('business', 'b')
      ->fields('b')
      ->range(0, 1)
      ->execute()
      ->fetchAll();
    $this->assertTrue($result, $message ?: 'The business database table is not empty.');
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
  public function createBusiness(array $values = []) : BusinessInterface {
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
   * This only works in functional tests.
   *
   * @param array $values
   *   An optional associative array of values, keyed by property name. Random
   *   values will be applied to all omitted properties.
   *
   * @return \Drupal\business\Entity\BusinessInterface
   *   A new business entity.
   */
  public function createUiBusiness(array $values = []) : BusinessInterface {
    // Provide some default values.
    $values += $this->randomBusinessValues();

    // Convert the entity property values to form values and submit the form.
    $edit = $this->convertBusinessValuesToFormPostValues($values);

    $this->drupalPostForm('business/add', $edit, t('Save'));

    // Retrieve the saved business by name and email address and return it.
    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = $this->container->get('entity.query')->get('business');
    $query
      ->condition('name', $values['name'])
      ->condition('field_business_email', $values['field_business_email'])
      ->range(0, 1);
    $result = $query->execute();
    $bid = reset($result);
    $this->assertTrue($bid, 'Business was successfully created through the UI.');

    return Business::load($bid);
  }

  /**
   * Returns random values for all properties on the business entity.
   *
   * @returns array
   *   An associative array of random values, keyed by property name.
   */
  public function randomBusinessValues() : array {
    return [
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
    ];
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
  public function randomBusinessFieldValues() : array {
    throw new \Exception(__METHOD__ . ' is deprecated.');
    $values = [];

    // @todo Add accountable and trade registry number.
    $values['name'][LANGUAGE_NONE][0]['value'] = $this->randomString();
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
  protected function randomBusinessPropertyValues() : array {
    throw new \Exception(__METHOD__ . ' is deprecated.');
    return [
      'type' => $this->randomName(),
      'created' => rand(0, 2000000000),
      'changed' => rand(0, 2000000000),
    ];
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
  public function convertBusinessValuesToFormPostValues(array $values) : array {
    // @todo Add accountable and trade registry number.
    return [
      'name[0][value]' => $values['name'],
      'field_business_email[0][value]' => $values['field_business_email'],
      // @todo Support other countries in addition to Belgium.
      'field_business_address[0][country_code]' => 'BE',
      'field_business_address[0][address_line1]' => $values['field_business_address']['address_line1'],
      'field_business_address[0][postal_code]' => $values['field_business_address']['postal_code'],
      'field_business_address[0][locality]' => $values['field_business_address']['locality'],
      'field_business_vat[0][value]' => $values['field_business_vat'],
      'field_business_iban[0][value]' => $values['field_business_iban'],
      'field_business_bic[0][value]' => $values['field_business_bic'],
      'field_business_accountable[0][value]' => $values['field_business_accountable'],
      'field_business_number[0][value]' => $values['field_business_number'],
      'field_business_phone[0][raw_input]' => $values['field_business_phone']['raw_input'],
      'field_business_mobile[0][raw_input]' => $values['field_business_mobile']['raw_input'],
    ];
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
  public function updateBusiness(BusinessInterface $business, array $values) {
    throw new \Exception(__METHOD__ . ' is deprecated.');
  }

  /**
   * Adds a business to a user, making the user the business owner.
   *
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business to add to the user.
   * @param \Drupal\user\UserInterface $user
   *   The user the business should be added to.
   */
  public function addBusinessToUser(BusinessInterface $business, UserInterface $user) {
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $businesses */
    $businesses = $user->get('field_user_businesses');
    $businesses->appendItem(['target_id' => $business->id()]);
    $user->save();
  }

  /**
   * Returns a random business from the database.
   *
   * @return \Drupal\business\Entity\BusinessInterface
   *   A random business.
   */
  public function randomBusiness() : BusinessInterface {
    $bid = $this->connection->select('business', 'b')
      ->fields('b', ['bid'])
      ->orderRandom()
      ->range(0, 1)
      ->execute()
      ->fetchColumn();

    return Business::load($bid);
  }

  /**
   * Returns the unchanged, i.e. not modified, business from the database.
   *
   * @param int $id
   *   The ID of the business to return.
   *
   * @return \Drupal\business\Entity\Business
   *   The business.
   */
  public function loadUnchangedBusiness(int $id) : Business {
    return $this->loadUnchangedEntity('business', $id);
  }

}
