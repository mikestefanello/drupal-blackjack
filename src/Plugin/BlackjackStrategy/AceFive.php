<?php

namespace Drupal\blackjack\Plugin\BlackjackStrategy;

use Drupal\blackjack\Plugin\BlackjackStrategy\Basic;
use Drupal\blackjack\BlackjackStrategyBase;

/**
 * Provides a blackjack strategy plugin for an ace 5 count.
 *
 * @Plugin(
 *   id = "ace_five",
 *   name = @Translation("Ace Five"),
 *   description = @Translation("Use an ace five count and bet the spread once at +2."),
 * )
 */
class AceFive extends Basic {

  /**
   * {@inheritdoc}
   */
  public function bet($bankroll, $last_outcome) {
    // Check if the count is under 2.
    if ($this->count < 2) {
      $this->betsCountOut++;
      return $this->betMin;
    }

    $this->betsCountIn++;
    return $this->betMin * $this->betSpread;
  }

  /**
   * {@inheritdoc}
   */
  public function count($card, $remaining) {
    // Check for an ace.
    if ($card == 'A') {
      $this->count--;
    }

    // Check for a 5.
    if ($card == 5) {
      $this->count++;
    }

    parent::count($card, $remaining);
  }

  /**
   * {@inheritdoc}
   */
  public function results() {
    return array_merge(parent::results(), [
      [t('Bets in count'), $this->betsCountIn],
      [t('Bets out of count'), $this->betsCountOut],
      [t('Highest count'), $this->highestCount],
      [t('Lowest count'), $this->lowestCount],
      [t('Bet spread'), $this->betSpread],
    ]);
  }

}
