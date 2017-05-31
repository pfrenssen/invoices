<?php

declare (strict_types = 1);

namespace Drupal\line_item\Entity;

use Drupal\business\BusinessOwnedTrait;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Line item entity.
 *
 * @ingroup line_item
 *
 * @ContentEntityType(
 *   id = "line_item",
 *   label = @Translation("Line item"),
 *   bundle_label = @Translation("Line item type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\line_item\LineItemListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\line_item\LineItemTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\line_item\Form\LineItemForm",
 *       "add" = "Drupal\line_item\Form\LineItemForm",
 *       "edit" = "Drupal\line_item\Form\LineItemForm",
 *       "delete" = "Drupal\line_item\Form\LineItemDeleteForm",
 *     },
 *     "access" = "Drupal\line_item\LineItemAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\line_item\LineItemHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "line_item",
 *   data_table = "line_item_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer line items",
 *   entity_keys = {
 *     "id" = "lid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/line-item/{line_item}",
 *     "add-form" = "/line-item/add",
 *     "edit-form" = "/line-item/{line_item}/edit",
 *     "delete-form" = "/line-item/{line_item}/delete",
 *     "collection" = "/line-items",
 *   },
 *   bundle_entity_type = "line_item_type",
 *   field_ui_base_route = "entity.line_item_type.edit_form"
 * )
 */
class LineItem extends ContentEntityBase implements LineItemInterface {

  use BusinessOwnedTrait;
  use EntityChangedTrait;

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
      throw new \Exception('Can not save a line item which is not associated with a business.');
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
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTaxRate() {
    throw new \Exception("Convert __FUNCTION__ to D8.");
    $tax_rate = !empty($this->field_line_item_tax[LANGUAGE_NONE][0]['value']) ? $this->field_line_item_tax[LANGUAGE_NONE][0]['value'] : '0.00';
    return bcdiv($tax_rate, 100, 8);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Description'))
      ->setDescription(t('Short description of the line item.'))
      ->setSettings(array(
        'max_length' => 60,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
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

    $fields['business'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Business'))
      ->setDescription(t('The business this client belongs to.'))
      ->setRevisionable(FALSE)
      ->setSetting('target_type', 'business')
      ->setSetting('handler', 'default')
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
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

    // @todo Add when the Invoice module is ported.
    // $fields['invoice'] = BaseFieldDefinition::create('entity_reference')
    //   ->setLabel(t('Invoice'))
    //   ->setDescription(t('Optional invoice ID to which this line item is linked. If this is NULL this line item is providing default values for a preset.'))
    //   ->setRevisionable(FALSE)
    //   ->setSetting('target_type', 'invoice')
    //   ->setSetting('handler', 'default')
    //   ->setRequired(FALSE)
    //   ->setTranslatable(TRUE)
    //   ->setDisplayOptions('view', [
    //     'label' => 'hidden',
    //     'weight' => 0,
    //   ])
    //   ->setDisplayOptions('form', [
    //     'type' => 'entity_reference_autocomplete',
    //     'weight' => 5,
    //     'settings' => [
    //       'match_operator' => 'CONTAINS',
    //       'size' => '60',
    //       'autocomplete_type' => 'tags',
    //       'placeholder' => '',
    //     ],
    //   ])
    //   ->setDisplayConfigurable('form', TRUE)
    //   ->setDisplayConfigurable('view', TRUE);

    // @todo Add when the preset IDs are ported.
    // 'pid' => [
    //   'description' => 'Optional preset ID that was used to construct this line item.',
    //   'type' => 'int',
    //   'unsigned' => TRUE,
    //   'not null' => FALSE,
    //   'default' => 0,
    // ],

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The Unix timestamp indicating when the line item was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The Unix timestamp indicating when the line item was last changed.'));

    return $fields;
  }

}
