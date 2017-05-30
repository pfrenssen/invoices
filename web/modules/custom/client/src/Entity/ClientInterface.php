<?php

declare (strict_types = 1);

namespace Drupal\client\Entity;

use Drupal\business\BusinessOwnedInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Url;
use Drupal\libphonenumber\LibPhoneNumberInterface;

/**
 * Provides an interface for defining Client entities.
 *
 * @ingroup client
 */
interface ClientInterface extends BusinessOwnedInterface, ContentEntityInterface, EntityChangedInterface, RevisionLogInterface {

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
