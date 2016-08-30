<?php

namespace Drupal\business\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Business entities.
 */
class BusinessViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['business']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Business'),
      'help' => $this->t('The Business ID.'),
    );

    return $data;
  }

}
