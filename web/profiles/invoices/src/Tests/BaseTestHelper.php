<?php

namespace Drupal\invoices\Tests;

/**
 * Reusable test methods.
 */
trait BaseTestHelper {

  /**
   * Create a user with the given user role.
   *
   * This is based on drupalCreateUser() but accepts a user role rather than a
   * set of permissions.
   *
   * @param string $role
   *   The user role to assign to the user.
   *
   * @return object|false
   *   A fully loaded user object with pass_raw property, or FALSE if account
   *   creation fails.
   */
  protected function drupalCreateUserWithRole($role) {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $role = user_role_load_by_name($role);

    // Create a user assigned to that role.
    $name = $this->randomName();
    $edit = [
      'name' => $name,
      'mail' => $name . '@example.com',
      'pass' => user_password(),
      'status' => 1,
      'roles' => [$role->rid => $role->rid],
      'field_user_first_name' => [
        LANGUAGE_NONE => [
          ['value' => $this->randomString()],
        ],
      ],
      'field_user_last_name' => [
        LANGUAGE_NONE => [
          ['value' => $this->randomString()],
        ],
      ],
    ];

    $account = user_save(drupal_anonymous_user(), $edit);

    $this->assertTrue(!empty($account->uid), t('User created with name %name and pass %pass', ['%name' => $edit['name'], '%pass' => $edit['pass']]), t('User login'));
    if (empty($account->uid)) {
      return FALSE;
    }

    // Add the raw password so that we can log in as this user.
    $account->pass_raw = $edit['pass'];
    return $account;
  }

  /**
   * Check if the properties of the given entity match the given values.
   *
   * This uses the entity metadata wrapper, so the values passed should match
   * the format used by the wrapper.
   *
   * @param string $entity_type
   *   The type of the entity.
   * @param \Entity $entity
   *   The entity to check.
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
  function assertEntityProperties($entity_type, \Entity $entity, array $values, $message = '', $group = 'Other') {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $wrapper = entity_metadata_wrapper($entity_type, $entity);

    $result = TRUE;
    foreach ($values as $property => $value) {
      if (is_array($value)) {
        $result &= $this->assertFalse(drupal_array_diff_assoc_recursive($value, $wrapper->$property->value()), format_string('The %property property has the correct value.', ['%property' => $property]));
      }
      else {
        $result &= $this->assertEqual($wrapper->$property->value(), $value, format_string('The %property property has the correct value.', ['%property' => $property]));
      }
    }

    return $this->assertTrue($result, $message ?: format_string('The @entity contains the given values.', ['@entity' => $entity_type]), $group);
  }

  /**
   * Check if the given form fields are indicating that validation failed.
   *
   * This checks for the presence of the 'error' class on the field.
   *
   * @param array $fields
   *   An indexed array of field names that should be checked.
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertFieldValidationFailed(array $fields, $message = '', $group = 'Other') {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $result = TRUE;
    foreach ($fields as $field) {
      $xpath = '//textarea[@name=:value and contains(@class, "error")]|//input[@name=:value and contains(@class, "error")]|//select[@name=:value and contains(@class, "error")]';
      $elements = $this->xpath($this->buildXPathQuery($xpath, [':value' => $field]));
      $result &= $this->assertTrue($elements, format_string('The field %field has the "error" class.', ['%field' => $field]));
    }
    return $this->assertTrue($result, $message ?: 'All fields are indicating that validation failed.', $group);
  }

  /**
   * Check if no pager is present on the page.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertNoPager($message = '', $group = 'Other') {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $message = $message ?: 'No pager is present on the page.';
    $xpath = '//div[@class = "item-list"]/ul[@class = "pager"]';
    return $this->assertXpathElements($xpath, 0, [], $message, $group);
  }

  /**
   * Check if a pager is present on the page.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertPager($message = '', $group = 'Other') {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $message = $message ?: 'A pager is present on the page.';
    $xpath = '//div[@class = "item-list"]/ul[@class = "pager"]';
    return $this->assertXpathElements($xpath, 1, [], $message, $group);
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
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertStatusMessages(array $messages, $message = '', $group = 'Other') {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    // Messages can contain a mix of HTML and sanitized HTML, for example:
    // '<em class="placeholder">&lt;script&gt;alert();&lt;&#039;script&gt;</em>'
    // Unfortunately, check_plain() and SimpleXML::asXml() encode quotes and
    // slashes differently. Work around this by doing the message type check
    // with decoded strings, but also check if the original encoded strings are
    // present in the raw HTML to avoid false negatives.
    $shown_messages = $this->decodeStatusMessages($this->getStatusMessages());
    $decoded_messages = $this->decodeStatusMessages($messages);

    $result = TRUE;
    foreach (['status', 'warning', 'error'] as $type) {
      $expected_messages = !empty($decoded_messages[$type]) ? $decoded_messages[$type] : [];

      // Loop over the messages that are shown and match them against the
      // expected messages.
      foreach ($shown_messages[$type] as $shown_message) {
        $key = array_search($shown_message, $expected_messages);

        // If the message is not one of the expected messages, fail.
        if ($key === FALSE) {
          $result &= $this->fail(format_string('Unexpected @type message: @message', ['@type' => $type, '@message' => $shown_message]));
        }

        // Mark found messages as passed and remove them from the list.
        else {
          $this->pass(format_string('Found @type message: @message', ['@type' => $type, '@message' => $shown_message]));
          unset($expected_messages[$key]);
        }
      }
      // Throw fails for all expected messages that are not shown.
      foreach ($expected_messages as $expected_message) {
        $result &= $this->fail(format_string('Did not find @type message: @message', ['@type' => $type, '@message' => $expected_message]));
      }
    }

    // Also check if the correctly encoded messages are present in the raw HTML.
    // The above asserts do not detect if all HTML entities are correctly
    // encoded, and could let insecure status messages slip through as false
    // negatives.
    foreach ($messages as $type => $expected_messages) {
      foreach ($expected_messages as $expected_message) {
        $result &= $this->assertRaw($expected_message, format_string('Found correctly encoded message in raw HTML: @message', ['@message' => $expected_message]));
      }
    }

    return $this->assertTrue($result, $message ?: 'The correct messages are shown.', $group);
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
   *     'field_client_email[und][0][email]' => t('Email address'),
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
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertRequiredFieldMessages(array $required_fields, $messages = [], $message = '', $group = 'Other') {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $success = TRUE;

    // Use the standard message of the Field module by default.
    if (!$messages) {
      foreach ($required_fields as $required_field) {
        $messages['error'][] = t('!name field is required.', ['!name' => $required_field]);
      }
    }
    $success &= $this->assertFieldValidationFailed(array_keys($required_fields));
    return $success &= $this->assertStatusMessages($messages, $message ?: 'The error messages about required fields are present.', $group);
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
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   *
   * @return bool
   *   TRUE if the assertion succeeded, FALSE otherwise.
   */
  function assertXpathElements($xpath, $count, array $arguments = [], $message = '', $group = 'Other') {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    // Provide a default message.
    $message = $message ?: format_plural($count, 'The element matching the XPath expression is present in the page.', 'The @count elements matching the XPath expression are present in the page.');

    $elements = $this->xpath($xpath, $arguments);
    return $this->assertEqual(count($elements), $count, $message, $group);
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
  function decodeStatusMessages(array $messages) {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
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
  function getStatusMessages() {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $return = [
      'error' => [],
      'warning' => [],
      'status' => [],
    ];

    foreach (array_keys($return) as $type) {
      // Retrieve the entire messages container.
      if ($messages = $this->xpath('//div[contains(@class, "messages") and contains(@class, :type)]', [':type' => $type])) {
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

        // Load the messges HTML using UTF-8 encoding.
        @$dom->loadHTML('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>' . $messages[0]->asXml() . '</body></html>');
        // Strip the accessibility element.
        $accessibility_message = $dom->getElementsByTagName('h2')->item(0);
        $accessibility_message->parentNode->removeChild($accessibility_message);

        // We have valid XML now, so we can use XPath to find the messages. If
        // there are multiple messages, they are output in an unordered list. A
        // single message is output directly in the <div> container.
        $xpath = new \DOMXPath($dom);
        $elements = $xpath->query('//body/div/ul/li');
        if (!$elements->length) {
          $elements = $xpath->query('//body/div');
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
  function randomAddressField() {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    // The Address Field module trims all input and converts double spaces to
    // single spaces before saving the values to the database. We make sure our
    // random data does the same so we do not get random failures.
    // @see addressfield_field_presave()
    return [
      'country' => chr(mt_rand(65, 90)) . chr(mt_rand(65, 90)),
      'locality' => trim(str_replace('  ', ' ', $this->randomString())),
      'postal_code' => (string) rand(1000, 9999),
      'thoroughfare' => trim(str_replace('  ', ' ', $this->randomString())),
    ];
  }

  /**
   * Returns a random email address.
   *
   * @return string
   *   A random email address.
   */
  function randomEmail() {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    return strtolower($this->randomName()) . '@example.com';
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
  public static function randomPhoneNumber($countrycode = 'BE') {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $matches = NULL;
    do {
      $number = rand(10000000, 89000000);
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
  public function randomPhoneNumberField($countrycode = 'BE') {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    return [
      'number' => $this->randomPhoneNumber($countrycode),
      'countrycode' => $countrycode,
    ];
  }

  /**
   * {@inheritdoc}
   *
   * Like DrupalTestCase::randomString(), but includes additional special
   * characters, and starts with a space. This helps in discovering security
   * problems and improper trimming and encoding of strings.
   *
   * By default it returns 16 characters rather than the usual 8 to make up for
   * lost entropy.
   */
  static function randomString($length = 16) {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $str = ' &\/;<>\'"ä☢';
    for ($i = 0; $i < $length; $i++) {
      $str .= chr(mt_rand(32, 126));
    }
    return $str;
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
  public function entityReferenceFieldValue($name, $id) {
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
   * @see DrupalWebTestCase::randomString()
   */
  public static function randomName($length = 8) {
    throw new \Exception('Convert ' . __METHOD__ . ' to D8.');
    $values = array_merge(range(65, 90), range(97, 122), range(48, 57));
    $max = count($values) - 1;
    $str = chr(mt_rand(97, 122));
    for ($i = 1; $i < $length; $i++) {
      $str .= chr($values[mt_rand(0, $max)]);
    }
    return $str;
  }

}
