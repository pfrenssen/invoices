<?php

declare (strict_types = 1);

namespace Drupal\invoice\Tests;

use Drupal\client\Entity\ClientInterface;
use Drupal\line_item\Entity\LineItemInterface;

/**
 * Reusable test methods for testing invoices.
 */
trait InvoiceTestHelper {

  /**
   * Checks if the properties of the given invoice match the given values.
   *
   * @param \Invoice $invoice
   *   The Invoice entity to check.
   * @param array $values
   *   An associative array of values to check, keyed by property name.
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   */
  public function assertInvoiceProperties(InvoiceInterface $invoice, array $values, $message = '', $group = 'Other') : void {
    $this->assertEntityProperties('invoice', $invoice, $values, $message, $group);
  }

  /**
   * Checks if the invoice database table is empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   */
  public function assertInvoiceTableEmpty($message = '', $group = 'Other') : void {
    $result = (bool) db_select('invoice', 'i')->fields('i')->execute()->fetchAll();
    $this->assertFalse($result, $message ?: 'The invoice database table is empty.', $group);
  }

  /**
   * Checks if the invoice database table is not empty.
   *
   * @param string $message
   *   The message to display along with the assertion.
   * @param string $group
   *   The type of assertion - examples are "Browser", "PHP".
   */
  public function assertInvoiceTableNotEmpty($message = '', $group = 'Other') : void {
    $result = (bool) db_select('invoice', 'i')->fields('i')->execute()->fetchAll();
    $this->assertTrue($result, $message ?: 'The invoice database table is not empty.', $group);
  }

  /**
   * Returns a newly created invoice entity without saving it.
   *
   * This is intended for unit tests. It will not set a business ID. If you are
   * doing a functionality test use $this->createUiInvoice() instead.
   *
   * @param array $values
   *   An optional associative array of values, keyed by property name. Random
   *   values will be applied to all omitted properties.
   *
   * @return \Invoice
   *   A new invoice entity.
   */
  public function createInvoice(array $values = []) : InvoiceInterface {
    // Provide some default values.
    $values += $this->randomInvoiceValues();
    $invoice = invoice_create();
    $this->updateInvoice($invoice, $values);

    return $invoice;
  }

  /**
   * Creates a new invoice entity through the user interface.
   *
   * The saved invoice is retrieved by the invoice number.
   *
   * @param array $values
   *   An optional associative array of values, keyed by property name. Random
   *   values will be applied to all omitted properties.
   *
   * @return \Invoice
   *   A new invoice entity.
   */
  public function createUiInvoice(array $values = []) : InvoiceInterface {
    // Provide some default values.
    $values += $this->randomInvoiceValues();

    // Convert the entity property values to form values and submit the form.
    $edit = $this->convertInvoiceValuesToFormPostValues($values);

    // Click on the 'Add existing entity' button to display the autocomplete
    // field. We have to pass the name of the submit button but this contains a
    // hash, so we retrieve the button by ID first.
    // @todo If no client is passed, open the 'Add new entity' form instead and
    //   create a new client with random values on the fly.
    $this->drupalGet('invoice/add');
    $this->clickAddExistingClientButton();

    // Also click on 'Add existing entity' for products and services.
    $this->clickAddExistingProductButton();
    $this->clickAddExistingServiceButton();

    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Retrieve the saved invoice by invoice number and return it.
    $invoice = $this->loadInvoiceByNumber($values['field_invoice_number']);
    $this->assertTrue($invoice, 'Invoice was successfully created through the UI.');

    return $invoice;
  }

  /**
   * Returns random values for all properties on the invoice entity.
   *
   * @return array
   *   An associative array of random values, keyed by property name.
   */
  public function randomInvoiceValues() : array {
    $date_polarity = rand(0, 1) ? '-' : '+';
    $date_offset = rand(0, 7);

    return [
      'field_invoice_client' => $this->randomClient(),
      'field_invoice_date' => (string) strtotime($date_polarity . $date_offset . ' day 00:00'),
      'field_invoice_number' => $this->randomString(),
      'field_invoice_discount' => rand(0, 100) . '.00',
      'field_invoice_po_number' => $this->randomString(),
      'field_invoice_due_date' => $this->randomDueDate(),
      'field_invoice_terms' => $this->randomString(),
      'field_invoice_status' => $this->randomInvoiceStatus(),
      'field_invoice_products' => [$this->randomLineItem('product')],
      'field_invoice_services' => [$this->randomLineItem('service')],
    ];
  }

  /**
   * Returns random data for the basic invoice properties.
   *
   * These are values for the properties that are present on every invoice
   * entity regardless of the bundle type.
   *
   * This excludes the invoice ID ('iid') which is immutable.
   *
   * @return array
   *   An associative array of property values, keyed by property name.
   */
  public function randomInvoicePropertyValues() : array {
    return [
      'type' => $this->randomName(),
      'bid' => $this->randomBusiness()->id(),
      'created' => rand(0, 2000000000),
      'changed' => rand(0, 2000000000),
    ];
  }

  /**
   * Returns random field data for the fields in the invoice entity.
   *
   * @returns array
   *   An associative array of field data, keyed by field name.
   */
  public function randomInvoiceFieldValues() : array {
    $values = [];

    $date_polarity = rand(0, 1) ? '-' : '+';
    $date_offset = rand(0, 7);

    $client = $this->randomClient();
    $product = $this->randomLineItem('product');
    $service = $this->randomLineItem('service');

    $values['field_invoice_client'][LANGUAGE_NONE][0]['target_id'] = $client->id();
    $values['field_invoice_date'][LANGUAGE_NONE][0]['value'] = (string) strtotime($date_polarity . $date_offset . ' day 00:00');
    $values['field_invoice_number'][LANGUAGE_NONE][0]['value'] = $this->randomString();
    // Round the discount and format it to use two decimals so it matches the
    // expected format.
    $values['field_invoice_discount'][LANGUAGE_NONE][0]['value'] = number_format(round(mt_rand(0, 9999) / 100, 2), 2);
    $values['field_invoice_po_number'][LANGUAGE_NONE][0]['value'] = $this->randomString();
    $values['field_invoice_due_date'][LANGUAGE_NONE][0]['value'] = (string) $this->randomDueDate();
    $values['field_invoice_terms'][LANGUAGE_NONE][0]['value'] = $this->randomString();
    $values['field_invoice_status'][LANGUAGE_NONE][0]['value'] = $this->randomInvoiceStatus();
    $values['field_invoice_products'][LANGUAGE_NONE][0]['target_id'] = $product->id();
    $values['field_invoice_services'][LANGUAGE_NONE][0]['target_id'] = $service->id();

    return $values;
  }

  /**
   * Returns a random due date.
   *
   * @return int
   *   The key of one of the due date values.
   */
  public function randomDueDate() : int {
    return array_rand([
      '0' => 'due on receipt',
      '7' => '7 days',
      '15' => '15 days',
      '30' => '30 days',
      '60' => '60 days',
    ]);
  }

  /**
   * Returns a random invoice status.
   *
   * @return string
   *   Either 'draft' or 'sent'.
   */
  public function randomInvoiceStatus() : string {
    return array_rand([
      'draft' => 'draft',
      'sent' => 'sent',
      'paid' => 'paid',
    ]);
  }

  /**
   * Returns form post values from the given entity values.
   *
   * @param array $values
   *   An associative array of invoice values, keyed by property name, as
   *   returned by self::randomInvoiceValues(). For the entity references
   *   (the client, products and services), pass full Entity objects.
   *
   * @return array
   *   An associative array of values, keyed by form field name, as used by
   *   parent::drupalPostForm().
   *
   * @throws \Exception
   *   - Thrown if a value is given for field_invoice_client but this is not a
   *     Client object.
   *   - Thrown when the value of a referenced service or product is not a
   *     LineItem object.
   *   - Thrown one of the referenced line items does not have a supported
   *     bundle type.
   *
   * @see self::randomInvoiceValues()
   */
  public function convertInvoiceValuesToFormPostValues(array $values) : array {
    // Prepare the input for the client entity reference field.
    // @todo Support creating a new client as well as referencing an existing
    //   one.
    if (!empty($values['field_invoice_client'])) {
      if (!$values['field_invoice_client'] instanceof ClientInterface) {
        throw new \Exception('The value for field_invoice_client should be an instance of Client.');
      }
      $values['field_invoice_client'] = $this->entityReferenceFieldValue($values['field_invoice_client']->name, $values['field_invoice_client']->id());
    }

    // Prepare the input for the products and services entity reference fields.
    // @todo Support creating new line items as well as referencing existing
    //   ones.
    // @todo Support creating multiple items.
    $line_items = [];
    foreach (array_keys($this->getLineItemTypes()) as $type) {
      if (!empty($values["field_invoice_${type}s"])) {
        /* @var $line_item \Drupal\line_item\Entity\LineItemInterface */
        $line_item = reset($values["field_invoice_${type}s"]);
        if (!$line_item instanceof LineItemInterface) {
          throw new \Exception("The values for field_invoice_${type}s should be instances of LineItem.");
        }
        if ($line_item->bundle() != $type) {
          throw new \Exception("The values for field_invoice_${type}s should have the '$type' bundle.");
        }
        $line_items[$type] = $this->entityReferenceFieldValue($line_item->id(), $line_item->id());
      }
    }

    return [
      'field_invoice_client[und][form][entity_id]' => $values['field_invoice_client'],
      'field_invoice_number[und][0][value]' => $values['field_invoice_number'],
      'field_invoice_date[und][0][value][month]' => date('n', $values['field_invoice_date']),
      'field_invoice_date[und][0][value][day]' => date('j', $values['field_invoice_date']),
      'field_invoice_date[und][0][value][year]' => date('Y', $values['field_invoice_date']),
      'field_invoice_discount[und][0][value]' => $values['field_invoice_discount'],
      'field_invoice_po_number[und][0][value]' => $values['field_invoice_po_number'],
      'field_invoice_due_date[und]' => $values['field_invoice_due_date'],
      'field_invoice_terms[und][0][value]' => $values['field_invoice_terms'],
      'field_invoice_status[und]' => $values['field_invoice_status'],
      'field_invoice_products[und][form][entity_id]' => !empty($line_items['product']) ? $line_items['product'] : '',
      'field_invoice_services[und][form][entity_id]' => !empty($line_items['service']) ? $line_items['service'] : '',
    ];
  }

  /**
   * Returns the invoice that corresponds with the given invoice number.
   *
   * @param string $invoice_number
   *   The invoice number.
   * @param bool $reset
   *   Whether to reset the static cache.
   *
   * @return \Invoice
   *   The desired invoice.
   */
  public function loadInvoiceByNumber($invoice_number, $reset = FALSE) : InvoiceInterface {
    $query = new \EntityFieldQuery();
    $query
      ->entityCondition('entity_type', 'invoice')
      ->entityCondition('bundle', 'invoice')
      ->fieldCondition('field_invoice_number', 'value', $invoice_number)
      ->range(0, 1);
    $result = $query->execute();
    $iids = array_keys($result['invoice']);

    return invoice_load($iids[0], $reset);
  }

  /**
   * Updates the given invoice with the given properties.
   *
   * @param \Invoice $invoice
   *   The invoice entity to update.
   * @param array $values
   *   An associative array of values to apply to the entity, keyed by property
   *   name.
   */
  public function updateInvoice(\Invoice $invoice, array $values) : void {
    $wrapper = entity_metadata_wrapper('invoice', $invoice);
    foreach ($values as $property => $value) {
      $wrapper->$property->set($value);
    }
  }

  /**
   * Adds an existing client to an invoice through the user interface.
   *
   * This assumes the invoice form is already displayed. It will click the 'Add
   * existing client' button, add the client, and submit the inline form.
   *
   * @param \Client $client
   *   The client to add to the invoice. If omitted a random client will be
   *   used.
   */
  public function addExistingClientToUiInvoice(\Client $client = NULL) : void {
    // Default to a random client.
    $client = $client ?: $this->randomClient();

    $values = [
      'field_invoice_client[und][form][entity_id]' => $this->entityReferenceFieldValue($client->name, $client->id()),
    ];

    $this->clickAddExistingClientButton();
    $this->submitInlineAddExistingClientForm($values);
  }

  /**
   * Clicks the "Add new client" button.
   */
  public function clickAddNewClientButton() : void {
    $this->clickButtonWithId('edit-field-invoice-client-und-actions-ief-add');
  }

  /**
   * Clicks the "Add existing client" button.
   */
  public function clickAddExistingClientButton() : void {
    $this->clickButtonWithId('edit-field-invoice-client-und-actions-ief-add-existing');
  }

  /**
   * Submits an 'Add existing client' form displayed inline in the invoice.
   *
   * This assumes the form is already open, ie. the 'Add existing client' button
   * has already been pressed.
   *
   * @param array $edit
   *   The values to fill in in the form.
   */
  public function submitInlineAddExistingClientForm(array $edit) : void {
    $this->clickButtonWithId('edit-field-invoice-client-und-form-actions-ief-reference-save', $edit);
  }

  /**
   * Clicks the "Add new service" button.
   */
  public function clickAddNewServiceButton() : void {
    $this->clickButtonWithId('edit-field-invoice-services-und-actions-ief-add');
  }

  /**
   * Clicks the "Add existing service" button.
   */
  public function clickAddExistingServiceButton() : void {
    $this->clickButtonWithId('edit-field-invoice-services-und-actions-ief-add-existing');
  }

  /**
   * Submits a service form that is displayed inline in the invoice.
   *
   * This assumes the form is already open, ie. the 'Add new service' button has
   * already been pressed.
   *
   * @param array $edit
   *   The values to fill in in the form.
   */
  public function submitInlineServiceForm(array $edit) : void {
    $this->clickButtonWithId('edit-field-invoice-services-und-form-actions-ief-add-save', $edit);
  }

  /**
   * Clicks the "Add new product" button.
   */
  public function clickAddNewProductButton() : void {
    $this->clickButtonWithId('edit-field-invoice-products-und-actions-ief-add');
  }

  /**
   * Clicks the "Add existing product" button.
   */
  public function clickAddExistingProductButton() : void {
    $this->clickButtonWithId('edit-field-invoice-products-und-actions-ief-add-existing');
  }

  /**
   * Submits a product form that is displayed inline in the invoice.
   *
   * This assumes the form is already open, ie. the 'Add new product' button has
   * already been pressed.
   *
   * @param array $edit
   *   The values to fill in in the form.
   */
  public function submitInlineProductForm(array $edit) : void {
    $this->clickButtonWithId('edit-field-invoice-products-und-form-actions-ief-add-save', $edit);
  }

  /**
   * Clicks the button with the given CSS ID.
   *
   * This is needed for buttons generated by the Inline Entity Form module.
   * These buttons use a hash in their field name so they have to be identified
   * by ID.
   *
   * @param string $id
   *   The CSS ID of the button to click.
   * @param array $edit
   *   Optional array containing field values to pass on to drupalPostAJAX().
   */
  protected function clickButtonWithId($id, array $edit = []) : void {
    // The button name contains an unpredictable hash. Retrieve it by ID.
    $button_element = $this->xpath('//input[contains(@id, "' . $id . '")]');
    $this->drupalPostAJAX(NULL, $edit, (string) $button_element[0]['name']);
  }

}
