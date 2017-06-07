<?php

declare (strict_types = 1);

namespace Drupal\line_item\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;

/**
 * Provides a form for deleting Line item entities.
 *
 * @ingroup line_item
 */
class LineItemDeleteForm extends ContentEntityDeleteForm {

  public function __construct(\Drupal\Core\Entity\EntityManagerInterface $entity_manager, \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, \Drupal\Component\Datetime\TimeInterface $time = NULL) {
    throw new \Exception(__METHOD__ . ' is generated');
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
  }

}
