<?php

namespace Drupal\blackjack;

use Drupal\blackjack\Hand;
use Drupal\blackjack\BlackjackStrategyInterface;

/**
 * Provides a class to be the player in the blackjack simulation.
 */
class Player {

  /**
   * Starting bankroll.
   *
   * @var int
   */
  protected $startingBankroll;

  /**
   * Current bankroll.
   *
   * @var int|float
   */
  protected $bankroll;

  /**
   * Blackjack strategy plugin.
   *
   * @var \Drupal\blackjack\BlackjackStrategyInterface
   */
  protected $strategy;

  /**
   * Current bet.
   *
   * @var int
   */
  protected $bet;

  /**
   * Current hands.
   *
   * @var array
   */
  protected $hands = [];

  /**
   * The count of hands played.
   *
   * @var int
   */
  protected $handsPlayed = 0;

  /**
   * The count of hands won.
   *
   * @var int
   */
  protected $wins = 0;

  /**
   * The count of hands lost.
   *
   * @var int
   */
  protected $loses = 0;

  /**
   * The count of hands pushed.
   *
   * @var int
   */
  protected $pushes = 0;

  /**
   * The count of hands doubled.
   *
   * @var int
   */
  protected $doubles = 0;

  /**
   * The count of hands split.
   *
   * @var int
   */
  protected $splits = 0;

  /**
   * The count of hands busted.
   *
   * @var int
   */
  protected $busts = 0;

  /**
   * The count of blackjacks.
   *
   * @var int
   */
  protected $blackjacks = 0;

  /**
   * The net outcomme of the last game.
   *
   * @var int|float
   */
  protected $lastOutcome = 0;

  /**
   * Construct a blackjack player.
   *
   * @param int $bankroll
   *   The starting bankroll.
   * @param \Drupal\blackjack\BlackjackStrategyInterface $strategy
   *   The blackjack strategy plugin.
   */
  public function __construct(int $bankroll, BlackjackStrategyInterface $strategy) {
    $this->startingBankroll = $this->bankroll = $bankroll;
    $this->strategy = $strategy;
  }

  /**
   * Return the player's bankroll.
   *
   * @return int|float
   *   The bankroll.
   */
  public function getBankroll() {
    return $this->bankroll;
  }

  /**
   * Ask the player if the game should stop.
   *
   * @return bool
   *   TRUE if the game should stop otherwise FALSE.
   */
  public function stop() {
    // Let the strategy dictate this.
    return $this->strategy->stop($this->getBankroll());
  }

  /**
   * Determine and set the bet for the next game.
   *
   * @return int
   *   The bet amount.
   */
  public function bet() {
    return $this->setBet($this->strategy->bet($this->getBankroll(), $this->lastOutcome));
  }

  /**
   * Set the bet for the next game.
   *
   * @return int
   *   The bet amount.
   */
  public function setBet($bet) {
    return $this->bet = $bet;
  }

  /**
   * Get the bet for the next game.
   *
   * Note that this is only the initial bet and not the total of all
   * bets in the current game.
   *
   * @return int
   *   The bet amount.
   */
  public function getBet() {
    return $this->bet;
  }

  /**
   * Get the total bet on the current game across all hands.
   *
   * @return int
   *   The total amount bet in this game.
   */
  public function getCurrentBet() {
    $total = 0;

    foreach ($this->hands as $hand) {
      $total += $hand->getBet();
    }

    return $total;
  }

  /**
   * Set a hand.
   *
   * @param \Drupal\blackjack\Hand $hand
   *   The hand.
   */
  public function setHand(Hand $hand) {
    $this->hands[] = $hand;
  }

  /**
   * Get a hand.
   *
   * @param int $index
   *   The index of the hand to return.
   * @return \Drupal\blackjack\Hand
   *   The hand.
   */
  public function getHand($index = 0) {
    return $this->hands[$index];
  }

  /**
   * Get the hands.
   *
   * @return array
   *   An array of \Drupal\blackjack\Hand.
   */
  public function getHands() {
    return $this->hands;
  }

  /**
   * Determine if the player is completely busted, meaning that all
   * hands are busted.
   *
   * @return bool
   *   TRUE if all of the player's hands are busted, otherwise FALSE.
   */
  public function isBusted() {
    foreach ($this->getHands() as $hand) {
      if (!$hand->isBusted()) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Determine if the player is completely done, meaning that all
   * hands are done being played.
   *
   * @return bool
   *   TRUE if all of the player's hands are done, otherwise FALSE.
   */
  public function isDone() {
    foreach ($this->getHands() as $hand) {
      if (!$hand->isDone()) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Play the game.
   *
   * @param \Drupal\blackjack\Shoe $shoe
   *   The game shoe.
   * @param int $max_hands
   *   The maximum amount of hands allowed per game.
   * @param $dealer_card
   *   The dealer's upcard.
   */
  public function play(Shoe $shoe, int $max_hands, $dealer_card) {
    // Loop until done.
    while (!$this->isDone()) {
      // Loop all hands.
      foreach ($this->getHands() as $hand) {
        // Loop until this hand is done.
        while (!$hand->isDone()) {
          // Determine if a split can be made.
          $can_split = $hand->isSplittable() && $this->canBet($this->getBet()) && (count($this->getHands()) < $max_hands);
  
          // Determine if a double can be made.
          $can_double = $hand->isDoubleable() && $this->canBet($this->getBet());
  
          // Determine what to do.
          switch ($this->strategy->act($hand, $dealer_card, $can_double, $can_split)) {
            case BlackjackStrategyInterface::ACTION_STAND:
              $hand->done();
              break;
  
            case BlackjackStrategyInterface::ACTION_DOUBLE:
              $hand->double();
  
            case BlackjackStrategyInterface::ACTION_HIT:
              $hand->addCard($shoe->deal());
              break;
  
            case BlackjackStrategyInterface::ACTION_SPLIT:
              // Split the hand.
              $card = $hand->split();
              $hand->addCard($shoe->deal());
  
              // Add the new hand.
              // @todo: does the hand order matter?
              $new_hand = new Hand($this->getBet());
              $new_hand->split();
              $new_hand->addCard($card);
              $new_hand->addCard($shoe->deal());
              $this->setHand($new_hand);
              break;
          }
        }
      }
    }
  }

  /**
   * Determine if the player can bet a certain amount based on their
   * bankroll and current bet.
   *
   * @param int $bet
   *   A bet to check that the player can make.
   * @return bool
   *   TRUE if the player has the money to make the bet, otherwise FALSE.
   */
  public function canBet($bet) {
    return $this->getBankroll() >= ($this->getCurrentBet() + $bet);
  }

  /**
   * Perform end of game tasks.
   *
   * @param \Drupal\blackjack\Hand $dealer_hand
   *   The dealer's hand.
   * @param int|float $blackjack_payout
   *   The payout for a blackjack.
   * @param int $shoe_remaining
   *   The amount of cards remaining in the shoe.
   */
  public function endGame(Hand $dealer_hand, $blackjack_payout, int $shoe_remaining) {
    // Reset the last outcome.
    $this->lastOutcome = 0;

    // Iterate the hands.
    foreach ($this->hands as $hand) {
      // Increment the hand count.
      $this->handsPlayed++;

      // Check for a double.
      if ($hand->isDoubled()) {
        // Increment the double counter.
        $this->doubles++;
      }

      // Check for a split.
      if ($hand->isSplit()) {
        // Increment the split counter.
        // Both split hands will register here so we add a half.
        $this->splits += 0.5;
      }

      // Check for a bust.
      if ($hand->isBusted()) {
        // Increment the bust counter.
        $this->busts++;

        // Lose the bet.
        $this->lose($hand->getBet());
      }
      // Check for a blackjack.
      elseif ($hand->isBlackjack()) {
        $this->blackjacks++;

        // Check if the dealer does not have blackjack.
        if (!$dealer_hand->isBlackjack()) {
          // Win the bet mutiplied by the blackjack payout.
          $this->win($hand->getBet() * $blackjack_payout);
        }
        else {
          // Increment the push counter.
          $this->pushes++;
        }
      }
      // Check if the dealer busted or the player had a better hand.
      elseif ($dealer_hand->isBusted() || ($hand->getValue() > $dealer_hand->getValue())) {
        $this->win($hand->getBet());
      }
      // Check if the player lost.
      elseif ($hand->getValue() < $dealer_hand->getValue()) {
        $this->lose($hand->getBet());
      }
      // The player pushed.
      else {
        $this->pushes++;
      }

      // Iterate the cards in the hand.
      foreach ($hand->getCards() as $card) {
        // Allow the strategy to count.
        $this->strategy->count($card, $shoe_remaining);
      }
    }

    // Iterate the cards in the dealer's hand.
    foreach ($dealer_hand->getCards() as $card) {
      // Allow the strategy to count.
      $this->strategy->count($card, $shoe_remaining);
    }

    // Discard the hands
    $this->hands = [];
  }

  /**
   * Win a bet.
   *
   * @param int|float $bet
   *   The bet that was won.
   */
  private function win($bet) {
    // Increment the wins counter.
    $this->wins++;

    // Add the bet to the bankroll.
    $this->bankroll += $bet;

    // Track the last outcome.
    $this->lastOutcome += $bet;
  }

  /**
   * Lose a bet.
   *
   * @param int|float $bet
   *   The bet that was lost.
   */
  private function lose($bet) {
    // Increment the loss counter.
    $this->loses++;

    // Subtract the bet from the bankroll.
    $this->bankroll -= $bet;

    // Track the last outcome.
    $this->lastOutcome -= $bet;
  }

  /**
   * Return results.
   *
   * @return array
   *   An array of results.
   */
  public function results() {
    return array_merge([
      [t('Starting bankroll'), number_format($this->startingBankroll, 2)],
      [t('Bankroll'), $this->formatNumberAndPercentage($this->bankroll, $this->startingBankroll)],
      [t('Change'), $this->formatNumberAndPercentage($this->bankroll - $this->startingBankroll, $this->startingBankroll)],
      [t('Hands'), $this->handsPlayed],
      [t('Wins'), $this->formatNumberAndPercentage($this->wins, $this->handsPlayed)],
      [t('Loses'), $this->formatNumberAndPercentage($this->loses, $this->handsPlayed)],
      [t('Pushes'), $this->formatNumberAndPercentage($this->pushes, $this->handsPlayed)],
      [t('Doubles'), $this->formatNumberAndPercentage($this->doubles, $this->handsPlayed)],
      [t('Splits'), $this->formatNumberAndPercentage($this->splits, $this->handsPlayed)],
      [t('Blackjacks'), $this->formatNumberAndPercentage($this->blackjacks, $this->handsPlayed)],
      [t('Busts'), $this->formatNumberAndPercentage($this->busts, $this->handsPlayed)],
    ], $this->strategy->results());
  }

  /**
   * Output a numeric value and the percentage that it is of a given
   * total value.
   *
   * IE, passing in 5 and 10 would output: 5 (50%)
   *
   * @param int|float $value
   *   The value to print.
   * @param int|float $total
   *   The total value to determine what percentage value is of this.
   * @return string
   *   A string to output.
   */
  public function formatNumberAndPercentage($value, $total) {
    return $value . ' (' . number_format(($value * 100) / $total, 2) . '%)';
  }
}
