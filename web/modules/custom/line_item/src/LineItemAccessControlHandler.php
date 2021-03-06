<?php

declare (strict_types = 1);

namespace Drupal\line_item;

use Drupal\business\BusinessManagerInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Access controller for the Line item entity.
 *
 * @see \Drupal\line_item\Entity\LineItem.
 */
class LineItemAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /**
   * The business manager service.
   *
   * @var \Drupal\business\BusinessManagerInterface
   */
  protected $businessManager;

  /**
   * Constructs a LineItemAccessControlHandler object.
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
    /** @var \Drupal\line_item\Entity\LineItemInterface $entity */
    switch ($operation) {
      case 'view':
        // Access is granted if the line item is owned by the user, and the user
        // has the 'view own line items' permission.
        if (in_array($entity->getBusiness()->id(), $this->businessManager->getBusinessIdsByUser($account))) {
          return AccessResult::allowedIfHasPermission($account, 'view own line items')->cachePerUser();
        }

        return AccessResult::forbidden();

      case 'update':
      case 'delete':
        // Administrators have access.
        if ($account->hasPermission('administer line items')) {
          return AccessResult::allowed()->cachePerPermissions();
        }

        // Access is granted if the line item is owned by the user, and the user
        // has the 'administer own line items' permission.
        if (in_array($entity->getBusiness()->id(), $this->businessManager->getBusinessIdsByUser($account))) {
          return AccessResult::allowedIfHasPermission($account, 'administer own line items')->cachePerUser();
        }

        return AccessResult::forbidden();
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'create new line items');
  }

}
