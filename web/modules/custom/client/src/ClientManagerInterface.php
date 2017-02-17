<?php

declare (strict_types = 1);

namespace Drupal\client;

use Drupal\business\Entity\BusinessInterface;
use Drupal\client\Entity\Client;
use Drupal\client\Entity\ClientInterface;

/**
 * Interface for ClientManager services.
 */
interface ClientManagerInterface {

  /**
   * Returns whether the given client is owned by the given business.
   *
   * @param \Drupal\client\Entity\ClientInterface $client
   *   The client to check.
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business to check.
   *
   * @return bool
   *   TRUE if the client is owned by the business, FALSE otherwise.
   */
  public function isOwnedByBusiness(ClientInterface $client, BusinessInterface $business) : bool;

  /**
   * Returns whether the client is used in any invoices.
   *
   * @param \Drupal\client\Entity\Client $client
   *   The client to check.
   *
   * @return bool
   *   TRUE if the client is used in invoices, FALSE otherwise.
   *
   * @todo Move to InvoiceManagerInterface.
   */
  function clientHasInvoices(Client $client) : bool;

  /**
   * Returns the invoices that are issued for the given client.
   *
   * @param \Drupal\client\Entity\Client $client
   *   The client for which to retrieve the invoices.
   *
   * @return \Drupal\invoice\Entity\Invoice[]
   *   An array of invoices.
   *
   * @todo Move to InvoiceManagerInterface.
   */
  function getInvoices(Client $client) : array;

  /**
   * Returns the IDs of the invoices that are issued for the given client.
   *
   * @param \Drupal\client\Entity\Client $client
   *   The client for which to retrieve the invoices.
   *
   * @return int[]
   *   An array of invoice IDs.
   *
   * @todo Move to InvoiceManagerInterface.
   */
  function getInvoiceIds(Client $client) : array;

  /**
   * Returns the client IDs for a given business.
   *
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business for which to retrieve the client IDs.
   * @param bool $reset
   *   Whether or not to reset the static cache. Defaults to FALSE. This
   *   parameter is deprecated. Call ::resetCache() instead.
   *
   * @return int[]
   *   An array with all the client IDs linked to the business. When no clients
   *   are found an empty array will be returned.
   */
  function getClientIdsByBusiness(BusinessInterface $business, bool $reset = FALSE) : array;

  /**
   * Returns the clients for a given business.
   *
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business for which to retrieve the clients.
   * @param bool $reset
   *   Whether or not to reset the static cache. Defaults to FALSE. This
   *   parameter is deprecated. Call ::resetCache() instead.
   *
   * @return \Drupal\client\Entity\ClientInterface[]
   *   An array with all the clients linked to the business. When no clients are
   *   found an empty array will be returned.
   */
  function getClientsByBusiness(BusinessInterface $business, bool $reset = FALSE) : array;

  /**
   * Resets the static cache.
   */
  public function resetCache();

}

