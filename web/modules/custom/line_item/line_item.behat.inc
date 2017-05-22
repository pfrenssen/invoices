<?php

declare (strict_types = 1);

/**
 * @file
 * Contains \LineItemSubContext.
 */

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;

/**
 * Behat step definitions for testing tax rates.
 */
class LineItemSubContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  use Drupal\invoicing\Traits\BaseTestHelper;
  use Drupal\invoicing\Traits\BusinessTestHelper;
  use Drupal\invoicing\Traits\LineItemTestHelper;

  /**
   * Test line items.
   *
   * @var LineItem[]
   */
  public $lineItems = [];

  /**
   * Creates products with data provided in a table.
   *
   * Table format:
   * | description  | unit cost | quantity | tax   | discount |
   * | Taketsuru 21 | 137.50    | 1        | 21.00 | 0        |
   * | ...          | ...       | ...      | ...   | ...      |
   *
   * @param \Behat\Gherkin\Node\TableNode $products_table
   *   The product data.
   *
   * @Given product(s):
   */
  public function createProducts(TableNode $products_table) {
    $this->createLineItems('product', $products_table);
  }

  /**
   * Creates services with data provided in a table.
   *
   * Table format:
   * | description | unit cost | time unit | quantity | tax   | discount |
   * | Copywriting | 34.95     | hours     | 10       | 19.00 | 15       |
   * | ...         | ...       | ...       | ...      | ...   | ...      |
   *
   * All columns in the table are optional.
   *
   * @param \Behat\Gherkin\Node\TableNode $services_table
   *   The service data.
   *
   * @Given service(s):
   */
  public function createServices(TableNode $services_table) {
    $this->createLineItems('service', $services_table);
  }

  /**
   * Creates line item of the given type using the given table data.
   *
   * Any missing columns in the table will be filled with random data. The line
   * items are identified by their description in Behat tests, so make sure all
   * line items have a unique description.
   *
   * @param string $type
   *   The line item type, either 'product' or 'service'.
   * @param \Behat\Gherkin\Node\TableNode $table
   *   The table data.
   *
   * @throws \Exception
   *   Thrown when no user is logged in, or when the logged in user has no
   *   business, or when two line items with the same description are created.
   *
   * @Given service(s):
   */
  protected function createLineItems($type, TableNode $table) {
    if (!$this->getuser()->uid) {
      throw new \Exception('There is no logged in user to create line items for.');
    }

    $user = entity_metadata_wrapper('user', user_load($this->getUser()->uid));
    $businesses = $user->field_user_businesses->value();

    if (empty($businesses)) {
      throw new \Exception('Cannot create line items, the currently logged in user has no business.');
    }

    foreach ($table->getHash() as $table_hash) {
      $values = ['bid' => $businesses[0]->id()] + $this->convertTableRowToValues($table_hash);
      $line_item = $this->createLineItem($type, $values);
      line_item_save($line_item);
      $description = $line_item->wrapper()->get('field_line_item_description')->value();
      // Line items are identified by their descriptions in Behat test. Fail the
      // test when a second line item with the same description is created.
      if (isset($this->lineItems[$description])) {
        throw new \Exception('A line item with description ' . $description . ' has already been created.');
      }
      $this->lineItems[$description] = $line_item;
    }
  }

  /**
   * Converts table row data to property values for creating line items.
   *
   * @param array $row
   *   A row from a TableNode.
   *
   * @return array
   *   An array of property values.
   */
  protected function convertTableRowToValues(array $row) {
    $mapping = [
      'description' => 'field_line_item_description',
      'unit cost' => 'field_line_item_unit_cost',
      'time unit' => 'field_line_item_time_unit',
      'quantity' => 'field_line_item_quantity',
      'tax' => 'field_line_item_tax',
      'discount' => 'field_line_item_discount',
    ];

    $values = [];
    foreach ($mapping as $column_name => $property) {
      if (isset($row[$column_name])) {
        $values[$property] = $row[$column_name];
      }
    }

    return $values;
  }

  /**
   * Checks that the price calculation of a line item equals the expected total.
   *
   * @param string $description
   *   The description of the line item.
   * @param string $total
   *   The expected price total.
   *
   * @Then the price calculation of the :description line item should equal :total
   */
  public function assertLineItemPriceCalculation($description, $total) {
    $line_item = $this->getLineItem($description);
    PHPUnit_Framework_Assert::assertEquals($total, line_item_calculate($line_item)['total']);
  }

  /**
   * Remove any created line items.
   *
   * @AfterScenario
   */
  public function cleanLineItems() {
    // Remove any line items that were created.
    foreach ($this->lineItems as $line_item) {
      line_item_delete($line_item);
    }
  }

  /**
   * Returns the line item with the given description.
   *
   * @param string $description
   *   The description of the line item.
   *
   * @return \Drupal\line_item\Entity\LineItemInterface
   *   The line item.
   *
   * @throws \InvalidArgumentException
   *   Thrown when no line item with the given description exists.
   */
  public function getLineItem($description) {
    if (isset($this->lineItems[$description])) {
      return $this->lineItems[$description];
    }
    throw new \InvalidArgumentException('No line item with description ' . $description . ' exists.');
  }

}
