<?php

namespace Drupal\blackjack;

use Drupal\blackjack\BlackjackStrategyInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\blackjack\Hand;

/**
 * Provides a base class for a blackjack strategy.
 *
 * @see \Drupal\webform\BlackjackStrategyInterface
 * @see \Drupal\webform\BlackjackStrategyManager
 * @see plugin_api
 */
class BlackjackStrategyBase extends PluginBase implements BlackjackStrategyInterface {

  /**
   * Minimum bet.
   *
   * @var int
   */
  protected $betMin = 1;

  /**
   * Maximum bet.
   *
   * @var int
   */
  protected $betMax;

  /**
   * Bet spread.
   *
   * @var int
   */
  protected $betSpread = 1;

  /**
   * Starting bankroll.
   *
   * @var int
   */
  protected $startingBankroll = 0;

  /**
   * The card count.
   *
   * @var int
   */
  protected $count = 0;

  /**
   * The highest card count.
   *
   * @var int
   */
  protected $highestCount = 0;

  /**
   * The lowest card count.
   *
   * @var int
   */
  protected $lowestCount = 0;

  /**
   * The amount of bets made in count.
   *
   * @var int
   */
  protected $betsCountIn = 0;

  /**
   * The amount of bets made out of count.
   *
   * @var int
   */
  protected $betsCountOut = 0;

  /**
   * {@inheritdoc}
   */
  public function count($card, $remaining) {
    // Update the totals.
    $this->highestCount = max($this->highestCount, $this->count);
    $this->lowestCount = min($this->lowestCount, $this->count);
  }

  /**
   * {@inheritdoc}
   */
  public function bet($bankroll, $last_outcome) {
    // Bet the minimum.
    return $this->betMin;
  }

  /**
   * {@inheritdoc}
   */
  public function shuffle() {
    // Reset the count.
    $this->count = 0;
  }

  /**
   * {@inheritdoc}
   */
  public function results() {
    return [
      [t('Strategy'), (string) $this->pluginDefinition['name']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function act(Hand $hand, $dealer_card, bool $can_double, bool $can_split) {
    // Always stand.
    return self::ACTION_STAND;
  }

  /**
   * {@inheritdoc}
   */
  public function stop($bankroll) {
    // Don't stop.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setBetMin(int $value) {
    $this->betMin = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function setBetMax(int $value) {
    $this->betMax = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function setBetSpread(int $value) {
    $this->betSpread = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStartingBankroll(int $value) {
    $this->startingBankroll = $value;
  }

  /**
   * Convert a possible face card to a ten, or keep the same if the
   * card is not a face card.
   *
   * @param $card
   *   A card to possibly convert.
   * @return
   *   The card, possibly converted for a face card to a 10.
   */
  public function convertFaceCard($card) {
    return in_array($card, ['J', 'Q', 'K']) ? 10 : $card;
  }
}
