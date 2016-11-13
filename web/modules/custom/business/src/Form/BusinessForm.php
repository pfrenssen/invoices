<?php

declare (strict_types = 1);

namespace Drupal\business\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Business edit forms.
 *
 * @ingroup business
 */
class BusinessForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created new business %label.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('The changes have been saved.'));
    }
    $form_state->setRedirect('entity.business.canonical', ['business' => $entity->id()]);
  }

}
