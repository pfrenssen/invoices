<?php

declare (strict_types = 1);

namespace Drupal\business\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Business entities.
 */
class BusinessViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['business']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Business'),
      'help' => $this->t('The Business ID.'),
    ];

    // Default to the business_field_data table. This will ensure we can select
    // the base fields without having to manually add a join to this table.
    $data['business_field_data']['table']['wizard_id'] = 'business';
    $data['business_field_data']['table']['base']['weight'] = -10;
    $data['business_field_data']['table']['base']['defaults']['field'] = 'bid';

    return $data;
  }

}
