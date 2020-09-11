<?php

namespace Drupal\blackjack\Plugin\BlackjackStrategy;

use Drupal\blackjack\Plugin\BlackjackStrategy\Basic;
use Drupal\blackjack\BlackjackStrategyBase;

/**
 * Provides a blackjack strategy plugin for anti-martingale betting
 * using 50% profit.
 *
 * @Plugin(
 *   id = "antimartingale50",
 *   name = @Translation("Anti-Martingale 50%"),
 *   description = @Translation("Bet your last win plus half of the profit, otherwise bet the min."),
 * )
 */
class AntiMartingale50 extends Basic {

  /**
   * {@inheritdoc}
   */
  public function bet($bankroll, $last_outcome) {
    // Check if we won the last game.
    if ($last_outcome > 0) {
      // Add half the profit.
      return $this->betMin + ($last_outcome * .5);
    }

    // Fallback to the minimum bet.
    return $this->betMin;
  }
}
