<?php

namespace Drupal\blackjack\Controller;

use Drupal\blackjack\BlackjackSimulatorInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller routines for the blackjack simulator.
 */
class BlackjackSimulatorController extends ControllerBase {

  /**
   * The blackjack simulator.
   *
   * @var \Drupal\blackjack\BlackjackSimulatorInterface
   */
  protected $simulator;

  /**
   * The page cache killswitch.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $pageCacheKillSwitch;

  /**
   * Construct a BlackjackSimulatorController object.
   *
   * @param \Drupal\blackjack\BlackjackSimulatorInterface $simulator
   *   The blackjack simulator.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $pageCacheKillSwitch
   *   The page cache kill switch.
   */
  public function __construct(BlackjackSimulatorInterface $simulator, KillSwitch $page_cache_kill_switch) {
    $this->simulator = $simulator;
    $this->pageCacheKillSwitch = $page_cache_kill_switch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('blackjack.simulator'),
      $container->get('page_cache_kill_switch')
    );
  }

  /**
   * Perform the simulation and output the results.
   */
  public function simulation() {
    // Prevent caching this page.
    $this->pageCacheKillSwitch->trigger();

    // Play the simulation.
    $this->simulator->play();

    // Get the results.
    $results = $this->simulator->results();

    return [
      'results' => [
        '#type' => 'table',
        '#header' => [],
        '#rows' => $results,
      ],
      'actions' => [
        'rerun' => [
          '#type' => 'link',
          '#title' => $this->t('Rerun simulation'),
          '#url' => Url::fromRoute('blackjack.simulator'),
          '#attributes' => [
            'class' => ['button'],
          ],
        ],
        'settings' => [
          '#type' => 'link',
          '#title' => $this->t('Simulation settings'),
          '#url' => Url::fromRoute('blackjack.simulator.form'),
          '#attributes' => [
            'class' => ['button'],
          ],
        ],
      ],
    ];
  }

}
