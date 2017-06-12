<?php

declare (strict_types = 1);

namespace Drupal\business;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Access controller for the Business entity.
 *
 * @see \Drupal\business\Entity\Business.
 */
class BusinessAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /**
   * The business manager service.
   *
   * @var \Drupal\business\BusinessManagerInterface
   */
  protected $businessManager;

  /**
   * Constructs a BusinessAccessControlHandler object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\business\BusinessManagerInterface $business_manager
   *   The business manager.
   */
  public function __construct(EntityTypeInterface $entity_type, BusinessManagerInterface $business_manager) {
    parent::__construct($entity_type);
    $this->businessManager = $business_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('business.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\business\Entity\BusinessInterface $entity */

    // Administrators have access to all operations.
    if ($account->hasPermission('administer businesses')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    // Access is granted only if the business is owned by the user, and the user
    // has the relevant permission.
    $is_owned_by_user = in_array($entity->id(), $this->businessManager->getBusinessIdsByUser($account));

    if (!$is_owned_by_user) {
      return AccessResult::forbidden()->cachePerUser();
    }

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view own businesses')->cachePerUser();

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit own businesses')->cachePerUser();

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete own businesses')->cachePerUser();
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create new businesses');
  }

}
