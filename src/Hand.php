<?php

namespace Drupal\blackjack;

use Drupal\blackjack\Shoe;

/**
 * Provides a class to be a blackjack hand.
 */
class Hand {

  /**
   * The cards in the hand.
   *
   * @var array
   */
  protected $cards = [];

  /**
   * The hand type.
   *
   * @var string
   */
  protected $type;

  /**
   * The hand value.
   *
   * @var int
   */
  protected $value;

  /**
   * Whether or not the hand has blackjack.
   *
   * @var bool
   */
  protected $blackjack;

  /**
   * Whether or not the hand has busted.
   *
   * @var bool
   */
  protected $busted;

  /**
   * The amount bet on the hand.
   *
   * @var int
   */
  protected $bet;

  /**
   * Whether or not this hand was split.
   *
   * @var bool
   */
  protected $split = FALSE;

  /**
   * Whether or not this hand was doubled.
   *
   * @var bool
   */
  protected $doubled = FALSE;

  /**
   * Whether or not this hand is done being played.
   *
   * @var bool
   */
  protected $done = FALSE;

  /**
   * Constant for hard hands.
   *
   * @var string
   */
  const TYPE_HARD = 'hard';

  /**
   * Constant for hard hands.
   *
   * @var string
   */
  const TYPE_SOFT = 'soft';

  /**
   * Construct a Hand object.
   *
   * @param int $bet
   *   The amount to bet on the hand.
   */
  public function __construct(int $bet = 0) {
    $this->setBet($bet);
  }

  /**
   * Set the bet amount for this hand.
   *
   * @param int $bet
   *   The amount to bet on the hand.
   */
  public function setBet($bet) {
    $this->bet = $bet;
  }

  /**
   * Get the bet for this hand.
   *
   * @return int
   *   The amount bet on this hand.
   */
  public function getBet() {
    return $this->bet;
  }

  /**
   * Split this hand.
   *
   * @return mixed
   *   The card removed from the hand, if needed, otherwise NULL.
   */
  public function split() {
    // Mark as split.
    $this->split = TRUE;

    // Check if there is more than one card in this hand.
    if (count($this->cards)) {
      return array_pop($this->cards);
    }

    return NULL;
  }

  /**
   * Determine if this hand has been split or is the result of a split.
   *
   * @return bool
   *   TRUE if this hand is or was split, otherwise FALSE.
   */
  public function isSplit() {
    return $this->split;
  }

  /**
   * Double this hand.
   */
  public function double() {
    // Double the bet.
    $this->bet *= 2;

    // Mark as doubled.
    $this->doubled = TRUE;
  }

  /**
   * Determine if this hand has been doubled.
   *
   * @return bool
   *   TRUE if this hand was doubled, otherwise FALSE.
   */
  public function isDoubled() {
    return $this->doubled;
  }

  /**
   * Add a card to the hand.
   */
  public function addCard($card) {
    // Check if this hand is done being played.
    if ($this->isDone()) {
      // @todo: custom class?
      throw new \Exception('Unable to add cards to a done hand.');
    }

    // Make sure the card is valid.
    if (!in_array($card, Shoe::DECK)) {
      // @todo: custom class?
      throw new \Exception('Invalid card added to hand.');
    }

    // Add the card.
    $this->cards[] = $card;

    // Evaluate the hand.
    $this->evaluate();
  }

  /**
   * Evaluate the hand to determine the value, blackjack, busted, and
   * hand type.
   */
  private function evaluate() {
    // Reset the hand value.
    $this->value = 0;

    // Default to a hard hand.
    $this->type = self::TYPE_HARD;

    // Add the total value.
    foreach ($this->cards as $card) {
      if (is_numeric($card)) {
        $this->value += $card;
      }
      elseif ($card == 'A') {
        $this->value += 11;
        $this->type = self::TYPE_SOFT;
      }
      else {
        $this->value += 10;
      }
    }

    // If over 21 and soft, convert aces to 1s.
    if (($this->value > 21) && ($this->type == self::TYPE_SOFT)) {
      // Count the occurrances of each card.
      $count = array_count_values($this->cards);

      // Iterate for each ace.
      for ($x = 0; $x < $count['A']; $x++) {
        // Subtract 10 from the value to have this ace act as a 1.
        $this->value -= 10;

        // If this is the last ace converted, the hand is hard.
        if (($count['A'] - $x) == 1) {
          $this->type = self::TYPE_HARD;
        }

        // Check if we're below or equal to 21.
        if ($this->value <= 21) {
          break;
        }
      }
    }

    // Check for blackjack.
    $this->blackjack = (count($this->cards) == 2) && ($this->value == 21) && !$this->isSplit();

    // Check for a bust.
    $this->busted = (bool) ($this->value > 21);

    // Determine if this hand is done being played.
    $this->done =
      $this->blackjack ||
      $this->busted ||
      ($this->isDoubled() && (count($this->cards) == 3)) ||
      ($this->isSplit() && ($this->getUpcard() == 'A') && (count($this->cards) == 2));
  }

  /**
   * Mark this hand as done being played.
   */
  public function done() {
    $this->done = TRUE;
  }

  /**
   * Check if this hand is done being played.
   *
   * @return bool
   *   TRUE if this hand is done, otherwise FALSE.
   */
  public function isDone() {
    return $this->done;
  }

  /**
   * Convert the hand to a string for printing.
   *
   * @return string
   *   The hand in string format.
   */
  public function toString() {
    return implode('-', $this->cards) . ' (' . $this->value . ')';
  }

  /**
   * Return the blackjack status of this hand.
   *
   * @return bool
   *   TRUE if the hand is a blackjack, otherwise FALSE.
   */
  public function isBlackjack() {
    return $this->blackjack;
  }

  /**
   * Return the busted status of this hand.
   *
   * @return bool
   *   TRUE if the hand is a busted, otherwise FALSE.
   */
  public function isBusted() {
    return $this->busted;
  }

  /**
   * Return the hand value.
   *
   * @return int
   *   The numeric value of the hand.
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Return the hand type.
   *
   * @return string
   *   The type of hand (a type constant).
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Return the hand cards.
   *
   * @return array
   *   The cards array.
   */
  public function getCards() {
    return $this->cards;
  }

  /**
   * Return the upcard, which is the first card in the hand.
   *
   * @return string|null
   *   The upcard or NULL if there is not one.
   */
  public function getUpcard() {
    return !empty($this->cards[0]) ? $this->cards[0] : NULL;
  }

  /**
   * Determine if a hand can be split.
   *
   * @return bool
   *   TRUE if the hand can be split, otherwises FALSE.
   */
  public function isSplittable() {
    // Check if there are exactly two cards.
    if (count($this->cards) == 2) {
      // Check if they are the same.
      if (count(array_unique($this->cards)) == 1) {
        // Check that the hand is not done.
        return !$this->isDone();
      }
    }

    return FALSE;
  }

  /**
   * Determine if a hand can be doubled.
   *
   * @return bool
   *   TRUE if the hand can be doubled, otherwises FALSE.
   */
  public function isDoubleable() {
    // Check if there are exactly two cards and the hand is not done.
    return (count($this->cards) == 2) && !$this->isDone();
  }
}
