<?php

declare (strict_types = 1);

namespace Drupal\client\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * Provides a form for deleting Client entities.
 *
 * @ingroup client
 */
class ClientDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %label?', [
      '%label' => $this->getEntity()->label(),
    ]);
  }

}
