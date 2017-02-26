<?php

declare (strict_types = 1);

namespace Drupal\client\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Client edit forms.
 *
 * @ingroup client
 */
class ClientForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $this->entity->setNewRevision();

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('New client %label has been added.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('The changes have been saved.'));
    }
    $form_state->setRedirect('entity.client.collection');
  }

}
