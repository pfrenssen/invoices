<?php

declare (strict_types = 1);

namespace Drupal\client\Entity;

use Drupal\business\Entity\BusinessInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Url;
use Drupal\libphonenumber\LibPhoneNumberInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Client entities.
 *
 * @ingroup client
 */
interface ClientInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface {

  /**
   * Returns the client name.
   *
   * @return string
   *   The client name.
   */
  public function getName() : string;

  /**
   * Sets the client name.
   *
   * @param string $name
   *   The client name.
   *
   * @return \Drupal\client\Entity\ClientInterface
   *   The updated client.
   *
   * @todo type hint on self.
   */
  public function setName(string $name) : ClientInterface;

  /**
   * Returns the ID of the business this client belongs to.
   *
   * @return int|null
   *   The ID of the business this client belongs to, or NULL if no business is
   *   set yet.
   */
  public function getBusinessId() : ?int;

  /**
   * Returns the business this client belongs to.
   *
   * @return \Drupal\business\Entity\BusinessInterface|null
   *   The business this client belongs to, or NULL if no business is set yet.
   */
  public function getBusiness() : ?BusinessInterface;

  /**
   * Sets the business ID this client belongs to.
   *
   * @param int $business_id
   *   The ID of the business this client belongs to.
   *
   * @return self
   *   The updated client.
   */
  public function setBusinessId(int $business_id) : ClientInterface;

  /**
   * Sets the business this client belongs to.
   *
   * @param \Drupal\business\Entity\BusinessInterface $business
   *   The business this client belongs to.
   *
   * @return self
   *   The updated client.
   */
  public function setBusiness(BusinessInterface $business) : ClientInterface;

  /**
   * Returns the creation timestamp.
   *
   * @return int
   *   The creation timestamp.
   */
  public function getCreatedTime() : int;

  /**
   * Sets the creation timestamp.
   *
   * @param int $timestamp
   *   The creation timestamp.
   *
   * @return \Drupal\client\Entity\ClientInterface
   *   The updated client.
   */
  public function setCreatedTime(int $timestamp) : ClientInterface;

  /**
   * Returns the e-mail address of the client.
   *
   * @return string|null
   *   The e-mail address, or NULL if the e-mail address is not set.
   */
  public function getEmail() : ?string;

  /**
   * Returns the client phone number.
   *
   * @return \Drupal\libphonenumber\LibPhoneNumberInterface|null
   *   The phone number, or NULL if the phone number is not set.
   */
  public function getPhoneNumber() : ?LibPhoneNumberInterface;

  /**
   * Returns the client website.
   *
   * @return null|\Drupal\Core\Url
   *   The website URL, or NULL if the website isn't set.
   */
  public function getWebsite() : ?Url;

}
