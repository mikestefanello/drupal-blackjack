<?php

namespace Drupal\blackjack;

/**
 * Provides an interface for blackjack simulation.
 */
interface BlackjackSimulatorInterface {

  /**
   * Reset the entire game in order to start fresh.
   */
  public function reset();

  /**
   * Execute the simulation.
   */
  public function play();

  /**
   * Provide results for the simulation.
   *
   * @return array
   *   An array of results with the first value as the result label
   *   and the second value as the result value.
   */
  public function results();
}
