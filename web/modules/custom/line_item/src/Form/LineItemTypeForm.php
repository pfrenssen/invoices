<?php

namespace Drupal\line_item\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class LineItemTypeForm.
 *
 * @package Drupal\line_item\Form
 */
class LineItemTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $line_item_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $line_item_type->label(),
      '#description' => $this->t("Label for the Line item type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $line_item_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\line_item\Entity\LineItemType::load',
      ],
      '#disabled' => !$line_item_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $line_item_type = $this->entity;
    $status = $line_item_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Line item type.', [
          '%label' => $line_item_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Line item type.', [
          '%label' => $line_item_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($line_item_type->toUrl('collection'));
  }

}
