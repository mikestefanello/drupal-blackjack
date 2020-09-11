<?php

namespace Drupal\blackjack;

use Drupal\blackjack\Hand;
use Drupal\blackjack\Shoe;

/**
 * Provides a class to be the dealer in the blackjack simulation.
 */
class Dealer {

  /**
   * Current hand.
   *
   * @var \Drupal\blackjack\Hand
   */
  protected $hand;

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
   * If the dealer should hit on soft 17.
   *
   * @var bool
   */
  protected $hitSoft17;

  /**
   * Construct a blackjack dealer.
   *
   * @param bool $hit_soft_17
   *   Whether or not the dealer should hit on soft 17.
   */
  public function __construct(bool $hit_soft_17) {
    $this->hitSoft17 = $hit_soft_17;
  }

  /**
   * Set the dealer hand.
   *
   * @param \Drupal\blackjack\Hand $hand
   *   The dealer's hand.
   */
  public function setHand(Hand $hand) {
    $this->hand = $hand;
  }

  /**
   * Get the dealer hand.
   *
   * @return \Drupal\blackjack\Hand
   *   The dealer's hand.
   */
  public function getHand() {
    return $this->hand;
  }

  /**
   * Play the game.
   *
   * @param \Drupal\blackjack\Shoe $shoe
   *   The game shoe.
   */
  public function play(Shoe $shoe) {
    // Loop until the hand is done.
    while (!$this->hand->isDone()) {
      // Check for a soft 17.
      if (($this->hand->getValue() == 17) && ($this->hand->getType() == Hand::TYPE_SOFT)) {
        // Check if we're not hitting these.
        if (!$this->hitSoft17) {
          // Stand.
          $this->hand->done();
        }
        else {
          // Hit.
          $this->hand->addCard($shoe->deal());
        }
      }
      // Check if the value is 17 or above.
      elseif ($this->hand->getValue() >= 17) {
        // Stand.
        $this->hand->done();
      }
      else {
        // Hit.
        $this->hand->addCard($shoe->deal());
      }
    }
  }

  /**
   * Perform end of game tasks.
   */
  public function endGame() {
    // Check for a bust.
    if ($this->hand->isBusted()) {
      $this->busts++;
    }

    // Check for a blackjack.
    if ($this->hand->isBlackjack()) {
      $this->blackjacks++;
    }

    // Discard the hand.
    $this->hand = NULL;
  }

  /**
   * Return results.
   *
   * @return array
   *   An array of results.
   */
  public function results() {
    return [
      [t('Dealer blackjacks'), $this->blackjacks],
      [t('Dealer busts'), $this->busts],
      [t('Dealer hit on soft 17'), $this->hitSoft17 ? t('Yes') : t('No')],
    ];
  }
}
