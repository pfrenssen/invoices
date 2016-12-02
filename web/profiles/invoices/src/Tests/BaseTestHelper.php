<?php

declare (strict_types = 1);

namespace Drupal\invoices\Tests;

use Behat\Mink\Element\NodeElement;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Random;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\StringTranslation\PluralTranslatableMarkup;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Reusable test methods.
 */
trait BaseTestHelper {

  /**
   * Create a user with the given user role.
   *
   * This is based on UserCreationTrait::createUser() with the following
   * changes:
   * - It accepts a user role rather than a set of permissions.
   * - It populates the fields for the first and last name.
   *
   * @param string $role
   *   The user role to assign to the user.
   *
   * @return \Drupal\user\Entity\User
   *   A fully loaded user object with pass_raw property.
   *
   * @throws \Exception
   *   Thrown when user creation fails.
   */
  protected function drupalCreateUserWithRole(string $role) : User {
    $role = Role::load($role);

    // Create a user assigned to that role.
    $name = $this->randomMachineName();
    $edit = [
      'name' => $name,
      'mail' => $name . '@example.com',
      'pass' => user_password(),
      'status' => 1,
      'roles' => [$role->id()],
      'field_user_first_name' => $this->randomString(),
      'field_user_last_name' => $this->randomString(),
    ];

    $account = User::create($edit);
    $account->save();

    $this->assertTrue($account->id());
    if (!$account->id()) {
      throw new \Exception('User creation failed.');
    }

    // Add the raw password so that we can log in as this user.
    $account->pass_raw = $edit['pass'];
    // Support BrowserTestBase as well.
    $account->passRaw = $account->pass_raw;

    return $account;
  }

  /**
   * Updates the given entity with the given values.
   *
   * The entity is only updated, it is not saved.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to update.
   * @param array $values
   *   An associative array of values to apply to the entity, keyed by field
   *   name.
   */
  protected function updateEntity(ContentEntityInterface $entity, array $values) {
    foreach ($values as $property => $value) {
      $entity->set($property, $value);
    }
  }

  /**
   * Check if the field values of the given entity match the given values.
   *
   * @param \Drupal\core\Entity\ContentEntityInterface $entity
   *   The entity to check.
   * @param array $values
   *   An associative array of values to check, keyed by field name.
   *
   * @throws \Exception
   *   Thrown when an unknown field is compared.
   */
  protected function assertEntityFieldValues(ContentEntityInterface $entity, array $values) {
    foreach ($values as $field_name => $expected_value) {
      $field_item_list = $entity->get($field_name);
      // Check if a single value or multiple values are expected.
      if (!is_array($expected_value) || (!empty($expected_value) && !is_numeric(current(array_keys($expected_value))))) {
        $count = $field_item_list->count();
        $this->assertEquals(1, $count, "Expected only a single value for field '$field_name'. Found $count values.");

        /** @var FieldItemInterface $item */
        $item = $field_item_list->first();
        if (!is_array($expected_value)) {
          $main_property = $item->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName();
          $actual = $field_item_list->$main_property;
        }
        else {
          $actual = $field_item_list->first()->getValue();
          // Filter out empty (default) values.
          $actual = array_filter($actual);
        }
      }
      else {
        throw new \Exception('Support for multivalue fields is not yet implemented.');
      }
      $this->assertEquals($expected_value, $actual, $field_name);
    }
  }

  /**
   * Check if the given form fields are indicating that validation failed.
   *
   * This checks for the presence of the 'error' class on the field.
   *
   * @param array $field_names
   *   An indexed array of field names that should be checked.
   */
  protected function assertFieldValidationFailed(array $field_names) {
    foreach ($field_names as $field_name) {
      $xpath = '//textarea[@name=:value and contains(@class, "error")]|//input[@name=:value and contains(@class, "error")]|//select[@name=:value and contains(@class, "error")]';
      $elements = $this->xpath($this->buildXPathQuery($xpath, [':value' => $field_name]));
      $this->assertTrue($elements, new FormattableMarkup('The field %field has the "error" class.', ['%field' => $field_name]));
    }
  }

  /**
   * Check if no pager is present on the page.
   *
   * @param string $message
   *   The message to display along with the assertion.
   */
  protected function assertNoPager(string $message = '') {
    $message = $message ?: 'No pager is present on the page.';
    $xpath = '//nav[@class = "pager"]';
    $this->assertXPathElements($xpath, 0, [], $message);
  }

  /**
   * Check if a pager is present on the page.
   *
   * @param string $message
   *   The message to display along with the assertion.
   */
  protected function assertPager(string $message = '') {
    $message = $message ?: 'A pager is present on the page.';
    $xpath = '//nav[@class = "pager"]';
    $this->assertXPathElements($xpath, 1, [], $message);
  }

  /**
   * Check if the displayed messages match the given messages.
   *
   * This performs the following checks:
   * - All messages appear in the right place ('status', 'warning', 'error').
   * - No unexpected messages are shown.
   *
   * @param array $messages
   *   An associative array of status messages that should be displayed, keyed
   *   by message type (either 'status', 'warning' or 'error'). Every type
   *   contains an indexed array of status messages.
   */
  protected function assertStatusMessages(array $messages) {
    // Messages can contain a mix of HTML and sanitized HTML, for example:
    // '<em class="placeholder">&lt;script&gt;alert();&lt;&#039;script&gt;</em>'
    // Unfortunately, check_plain() and SimpleXML::asXml() encode quotes and
    // slashes differently. Work around this by doing the message type check
    // with decoded strings, but also check if the original encoded strings are
    // present in the raw HTML to avoid false negatives.
    $shown_messages = $this->decodeStatusMessages($this->getStatusMessages());
    $decoded_messages = $this->decodeStatusMessages($messages);

    foreach (['status', 'warning', 'error'] as $type) {
      $expected_messages = !empty($decoded_messages[$type]) ? $decoded_messages[$type] : [];

      // Loop over the messages that are shown and match them against the
      // expected messages.
      foreach ($shown_messages[$type] as $shown_message) {
        $key = array_search($shown_message, $expected_messages);

        // If the message is not one of the expected messages, fail.
        $this->assertNotFalse($key, new FormattableMarkup('Unexpected @type message: @message', ['@type' => $type, '@message' => $shown_message]));

        // Remove found messages from the list.
        unset($expected_messages[$key]);
      }
      // Fail if any of the expected messages is not shown.
      $this->assertEmpty($expected_messages, new FormattableMarkup('Did not find @type messages: @messages', ['@type' => $type, '@messages' => '"' . implode('", "', $expected_messages) . '"']));
    }

    // Also check if the correctly encoded messages are present in the raw HTML.
    // The above asserts do not detect if all HTML entities are correctly
    // encoded, and could let insecure status messages slip through as false
    // negatives.
    foreach ($messages as $type => $expected_messages) {
      foreach ($expected_messages as $expected_message) {
        $this->assertSession()->responseContains($expected_message, new FormattableMarkup('Found correctly encoded message in raw HTML: @message', ['@message' => $expected_message]));
      }
    }
  }

  /**
   * Check if the status messages about required fields are shown.
   *
   * This will fail if any other messages are shown.
   *
   * Example:
   * @code
   *   $required_fields = [
   *     'name' => t('Client name'),
   *     'field_client_email[0][email]' => t('Email address'),
   *   ];
   *   $this->assertRequiredFieldMessages($required_fields);
   * @endcode
   *
   * @param array $required_fields
   *   An associative array of required fields, keyed on field name, with the
   *   human readable name as value.
   * @param array $messages
   *   An associative array of status messages that should be displayed, keyed
   *   by message type (either 'status', 'warning' or 'error'). Every type
   *   contains an indexed array of status messages. When omitted the standard
   *   messages of the Field module will be used.
   * @param string $message
   *   The message to display along with the assertion.
   */
  protected function assertRequiredFieldMessages(array $required_fields, array $messages = [], string $message = '') {
    // Use the standard message of the Field module by default.
    if (!$messages) {
      foreach ($required_fields as $required_field) {
        $messages['error'][] = (string) t('@name field is required.', [
          '@name' => $required_field,
        ]);
      }
    }
    $this->assertFieldValidationFailed(array_keys($required_fields));
    $this->assertStatusMessages($messages);
  }

  /**
   * Check if element(s) that match the given XPath expression are present.
   *
   * @param string $xpath
   *   The XPath expression to execute on the page.
   * @param int $count
   *   The number of elements that should match the expression.
   * @param array $arguments
   *   Optional array of arguments to pass to DrupalWebTestCase::xpath().
   * @param string $message
   *   The message to display along with the assertion.
   */
  protected function assertXPathElements(string $xpath, int $count, array $arguments = [], string $message = '') {
    // Provide a default message.
    $message = $message ?: (string) new PluralTranslatableMarkup($count, 'The element matching the XPath expression is present in the page.', 'The @count elements matching the XPath expression are present in the page.');

    $elements = $this->xpath($xpath, $arguments);
    $this->assertEquals($count, count($elements), $message);
  }

  /**
   * Decodes HTML entities of a given array of status messages.
   *
   * @param array $messages
   *   An associative array of status messages to decode, keyed by message type
   *   (either 'status', 'warning' or 'error'). Every type contains an indexed
   *   array of status messages.
   *
   * @return array
   *   The decoded array of status messages.
   */
  protected function decodeStatusMessages(array $messages) : array {
    foreach (array_keys($messages) as $type) {
      foreach ($messages[$type] as $key => $encoded_message) {
        $messages[$type][$key] = html_entity_decode($encoded_message, ENT_QUOTES, 'UTF-8');
      }
    }
    return $messages;
  }

  /**
   * Returns the status messages that are found in the page.
   *
   * @return array
   *   An associative array of status messages, keyed by message type (either
   *   'status', 'warning' or 'error'). Every type contains an indexed array of
   *   status messages.
   */
  protected function getStatusMessages() : array {
    $return = [
      'error' => [],
      'warning' => [],
      'status' => [],
    ];

    foreach (array_keys($return) as $type) {
      // Retrieve the entire messages container.
      /** @var NodeElement[] $messages */
      if ($messages = $this->xpath('//div[contains(concat(" ", @class, " "), " messages ") and contains(concat(" ", @class, " "), " messages--' . $type . ' ")]')) {
        // If only a single message is being rendered by theme_status_messages()
        // it outputs it as text preceded by an <h2> element that is provided
        // for accessibility reasons. An example:
        //
        // @code
        //   <div class="messages status">
        //     <h2 class="element-invisible">Status message</h2>
        //     Email field is required.
        //   </div>
        // @endcode
        //
        // While this is valid HTML, it is invalid XML, so this can't be parsed
        // with XPath. We can turn it into valid XML again by removing the
        // accessibility element using DOMDocument.
        $dom = new \DOMDocument();

        // Load the messages HTML using UTF-8 encoding.
        @$dom->loadHTML('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>' . $messages[0]->getOuterHtml() . '</body></html>');
        // Strip the accessibility element.
        $accessibility_message = $dom->getElementsByTagName('h2')->item(0);
        $accessibility_message->parentNode->removeChild($accessibility_message);

        // We have valid XML now, so we can use XPath to find the messages. If
        // there are multiple messages, they are output in an unordered list. A
        // single message is output directly in the <div> container. In case of
        // an 'error' message, the output is wrapped in a second div.
        $xpath = new \DOMXPath($dom);
        $paths = [
          // Multiple messages of type 'error'.
          '//body/div/div/ul/li',
          // A single message of type 'error'.
          '//body/div/div',
          // Multiple messages of type 'warning' or 'status'.
          '//body/div/ul/li',
          // A single message of type 'warning' or 'status'.
          '//body/div',
        ];
        foreach ($paths as $path) {
          $elements = $xpath->query($path);
          if ($elements->length) {
            break;
          }
        }

        // Loop over the messages. Strip the containing element, which is either
        // a <div> or a <li>, before adding them to the return array.
        foreach ($elements as $element) {
          preg_match('/^<(li|div)[^>]*>(.*)<\/(li|div)>$/s', $dom->saveHTML($element), $matches);
          $return[$type][] = trim($matches[2]);
        }
      }
    }

    return $return;
  }

  /**
   * Returns a random address field.
   *
   * @return array
   *   A random address field.
   */
  protected function randomAddressField() : array {
    // The Address Field module trims all input and converts double spaces to
    // single spaces before saving the values to the database. We make sure our
    // random data does the same so we do not get random failures.
    // @see addressfield_field_presave()
    // @todo Is this still necessary in D8 with the Address module?
    return [
      'country_code' => chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)),
      'locality' => trim(str_replace('  ', ' ', $this->randomString())),
      'postal_code' => (string) rand(1000, 9999),
      'address_line1' => trim(str_replace('  ', ' ', $this->randomString())),
    ];
  }

  /**
   * Returns a random email address.
   *
   * @return string
   *   A random email address.
   */
  protected function randomEmail() : string {
    return strtolower($this->randomMachineName()) . '@example.com';
  }

  /**
   * Returns a random Belgian phone number.
   *
   * @todo Add support for international numbers.
   *
   * @param string $countrycode
   *   The country code for which to return a phone number. Currently unused.
   *
   * @return string
   *   A random phone number.
   */
  public static function randomPhoneNumber(string $countrycode = 'BE') : string {
    $matches = NULL;
    do {
      $number = (string) rand(10000000, 89000000);
      // This regex is taken from libphonenumber. See PhoneNumberMetadata_BE.
      preg_match('/(?:1[0-69]|[49][23]|5\\d|6[013-57-9]|71|8[0-79])[1-9]\\d{5}|[23][2-8]\\d{6}/', $number, $matches);
    } while (empty($matches[0]));

    return (string) $number;
  }

  /**
   * Returns random data for a Phone Number field.
   *
   * @todo Support countries other than Belgium.
   *
   * @param string $countrycode
   *   The country code for which to return a phone number.
   *
   * @return array
   *   An array with the following keys:
   *   - number: the phone number, without country code or leading zeroes.
   *   - countrycode: the country code for the phone number.
   */
  protected function randomPhoneNumberField(string $countrycode = 'BE') : array {
    return [
      'raw_input' => $this->randomPhoneNumber($countrycode),
      // @todo Add this back when we have a better phone field.
      // 'countrycode' => $countrycode,
    ];
  }

  /**
   * Formats the given phone number in the given format using libphonenumber.
   *
   * @param string $number
   *   The phone number to format.
   * @param int $format
   *   The format to use. See PhoneNumberFormat for possible options.
   * @param string $countrycode
   *   The default country code to use if the country code is missing from the
   *   number.
   *
   * @return string
   *   The formatted number.
   */
  protected function formatPhoneNumber(string $number, int $format = PhoneNumberFormat::E164, string $countrycode = 'BE') : string {
    $util = PhoneNumberUtil::getInstance();
    $number = $util->parseAndKeepRawInput($number, $countrycode);
    return $util->format($number, $format);
  }

  /**
   * Prepares the input for the entity reference autocomplete field.
   *
   * @param string $name
   *   The name of the entity that is referenced.
   * @param string $id
   *   The id of the entity that is referenced.
   *
   * @return string
   *   The input for the entity reference autocomplete field.
   */
  protected function entityReferenceFieldValue(string $name, string $id) : string {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    // Prepare the field input the way entityreference expects it.
    // @see entityreference_autocomplete_callback_get_matches()
    $value = "{$name} ({$id})";
    // Contrary to entityreference_autocomplete_callback_get_matches() we do
    // not start with an HTML safe string so we don't need to strip tags and
    // decode HTML entities.
    $value = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim($value)));
    if (strpos($value, ',') !== FALSE || strpos($value, '"') !== FALSE) {
      $value = '"' . str_replace('"', '""', $value) . '"';
    }

    return $value;
  }

  /**
   * Generates a random string containing letters and numbers.
   *
   * This is a duplicate of DrupalWebTestCase::randomName(). It is provided here
   * so it can be used in Behat tests.
   *
   * The string will always start with a letter. The letters may be upper or
   * lower case. This method is better for restricted inputs that do not
   * accept certain characters. For example, when testing input fields that
   * require machine readable values (i.e. without spaces and non-standard
   * characters) this method is best.
   *
   * Do not use this method when testing unvalidated user input. Instead, use
   * DrupalWebTestCase::randomString().
   *
   * @param int $length
   *   Length of random string to generate.
   *
   * @return string
   *   Randomly generated string.
   *
   * @deprecated
   *   Use \Drupal\Tests\RandomGeneratorTrait::randomMachineName() instead.
   *
   * @see DrupalWebTestCase::randomString()
   */
  public static function randomName(int $length = 8) : string {
    return (new Random())->name($length, TRUE);
  }

  /**
   * Returns the unchanged, i.e. not modified, entity from the database.
   *
   * @param string $entity_type_id
   *   The entity type.
   * @param mixed $id
   *   The ID of the entity to return.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity.
   */
  public function loadUnchangedEntity(string $entity_type_id, $id) : EntityInterface {
    return $this->entityTypeManager->getStorage($entity_type_id)->loadUnchanged($id);
  }

}
