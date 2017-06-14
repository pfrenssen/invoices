<?php

declare (strict_types = 1);

namespace Drupal\line_item\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for tax rate entities.
 *
 * This form doesn't have any fields. It is mainly used as the main page for
 * field UI, form mode and display mode settings.
 *
 * @ingroup line_item
 */
class TaxRateSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tax_rate_settings';
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
    $form['description']['#markup'] = 'Settings form for Tax rate entities. Manage field settings here.';
    return $form;
  }

}
