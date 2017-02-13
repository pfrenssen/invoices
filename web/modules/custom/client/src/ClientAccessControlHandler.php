<?php

declare (strict_types = 1);

namespace Drupal\client;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Client entity.
 *
 * @see \Drupal\client\Entity\Client.
 */
class ClientAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) : AccessResult {
    // Administrators have access to all operations.
    if ($account->hasPermission('administer clients')) {
      return AccessResult::allowed();
    }

    /** @var \Drupal\client\Entity\ClientInterface $entity */
    switch ($operation) {
      case 'view':
        throw new \Exception("Convert $operation permission to D8");

        return AccessResult::allowed();

        $access = user_access("view any $client->type client", $account);
        $access |= user_access('view own clients', $account) && client_is_owned_by_user($client, $account);
        return $access;

      case 'update':
        throw new \Exception("Convert $operation permission to D8");

        return AccessResult::allowedIfHasPermission($account, 'edit client entities');

        $access = user_access("edit any $client->type client", $account);
        $access |= user_access('administer own clients', $account) && client_is_owned_by_user($client, $account);
        return $access;

      case 'delete':
        throw new \Exception("Convert $operation permission to D8");

        return AccessResult::allowedIfHasPermission($account, 'delete client entities');

        // Clients may only be deleted if they are not used in invoices.
        $access = user_access("edit any $client->type client", $account);
        $access |= user_access('administer own clients', $account) && client_is_owned_by_user($client, $account);
        $access &= !client_has_invoices($client);
        return $access;
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) : AccessResult {
    return AccessResult::allowedIfHasPermission($account, 'create new clients');
  }

}
