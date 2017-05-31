<?php

declare (strict_types = 1);

namespace Drupal\line_item\Tests;

use Drupal\line_item\Entity\LineItem;
use Drupal\line_item\Entity\LineItemInterface;

/**
 * Reusable test methods for testing line items.
 */
trait LineItemTestHelper {

  /**
   * Checks if the properties of the given line item match the given values.
   *
   * @param \Drupal\line_item\Entity\LineItemInterface $line_item
   *   The line item entity to check.
   * @param array $values
   *   An associative array of values to check, keyed by property name.
   */
  function assertLineItemProperties(LineItemInterface $line_item, array $values) : void {
    if (isset($values['type'])) {
      unset($values['type']);
    }
    $this->assertEntityFieldValues($line_item, $values);
  }

  /**
   * Checks if the line item database table is empty.
   */
  function assertLineItemTableEmpty() : void {
    $result = (bool) $this->connection->select('line_item', 'li')
      ->fields('li')
      ->execute()
      ->fetchAll();
    $this->assertFalse($result);
  }

  /**
   * Checks if the line item database table is not empty.
   */
  function assertLineItemTableNotEmpty() : void {
    $result = (bool) $this->connection->select('line_item', 'li')
      ->fields('li')
      ->execute()
      ->fetchAll();
    $this->assertTrue($result);
  }

  /**
   * Returns a newly created line item entity without saving it.
   *
   * This is intended for unit tests. It will set a random business ID. If you
   * are doing a functionality test use $this->createUiLineItem() instead.
   *
   * @param string $type
   *   Optional line item type, either 'product' or 'service'.
   * @param array $values
   *   An optional associative array of values, keyed by property name. Random
   *   values will be applied to all omitted properties.
   *
   * @return \Drupal\line_item\Entity\LineItemInterface
   *   A new line item entity.
   */
  function createLineItem(string $type = NULL, array $values = []) : LineItemInterface {
    // Provide some default values.
    $values += $this->randomLineItemValues($type);
    $line_item = LineItem::create(['type' => $values['type']]);
    $this->updateEntity($line_item, $values);

    return $line_item;
  }

  /**
   * Returns random values for all properties on the line item entity.
   *
   * @param string $type
   *   Optional line item type. If omitted a random line item type will be used.
   *
   * @returns array
   *   An associative array of random values, keyed by property name.
   */
  function randomLineItemValues(string $type = NULL) : array {
    $type = $type ?: $this->randomLineItemType();

    $values = [
      'business' => $this->randomBusiness()->id(),
      'field_line_item_description' => $this->randomString(),
      'field_line_item_discount' => $this->randomDecimal(),
      'field_line_item_quantity' => $this->randomDecimal(),
      'field_line_item_tax' => $this->randomDecimal(),
      'field_line_item_unit_cost' => $this->randomDecimal(),
      'type' => $type,
    ];

    if ($type == 'service') {
      $values['field_line_item_time_unit'] = array_rand([
        'minutes' => 'minutes',
        'hours' => 'hours',
        'days' => 'days',
        'weeks' => 'weeks',
        'months' => 'months',
        'years' => 'years',
      ]);
    }

    return $values;
  }

  /**
   * Generates the type for the line item.
   *
   * @return string
   *   The line item type.
   */
  function randomLineItemType() : string {
    return array_rand($this->getLineItemTypes());
  }

  /**
   * Returns the supported line item types.
   *
   * @return array
   *   An associative array of line item names, keyed by bundle name.
   */
  public function getLineItemTypes() : array {
    return [
      'product' => t('Product'),
      'service' => t('Service'),
    ];
  }

  /**
   * Updates the given line item with the given properties.
   *
   * @param \Drupal\line_item\Entity\LineItemInterface $line_item
   *   The line item entity to update.
   * @param array $values
   *   An associative array of values to apply to the entity, keyed by property
   *   name.
   *
   * @deprecated
   *   Use BaseTestHelper::updateEntity() instead.
   */
  function updateLineItem(LineItemInterface $line_item, array $values) {
    throw new \Exception(__METHOD__ . ' is deprecated.');
    unset($values['type']);
    $wrapper = entity_metadata_wrapper('line_item', $line_item);
    foreach ($values as $property => $value) {
      $wrapper->$property->set($value);
    }
  }

  /**
   * Returns a random line item from the database.
   *
   * @param string $type
   *   Optional line item type. Either 'product' or 'service'.
   *
   * @return \Drupal\line_item\Entity\LineItemInterface
   *   A random line item.
   */
  function randomLineItem(string $type = NULL) : LineItemInterface {
    $query = $this->connection->select('line_item', 'li')
      ->fields('li', ['lid'])
      ->orderRandom()
      ->range(0, 1);

    if ($type) {
      $query->condition('type', $type);
    }

    $lid = $query->execute()->fetchColumn();

    return LineItem::load($lid);
  }

  /**
   * Returns the unchanged, i.e. not modified, line item from the database.
   *
   * @param int $id
   *   The ID of the line item to return.
   *
   * @return \Drupal\line_item\Entity\LineItemInterface
   *   The line item.
   */
  function loadUnchangedLineItem(int $id) : LineItemInterface {
    return $this->loadUnchangedEntity('line_item', $id);
  }

}
