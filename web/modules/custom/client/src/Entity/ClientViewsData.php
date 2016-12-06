<?php

declare (strict_types = 1);

namespace Drupal\client\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Client entities.
 */
class ClientViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['client']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Client'),
      'help' => $this->t('The Client ID.'),
    ];

    // Default to the client_field_data table. This will ensure we can select
    // the base fields without having to manually add a join to this table.
    $data['client_field_data']['table']['wizard_id'] = 'client';
    $data['client_field_data']['table']['base']['weight'] = -10;
    $data['client_field_data']['table']['base']['defaults']['field'] = 'cid';

    return $data;
  }

}
