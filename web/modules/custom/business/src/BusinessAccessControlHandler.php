<?php

declare (strict_types = 1);

namespace Drupal\business;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Business entity.
 *
 * @see \Drupal\business\Entity\Business.
 */
class BusinessAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    throw new \Exception(__METHOD__ . ' is generated');
    /** @var \Drupal\business\Entity\BusinessInterface $entity */
    // @todo Update with actual permissions from business.permissions.yml.
    switch ($operation) {
      case 'view':
        return AccessResult::allowed();

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit business entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete business entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    throw new \Exception(__METHOD__ . ' is generated');
    return AccessResult::allowedIfHasPermission($account, 'add business entities');
  }

}
