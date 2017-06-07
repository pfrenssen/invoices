<?php

declare (strict_types = 1);

namespace Drupal\line_item\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\business\BusinessOwnedTrait;

/**
 * Defines the Tax rate entity.
 *
 * @ingroup line_item
 *
 * @ContentEntityType(
 *   id = "tax_rate",
 *   label = @Translation("Tax rate"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\line_item\TaxRateListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\line_item\Form\TaxRateForm",
 *       "add" = "Drupal\line_item\Form\TaxRateForm",
 *       "edit" = "Drupal\line_item\Form\TaxRateForm",
 *       "delete" = "Drupal\line_item\Form\TaxRateDeleteForm",
 *     },
 *     "access" = "Drupal\line_item\TaxRateAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\line_item\TaxRateHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "tax_rate",
 *   admin_permission = "administer tax rates",
 *   entity_keys = {
 *     "id" = "tid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/tax_rate/{tax_rate}",
 *     "add-form" = "/settings/tax-rates/add",
 *     "edit-form" = "/admin/structure/tax_rate/{tax_rate}/edit",
 *     "delete-form" = "/admin/structure/tax_rate/{tax_rate}/delete",
 *     "collection" = "/admin/structure/tax_rate",
 *   },
 *   field_ui_base_route = "tax_rate.settings"
 * )
 */
class TaxRate extends ContentEntityBase implements TaxRateInterface {

  use BusinessOwnedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);

    // Default to the active business of the logged in user.
    /** @var \Drupal\business\BusinessManagerInterface $business_manager */
    $business_manager = \Drupal::service('business.manager');

    $values += [
      'business' => $business_manager->getActiveBusinessId(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) : void {
    parent::preSave($storage);
    if (!$this->getBusinessId()) {
      throw new \Exception('Can not save a tax rate which is not associated with a business.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function id() : int {
    return (int) parent::id();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() : string {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName(string $name) : TaxRateInterface {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRate() : string {
    return $this->get('rate')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRate(string $rate) : TaxRateInterface {
    $this->set('rate', $rate);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['business'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Business'))
      ->setDescription(t('The business that owns the tax rate.'))
      ->setRevisionable(FALSE)
      ->setSettings([
        'target_type' => 'business',
        'handler' => 'default',
      ])
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the tax rate.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['rate'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Rate'))
      ->setDescription(t('The actual tax rate.'))
      ->setSettings([
        'precision' => 5,
        'scale' => 2,
      ])
      ->setDefaultValue('0.00')
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
