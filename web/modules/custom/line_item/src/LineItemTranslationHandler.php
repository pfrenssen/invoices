<?php

declare (strict_types = 1);

namespace Drupal\line_item;

use Drupal\content_translation\ContentTranslationHandler;
use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the translation handler for line_item.
 */
class LineItemTranslationHandler extends ContentTranslationHandler {

  // Override here the needed methods from ContentTranslationHandler.
  public function __construct(\Drupal\Core\Entity\EntityTypeInterface $entity_type, \Drupal\Core\Language\LanguageManagerInterface $language_manager, \Drupal\content_translation\ContentTranslationManagerInterface $manager, \Drupal\Core\Entity\EntityManagerInterface $entity_manager, \Drupal\Core\Session\AccountInterface $current_user) {
    throw new \Exception(__METHOD__ . ' is generated');
    parent::__construct($entity_type, $language_manager, $manager, $entity_manager, $current_user);
  }

}
