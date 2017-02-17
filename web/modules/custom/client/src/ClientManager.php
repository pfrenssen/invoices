<?php

declare (strict_types = 1);

namespace Drupal\client;

use Drupal\business\Entity\BusinessInterface;
use Drupal\client\Entity\Client;
use Drupal\client\Entity\ClientInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Helper methods for managing businesses.
 */
class ClientManager implements ClientManagerInterface {

  /**
   * Static cache of client IDs, keyed by business ID.
   *
   * @var int[][]
   */
  protected $cids = [];

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity storage handler for Client entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs the BusinessManager service.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user account.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->storage = $entity_type_manager->getStorage('client');
  }

  /**
   * {@inheritdoc}
   */
  public function isOwnedByBusiness(ClientInterface $client, BusinessInterface $business) : bool {
    return $client->getBusiness()->id() === $business->id();
  }

  /**
   * {@inheritdoc}
   */
  function clientHasInvoices(Client $client) : bool {
    return (bool) $this->getInvoiceIds($client);
  }

  /**
   * {@inheritdoc}
   */
  function getInvoices(Client $client) : array {
    return $this->entityTypeManager->getStorage('invoice')->loadMultiple($this->getInvoiceIds($client));
  }

  /**
   * {@inheritdoc}
   */
  function getInvoiceIds(Client $client) : array {
    $query = $this->entityTypeManager->getStorage('invoice')->getQuery();
    $query->condition('field_invoice_client', $client->id());
    $result = $query->execute();
    return isset($result['invoice']) ? array_keys($result['invoice']) : [];
  }

  /**
   * {@inheritdoc}
   */
  function getClientIdsByBusiness(BusinessInterface $business, bool $reset = FALSE) : array {
    if ($reset) {
      throw new \Exception('The $reset parameter is deprecated. Call ::resetCache() instead.');
    }

    if (empty($this->cids[$business->id()])) {
      $query = $this->storage->getQuery();
      $query->condition('business', $business->id());
      $result = $query->execute();

      $this->cids[$business->id()] = !empty($result['client']) ? array_keys($result['client']) : [];
    }

    return $this->cids[$business->id()];
  }

  /**
   * {@inheritdoc}
   */
  function getClientsByBusiness(BusinessInterface $business, bool $reset = FALSE) : array {
    $cids = $this->getClientIdsByBusiness($business, $reset);
    return $this->storage->loadMultiple($cids);
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache() {
    $this->cids = [];
  }

}
