<?php

declare (strict_types = 1);

namespace Drupal\business\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the Business entity.
 *
 * @ingroup business
 *
 * @ContentEntityType(
 *   id = "business",
 *   label = @Translation("Business"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\business\BusinessListBuilder",
 *     "views_data" = "Drupal\business\Entity\BusinessViewsData",
 *     "translation" = "Drupal\business\BusinessTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\business\Form\BusinessForm",
 *       "add" = "Drupal\business\Form\BusinessForm",
 *       "edit" = "Drupal\business\Form\BusinessForm",
 *       "delete" = "Drupal\business\Form\BusinessDeleteForm",
 *     },
 *     "access" = "Drupal\business\BusinessAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\business\BusinessHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "business",
 *   data_table = "business_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer business entities",
 *   entity_keys = {
 *     "id" = "bid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/business/{business}",
 *     "add-form" = "/business/add",
 *     "edit-form" = "/business/{business}/edit",
 *     "delete-form" = "/business/{business}/delete",
 *     "collection" = "/businesses",
 *   },
 *   field_ui_base_route = "business.settings"
 * )
 */
class Business extends ContentEntityBase implements BusinessInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
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
  public function setName(string $name) : BusinessInterface {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() : int {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime(int $timestamp) : BusinessInterface {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setLabel(t('Business name'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
