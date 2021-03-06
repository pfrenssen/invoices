<?php

declare (strict_types = 1);

namespace Drupal\line_item\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Line item edit forms.
 *
 * @ingroup line_item
 */
class LineItemForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    throw new \Exception(__METHOD__ . ' is generated');
    /* @var $entity \Drupal\line_item\Entity\LineItem */
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    throw new \Exception(__METHOD__ . ' is generated');
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Line item.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Line item.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.line_item.canonical', ['line_item' => $entity->id()]);
  }

}
