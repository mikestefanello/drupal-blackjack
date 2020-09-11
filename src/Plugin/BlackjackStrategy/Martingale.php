<?php

namespace Drupal\blackjack\Plugin\BlackjackStrategy;

use Drupal\blackjack\Plugin\BlackjackStrategy\Basic;
use Drupal\blackjack\BlackjackStrategyBase;

/**
 * Provides a blackjack strategy plugin for martingale betting.
 *
 * @Plugin(
 *   id = "martingale",
 *   name = @Translation("Martingale"),
 *   description = @Translation("Bet double your last loss otherwise bet the minimum."),
 * )
 */
class Martingale extends Basic {

  /**
   * {@inheritdoc}
   */
  public function bet($bankroll, $last_outcome) {
    // Check if we lost the last game.
    if ($last_outcome < 0) {
      // Bet twice the loss.
      return $last_outcome * -2;
    }

    // Fallback to the minimum bet.
    return $this->betMin;
  }
}
