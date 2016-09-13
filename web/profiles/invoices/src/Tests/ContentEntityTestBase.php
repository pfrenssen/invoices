<?php

namespace Drupal\invoices\Tests;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Base class for tests on content entities.
 *
 * Converting an implementing class from D7:
 * - Remove ::getInfo().
 * - Rename ::getName() to ::getEntityTypeId().
 * - Rename ::getTypes() to ::getEntityBundleIds().
 * - Remove ::createEntityType().
 * - Remove ::createEntity().
 * - Rename ::updateEntityUsingMetadataWrapper() to ::updateEntity().
 * - Remove ::loadEntity().
 * - Remove ::getBasicPropertyValues().
 * - Rename ::getFieldValues() to ::getValues().
 * - Remove ::getEntityMetadataWrappers().
 * - Remove ::convertPropertiesToEntityMetadataWrappers().
 * - Remove ::updateEntity().
 */
abstract class ContentEntityTestBase extends EntityKernelTestBase {

  use BaseTestHelper;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the machine name of the entity under test.
   *
   * @return string
   *   The machine name of the entity under test.
   */
  abstract public function getEntityTypeId();

  /**
   * Returns the available bundles.
   *
   * @return array
   *   An array of entity type machine names.
   */
  abstract public function getEntityBundleIds();

  /**
   * Creates a new entity.
   *
   * @param array $values
   *   An optional array of field values to assign to the new entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The created entity.
   */
  public function createEntity(array $values = []) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $storage */
    $storage = $this->entityTypeManager->getStorage($this->getEntityTypeId());
    return $storage->create($values);
  }

  /**
   * Returns random field data for the fields in the given bundle.
   *
   * This is excluding the entity ID which is not writable.
   *
   * @param string $type
   *   The entity type for which to return the random data.
   *
   * @return array
   *   An associative array of field data, keyed by field name.
   */
  abstract protected function getValues($type);

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installEntitySchema($this->getEntityTypeId());
    $this->installConfig([$this->getEntityTypeId()]);

    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Tests all fields on the bundles that exist for the entity.
   */
  public function testBundles() {
    // Loop over the available bundles.
    foreach ($this->getEntityBundleIds() as $type) {
      // Check if all properties can be accessed on a freshly created entity.
      $values = $this->getValues($type);
      $entity = $this->createEntity($values);
      $this->assertEntityFieldValues($entity, $values);

      // Check if an empty entity can have its properties updated.
      $entity = $this->createEntity();
      $values = $this->getValues($type);
      $this->updateEntity($entity, $values);
      $this->assertEntityFieldValues($entity, $values);

      // Check if an existing entity can have its properties updated.
      $values = $this->getValues($type);
      $this->updateEntity($entity, $values);
      $this->assertEntityFieldValues($entity, $values);

      // Check if all properties can be accessed on a saved entity.
      $values = $this->getValues($type);
      $entity = $this->createEntity($values);
      $entity->save();
      $this->assertEntityFieldValues($entity, $values);

      // Check if all properties can be accessed on a reloaded entity.
      $this->reloadEntity($entity);
      $this->assertEntityFieldValues($entity, $values);
    }
  }

}
