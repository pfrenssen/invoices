<?php

declare (strict_types = 1);

namespace Drupal\business\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for business entities.
 *
 * This form doesn't have any fields. It is mainly used as the main page for
 * field UI, form mode and display mode settings.
 *
 * @ingroup business
 */
class BusinessSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'business_settings';
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
    $form['description']['#markup'] = 'Settings form for Business entities. Manage field settings here.';
    return $form;
  }

}
