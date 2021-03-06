<?php

declare (strict_types = 1);

namespace Drupal\client\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for client entities.
 *
 * This form doesn't have any fields. It is mainly used as the main page for
 * field UI, form mode and display mode settings.
 *
 * @ingroup client
 */
class ClientSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'client_settings';
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
    $form['description']['#markup'] = 'Settings form for Client entities. Manage field settings here.';
    return $form;
  }

}
