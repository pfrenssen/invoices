<?php

namespace Drupal\line_item\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete Line item type entities.
 */
class LineItemTypeDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    throw new \Exception(__METHOD__ . ' is generated');
    return $this->t('Are you sure you want to delete %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    throw new \Exception(__METHOD__ . ' is generated');
    return new Url('entity.line_item_type.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    throw new \Exception(__METHOD__ . ' is generated');
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    throw new \Exception(__METHOD__ . ' is generated');
    $this->entity->delete();

    drupal_set_message(
      $this->t('content @type: deleted @label.',
        [
          '@type' => $this->entity->bundle(),
          '@label' => $this->entity->label(),
        ]
        )
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
