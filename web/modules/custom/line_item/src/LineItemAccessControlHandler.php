<?php

namespace Drupal\line_item;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Line item entity.
 *
 * @see \Drupal\line_item\Entity\LineItem.
 */
class LineItemAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\line_item\Entity\LineItemInterface $entity */
    switch ($operation) {
      case 'view':
        // @todo Check if the user owns the line item.
        return AccessResult::allowedIfHasPermission($account, 'view own line items');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit line items');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete line items');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add line items');
  }

}
