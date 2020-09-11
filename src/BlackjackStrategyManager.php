<?php

/**
 * @file
 * Contains \Drupal\blackjack\BlackjackStrategyManager.
 */

namespace Drupal\blackjack;

use Drupal\blackjack\BlackjackStrategyManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

class BlackjackStrategyManager extends DefaultPluginManager implements BlackjackStrategyManagerInterface {

  /**
   * Creates the discovery object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    $subdir = 'Plugin/BlackjackStrategy';
    $plugin_interface = 'Drupal\blackjack\BlackjackStrategyInterface';
    $plugin_definition_annotation_name = 'Drupal\Component\Annotation\Plugin';

    parent::__construct($subdir, $namespaces, $module_handler, $plugin_interface, $plugin_definition_annotation_name);

    $this->alterInfo('blackjack_strategy_info');
    $this->setCacheBackend($cache_backend, 'blackjack_strategy_info');
  }

}
