<?php

namespace Drupal\line_item\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class LineItemSettingsForm.
 *
 * @package Drupal\line_item\Form
 *
 * @ingroup line_item
 */
class LineItemSettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    throw new \Exception(__METHOD__ . ' is generated');
    return 'LineItem_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    throw new \Exception(__METHOD__ . ' is generated');
    // Empty implementation of the abstract submit class.
  }

  /**
   * Defines the settings form for Line item entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    throw new \Exception(__METHOD__ . ' is generated');
    $form['LineItem_settings']['#markup'] = 'Settings form for Line item entities. Manage field settings here.';
    return $form;
  }

}
