<?php
/**
 * @file
 * Provides Drupal\blackjack\BlackjackStrategyInterface.
 */

namespace Drupal\blackjack;

use Drupal\blackjack\Hand;

/**
 * An interface for all Blackjack strategy plugins.
 */
interface BlackjackStrategyInterface {

  /**
   * Action constant for hitting a hand.
   *
   * @var string
   */
  const ACTION_HIT = 'hit';

  /**
   * Action constant for hitting a hand.
   *
   * @var string
   */
  const ACTION_STAND = 'stand';

  /**
   * Action constant for splitting a hand.
   *
   * @var string
   */
  const ACTION_SPLIT = 'split';

  /**
   * Action constant for doubling a hand.
   *
   * @var string
   */
  const ACTION_DOUBLE = 'double';

  /**
   * Perform counting operations after a single game has concluded.
   *
   * @param string $card
   *   A card that was dealt.
   * @param int $shoe_remaining
   *   The amount of card left in the shoe.
   */
  public function count($card, $remaining);

  /**
   * Detremine the amount to bet.
   *
   * @param int|float $bankroll
   *   The player's current bankroll.
   * @param int|float $last_outcome
   *   The net outcome of the last game.
   * @return int
   *   The bet to make on the next game.
   */
  public function bet($bankroll, $last_outcome);

  /**
   * Perform any operations needed when the shoe is shuffled.
   */
  public function shuffle();

  /**
   * Provide results to output after the simulation has concluded.
   *
   * @return array
   *   An array of results with the first value as the result label
   *   and the second value as the result value.
   */
  public function results();

  /**
   * Act on your hand.
   *
   * @param \Drupal\blackjack\Hand $hand
   *   The player's hand.
   * @param string $dealer_card
   *   The dealer's upcard.
   * @param bool $can_double
   *   TRUE if the game rules and bankroll allow the player to double.
   * @param bool $can_split
   *   TRUE if the game rules and bankroll allow the player to split.
   * @return string
   *   The action constant representing what the player action should be.
   */
  public function act(Hand $hand, $dealer_card, bool $can_double, bool $can_split);

  /**
   * Determine if you should stop playing or not before the next
   * hand is dealt.
   *
   * @param int|float $bankroll
   *   The current bankroll.
   * @return bool
   *   TRUE if the player should stop playing, otherwise FALSE.
   */
  public function stop($bankroll);

  /**
   * Set the minimum bet amount.
   *
   * @param int $value
   *   The minimum bet amount.
   */
  public function setBetMin(int $value);

  /**
   * Set the maximum bet amount.
   *
   * @param int $value
   *   The maximum bet amount.
   */
  public function setBetMax(int $value);

  /**
   * Set the bet spread.
   *
   * @param int $value
   *   The bet spread.
   */
  public function setBetSpread(int $value);

  /**
   * Set the starting bankroll.
   *
   * @param int $value
   *   The starting bankroll.
   */
  public function setStartingBankroll(int $value);

}
