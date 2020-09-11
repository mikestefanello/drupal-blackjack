<?php

namespace Drupal\blackjack\Commands;

use Drush\Commands\DrushCommands;
use Drupal\blackjack\BlackjackSimulatorInterface;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;

/**
 * Blackjack Drush commands.
 */
class BlackjackCommands extends DrushCommands {

  /**
   * The blackjack simulator.
   *
   * @var \Drupal\blackjack\BlackjackSimulatorInterface
   */
  protected $simulator;

  /**
   * BlackjackCommands constructor.
   *
   * @param \Drupal\blackjack\BlackjackSimulatorInterface $simulator
   */
  public function __construct(BlackjackSimulatorInterface $simulator) {
    $this->simulator = $simulator;
  }

  /**
   * Run a blackjack simulation based on the current configuration.
   *
   * @command blackjack
   *
   * @usage drush blackjack
   *   Run a blackjack simulation based on the current configuration.
   *
   * @field-labels
   *   stat: Stat
   *   value: Value
   *
   * @validate-module-enabled blackjack
   *
   * @aliases bj
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   *   The simulation results.
   */
  public function blackjack() {
    // Play the simulation.
    $this->simulator->play();

    // Get the results.
    $results = $this->simulator->results();

    // Format the results.
    $output = [];
    foreach ($results as $result) {
      $output[] = ['stat' => (string) $result[0], 'value' => (string) $result[1]];
    }

    // Print the results.
    return new RowsOfFields($output);
  }

}
