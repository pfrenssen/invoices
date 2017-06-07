<?php

namespace Drupal\line_item;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Line item entities.
 *
 * @ingroup line_item
 */
class LineItemListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    throw new \Exception(__METHOD__ . ' is generated');
    $header['id'] = $this->t('Line item ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    throw new \Exception(__METHOD__ . ' is generated');
    /* @var $entity \Drupal\line_item\Entity\LineItem */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url('entity.line_item.edit_form', ['line_item' => $entity->id()])
    );
    return $row + parent::buildRow($entity);
  }

}
