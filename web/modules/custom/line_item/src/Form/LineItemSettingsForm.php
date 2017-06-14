<?php

declare (strict_types = 1);

namespace Drupal\line_item\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for line item entities.
 *
 * This form doesn't have any fields. It is mainly used as the main page for
 * field UI, form mode and display mode settings.
 *
 * @ingroup line_item
 */
class LineItemSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'line_item_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description']['#markup'] = 'Settings form for Line item entities. Manage field settings here.';
    return $form;
  }

}
