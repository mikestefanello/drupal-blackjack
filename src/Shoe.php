<?php

namespace Drupal\blackjack;

use Drupal\blackjack\BlackjackStrategyInterface;

/**
 * Provides a class to control the shoe of the blackjack simulation.
 */
class Shoe {

  /**
   * The amount of decks to use.
   *
   * @var int
   */
  protected $decks;

  /**
   * The shoe penetration allowed, between 0 and 1.
   *
   * @var float
   */
  protected $penetration;

  /**
   * Blackjack strategy plugin.
   *
   * @var \Drupal\blackjack\BlackjackStrategyInterface
   */
  protected $strategy;

  /**
   * The shoe cards.
   *
   * @var array
   */
  protected $cards;

  /**
   * The amount of times the deck was shuffled.
   *
   * @var int
   */
  protected $shuffles = 0;

  /**
   * The values of a deck.
   *
   * @var array
   */
  const DECK = [2, 3, 4, 5, 6, 7, 8, 9, 10, 'J', 'Q', 'K', 'A'];

  /**
   * Construct a shoe object.
   *
   * @param int $decks
   *   The amount of decks to add.
   * @param float $penetration
   *   The penetration allowed.
   * @param \Drupal\blackjack\BlackjackStrategyInterface $strategy
   *   The blackjack strategy plugin.
   */
  public function __construct(int $decks, float $penetration, BlackjackStrategyInterface $strategy) {
    $this->decks = $decks;
    $this->penetration = $penetration;
    $this->strategy = $strategy;
  }

  /**
   * Shuffle the shoe.
   */
  public function shuffle() {
    // Clear the current cards.
    $this->cards = [];

    // Increment the shuffle count.
    $this->shuffles++;

    // Generate a full deck.
    $deck = [];
    foreach (self::DECK as $card) {
      for ($x = 0; $x < 4; $x++) {
        $deck[] = $card;
      }
    }

    // Add decks to the shoe.
    for ($x = 0; $x < $this->decks; $x++) {
      $this->cards = array_merge($this->cards, $deck);
    }

    // Shuffle the deck 20 times.
    for ($x = 0; $x < 20; $x++) {
      shuffle($this->cards);
    }

    // The strategy needs to be invoked here.
    $this->strategy->shuffle();
  }

  /**
   * Deal a card.
   *
   * @return string
   *   A card.
   */
  public function deal() {
    // Shuffle, if needed.
    if ($this->shuffleNeeded()) {
      $this->shuffle();
    }

    // Deal a card.
    return array_shift($this->cards);
  }

  /**
   * Determine how many cards are remaining in the shoe.
   *
   * @return int
   *   The remaining card count.
   */
  public function remaining() {
    return count($this->cards);
  }

  /**
   * Determine if a shuffle is required.
   *
   * @return bool
   *   TRUE if a shuffle is required otherwise FALSE.
   */
  public function shuffleNeeded() {
    // Check if the shoe is empty.
    if (empty($this->cards)) {
      return TRUE;
    }
    // Check if the penetration has been reached.
    elseif ($this->remaining() < (($this->decks * 52) * (1 - $this->penetration))) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Return results.
   *
   * @return array
   *   An array of results.
   */
  public function results() {
    return [
      [t('Shuffles'), $this->shuffles],
    ];
  }
}
