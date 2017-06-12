<?php

declare (strict_types = 1);

namespace Drupal\client;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\business\BusinessManagerInterface;
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
      return AccessResult::allowed()->cachePerPermissions();
    }

    switch ($operation) {
      case 'view':
        // Access is granted if the client is owned by the user, and the user
        // has the 'view own clients' permission.
        if (in_array($entity->getBusiness()->id(), $this->businessManager->getBusinessIdsByUser($account))) {
          return AccessResult::allowedIfHasPermission($account, 'view own clients')->cachePerUser();
        }

        return AccessResult::forbidden();

      case 'update':
        // Access is granted if the client is owned by the user, and the user
        // has the 'administer own clients' permission.
        if (in_array($entity->getBusiness()->id(), $this->businessManager->getBusinessIdsByUser($account))) {
          return AccessResult::allowedIfHasPermission($account, 'administer own clients')->cachePerUser();
        }

        return AccessResult::forbidden();

      case 'delete':
        // Clients may only be deleted if they are not used in invoices.
        // @todo Uncomment when we have converted invoices.
        // if ($this->clientManager->clientHasInvoices($entity)) {
        //   return AccessResult::forbidden();
        // }

        // Access is granted if the client is owned by the user, and the user
        // has the 'administer own clients' permission.
        if (in_array($entity->getBusiness()->id(), $this->businessManager->getBusinessIdsByUser($account))) {
          return AccessResult::allowedIfHasPermission($account, 'administer own clients')->cachePerUser();
        }

        return AccessResult::forbidden();
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
