<?php

declare (strict_types = 1);

namespace Drupal\client\Entity;

use Drupal\business\Entity\Business;
use Drupal\business\Entity\BusinessInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\libphonenumber\LibPhoneNumberInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Client entity.
 *
 * @ingroup client
 *
 * @ContentEntityType(
 *   id = "client",
 *   label = @Translation("Client"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\client\ClientListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\client\ClientTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\client\Form\ClientForm",
 *       "add" = "Drupal\client\Form\ClientForm",
 *       "edit" = "Drupal\client\Form\ClientForm",
 *       "delete" = "Drupal\client\Form\ClientDeleteForm",
 *     },
 *     "access" = "Drupal\client\ClientAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\client\ClientHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "client",
 *   data_table = "client_field_data",
 *   revision_table = "client_revision",
 *   revision_data_table = "client_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer client entities",
 *   entity_keys = {
 *     "id" = "cid",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/client/{client}",
 *     "add-form" = "/client/add",
 *     "edit-form" = "/client/{client}/edit",
 *     "delete-form" = "/client/{client}/delete",
 *     "collection" = "/clients",
 *   },
 *   field_ui_base_route = "client.settings"
 * )
 */
class Client extends RevisionableContentEntityBase implements ClientInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) : void {
    parent::preCreate($storage_controller, $values);

    /** @var \Drupal\business\BusinessManagerInterface $business_manager */
    $business_manager = \Drupal::service('business.manager');

    $values += [
      'uid' => \Drupal::currentUser()->id(),
      'business' => $business_manager->getActiveBusinessId(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) : void {
    parent::preSave($storage);
    if (!$this->getBusinessId()) {
      throw new \Exception('Can not save a client which is not associated with a business.');
    }

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the client owner the
    // revision author.
    if (!$this->getRevisionUserId()) {
      $this->setRevisionUserId($this->getOwnerId());
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
  public function setName(string $name) : ClientInterface {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBusinessId() : ?int {
    $business_id = $this->get('business')->target_id;
    return !empty($business_id) ? (int) $business_id : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getBusiness(): ?BusinessInterface {
    return Business::load($this->getBusinessId());
  }

  /**
   * {@inheritdoc}
   */
  public function setBusinessId(int $business_id) : ClientInterface {
    $this->set('business', $business_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setBusiness(BusinessInterface $business) : ClientInterface {
    return $this->setBusinessId($business->id());
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
  public function setCreatedTime(int $timestamp) : ClientInterface {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() : ?AccountInterface {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() : ?int {
    return (int) $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) : ClientInterface {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) : ClientInterface {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmail(): ?string {
    return $this->get('field_client_email')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getPhoneNumber(): ?LibPhoneNumberInterface {
    return $this->get('field_client_phone')->first();
  }

  /**
   * {@inheritdoc}
   */
  public function getWebsite(): ?Url {
    $uri = $this->get('field_client_website');
    return !empty($uri) ? Url::fromUri($uri) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) : array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Client entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
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
      ->setRequired(TRUE)
      ->setLabel(t('Client name'))
      ->setRevisionable(TRUE)
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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_timestamp'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
