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
 * Access controller for the Tax rate entity.
 *
 * @see \Drupal\line_item\Entity\TaxRate.
 */
class TaxRateAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /**
   * The business manager service.
   *
   * @var \Drupal\business\BusinessManagerInterface
   */
  protected $businessManager;

  /**
   * Constructs a TaxRateAccessControlHandler object.
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
    /** @var \Drupal\line_item\Entity\TaxRateInterface $entity */
    switch ($operation) {
      case 'view':
        // Access is granted if the tax rate is owned by the user, and the user
        // has the 'view own tax rates' permission.
        if (in_array($entity->getBusiness()->id(), $this->businessManager->getBusinessIdsByUser($account))) {
          return AccessResult::allowedIfHasPermission($account, 'view own tax rates')->cachePerUser();
        }

        return AccessResult::forbidden();

      case 'update':
      case 'delete':
        // Access is granted if the tax rate is owned by the user, and the user
        // has the 'administer own tax rates' permission.
        if (in_array($entity->getBusiness()->id(), $this->businessManager->getBusinessIdsByUser($account))) {
          return AccessResult::allowedIfHasPermission($account, 'administer own tax rates')->cachePerUser();
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
    return AccessResult::allowedIfHasPermission($account, 'create new tax rates');
  }

}
