<?php

namespace Drupal\blackjack\Plugin\BlackjackStrategy;

use Drupal\blackjack\Plugin\BlackjackStrategy\Basic;
use Drupal\blackjack\BlackjackStrategyBase;

/**
 * Provides a blackjack strategy plugin for a hi-lo count.
 *
 * @Plugin(
 *   id = "hilo",
 *   name = @Translation("Hi Lo"),
 *   description = @Translation("Use a hi lo count and bet the minimum times twice the true count."),
 * )
 */
class HiLo extends Basic {

  /**
   * The true count.
   *
   * @var int.
   */
  protected $trueCount = 0;

  /**
   * The highest true count.
   *
   * @var int
   */
  protected $highestTrueCount = 0;

  /**
   * The lowest true count.
   *
   * @var int
   */
  protected $lowestTrueCount = 0;

  /**
   * {@inheritdoc}
   */
  public function bet($bankroll, $last_outcome) {
    // Check if the count is positive.
    if ($this->trueCount > 0) {
      $this->betsCountIn++;
      return $this->betMin * 2 * $this->trueCount;
    }
    $this->betsCountOut++;
    return $this->betMin;
  }

  /**
   * {@inheritdoc}
   */
  public function count($card, $remaining) {
    switch ($card) {
      case 2:
      case 3:
      case 4:
      case 5:
      case 6:
        $this->count++;
        break;

      case 10:
      case 'J':
      case 'Q':
      case 'K':
      case 'A':
        $this->count--;
        break;
    }

    // Determine the true count.
    $this->trueCount = round($this->count / ($remaining / 52));

    // Update the totals.
    $this->highestTrueCount = max($this->highestTrueCount, $this->trueCount);
    $this->lowestTrueCount = min($this->lowestTrueCount, $this->trueCount);
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
      [t('Highest true count'), $this->highestTrueCount],
      [t('Lowest count'), $this->lowestCount],
      [t('Lowest true count'), $this->lowestTrueCount],
    ]);
  }

}
