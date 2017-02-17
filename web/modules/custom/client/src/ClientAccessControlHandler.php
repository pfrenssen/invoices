<?php

declare (strict_types = 1);

namespace Drupal\client;

use Drupal\business\BusinessManagerInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Access controller for the Client entity.
 *
 * @see \Drupal\client\Entity\Client.
 */
class ClientAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /**
   * The client manager service.
   *
   * @var \Drupal\client\ClientManagerInterface
   */
  protected $clientManager;

  /**
   * The business manager service.
   *
   * @var \Drupal\business\BusinessManagerInterface
   */
  protected $businessManager;

  /**
   * Constructs a ClientAccessControlHandler object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\client\ClientManagerInterface $client_manager
   *   The client manager.
   * @param \Drupal\business\BusinessManagerInterface $business_manager
   *   The business manager.
   */
  public function __construct(EntityTypeInterface $entity_type, ClientManagerInterface $client_manager, BusinessManagerInterface $business_manager) {
    parent::__construct($entity_type);
    $this->clientManager = $client_manager;
    $this->businessManager = $business_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('client.manager'),
      $container->get('business.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) : AccessResult {
    /** @var \Drupal\client\Entity\ClientInterface $entity */
    // Administrators have access to all operations.
    if ($account->hasPermission('administer clients')) {
      return AccessResult::allowed();
    }

    switch ($operation) {
      case 'view':
        throw new \Exception("Convert $operation permission to D8");

        return AccessResult::allowed();

        $access = user_access("view any $client->type client", $account);
        $access |= user_access('view own clients', $account) && client_is_owned_by_user($client, $account);
        return $access;

      case 'update':
        // Access is granted if the client is owned by the user, and the user
        // has the 'administer own clients' permission.
        if (in_array($entity->getBusiness()->id(), $this->businessManager->getBusinessIdsByUser($account))) {
          return AccessResult::allowedIfHasPermission($account, 'administer own clients');
        }

        return AccessResult::forbidden();

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
