<?php

namespace Drupal\blackjack\Plugin\BlackjackStrategy;

use Drupal\blackjack\BlackjackStrategyBase;
use Drupal\blackjack\Hand;

/**
 * Provides a blackjack strategy plugin for basic strategy.
 *
 * @Plugin(
 *   id = "basic",
 *   name = @Translation("Basic strategy"),
 *   description = @Translation("Basic strategy playing rules while betting the minimum on each hand."),
 * )
 */
class Basic extends BlackjackStrategyBase {

  /**
   * Rule mapping for splitting.
   */
  const RULES_SPLIT = [
    'A' => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    2 => [2, 3, 4, 5, 6, 7],
    3 => [2, 3, 4, 5, 6, 7],
    4 => [5, 6],
    5 => [],
    6 => [2, 3, 4, 5, 6],
    7 => [2, 3, 4, 5, 6, 7],
    8 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    9 => [2, 3, 4, 5, 6, 8, 9],
    10 => [],
  ];

  /**
   * Rule mapping for doubling hard hands.
   */
  const RULES_DOUBLE_HARD = [
    9 => [3, 4, 5, 6],
    10 => [2, 3, 4, 5, 6, 7, 8, 9],
    11 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
  ];

  /**
   * Rule mapping for doubling soft hands.
   */
  const RULES_DOUBLE_SOFT = [
    13 => [5, 6],
    14 => [5, 6],
    15 => [4, 5, 6],
    16 => [4, 5, 6],
    17 => [3, 4, 5, 6],
    18 => [2, 3, 4, 5, 6],
    19 => [6],
  ];

  /**
   * Rule mapping for hitting hard hands.
   */
  const RULES_HIT_HARD = [
    4 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    5 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    6 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    7 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    8 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    9 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    10 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    11 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    12 => [2, 3, 7, 8, 9, 10, 'A'],
    13 => [7, 8, 9, 10, 'A'],
    14 => [7, 8, 9, 10, 'A'],
    15 => [7, 8, 9, 10, 'A'],
    16 => [7, 8, 9, 10, 'A'],
  ];

  /**
   * Rule mapping for hitting soft hands.
   */
  const RULES_HIT_SOFT = [
    13 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    14 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    15 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    16 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    17 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 'A'],
    18 => [9, 10, 'A'],
  ];

  /**
   * {@inheritdoc}
   */
  public function act(Hand $hand, $dealer_card, bool $can_double, bool $can_split) {
    // @todo Adjust for dealer standing on soft 17.
    // @todo Surrender rules!.

    // Check if the hand can be split.
    if ($can_split) {
      // Determine if the hand should be split.
      if (in_array($dealer_card, self::RULES_SPLIT[$this->convertFaceCard($hand->getUpcard())])) {
        return self::ACTION_SPLIT;
      }
    }

    // Check if the hand can be doubled.
    if ($can_double) {
      // Check if we should double.
      if ($this->inRulesByHandType($hand, $dealer_card, self::RULES_DOUBLE_HARD, self::RULES_DOUBLE_SOFT)) {
        return self::ACTION_DOUBLE;
      }
    }

    // Determine if we should hit.
    if ($this->inRulesByHandType($hand, $dealer_card, self::RULES_HIT_HARD, self::RULES_HIT_SOFT)) {
      return self::ACTION_HIT;
    }

    // Fallback to stand.
    return self::ACTION_STAND;
  }

  /**
   * Determine if a given hand falls under a given ruleset based on the
   * type of hand.
   *
   * @param \Drupal\blackjack\Hand $hand
   *   The player's hand.
   * @param $dealer_card
   *   The dealer's upcard.
   * @param array $hard_rules
   *   The rules to follow if the hand is hard.
   * @param array $soft_rules
   *   The rules to follow if the hand is soft.
   * @return bool
   *   TRUE if the hand fall under the given rules, otherwise FALSE.
   */
  public function inRulesByHandType(Hand $hand, $dealer_card, array $hard_rules, array $soft_rules) {
    $rules = ($hand->getType() == Hand::TYPE_HARD) ? $hard_rules : $soft_rules;
    if (isset($rules[$hand->getValue()]) && in_array($this->convertFaceCard($dealer_card), $rules[$hand->getValue()])) {
      return TRUE;
    }
    return FALSE;
  }

}
