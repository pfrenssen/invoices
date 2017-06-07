<?php

declare (strict_types = 1);

namespace Drupal\business\Plugin\views\argument_default;

use Drupal\business\BusinessManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default argument plugin to return the ID of the active business.
 *
 * @ingroup views_argument_default_plugins
 *
 * @ViewsArgumentDefault(
 *   id = "active_business",
 *   title = @Translation("Active business")
 * )
 */
class ActiveBusiness extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * The business manager.
   *
   * @var \Drupal\business\BusinessManagerInterface
   */
  protected $businessManager;

  /**
   * Constructs a Raw object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\business\BusinessManagerInterface $business_manager
   *   The business manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BusinessManagerInterface $business_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->businessManager = $business_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('business.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    return $this->businessManager->getActiveBusinessId();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // @todo Write an ActiveBusiness cache context.
    return [];
  }

}
