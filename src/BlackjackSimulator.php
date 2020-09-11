<?php

namespace Drupal\blackjack;

use Drupal\blackjack\Shoe;
use Drupal\blackjack\Hand;
use Drupal\blackjack\Player;
use Drupal\blackjack\Dealer;
use Drupal\blackjack\BlackjackSimulatorInterface;
use Drupal\blackjack\BlackjackStrategyInterface;
use Drupal\blackjack\BlackjackStrategyManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Defines the blackjack simulator.
 */
class BlackjackSimulator implements BlackjackSimulatorInterface {

  /**
   * The blackjack strategy manager.
   *
   * @var \Drupal\blackjack\BlackjackStrategyManagerInterface
   */
  protected $strategyManager;

  /**
   * The blackjack configuration settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The shoe.
   *
   * @var \Drupal\blackjack\Shoe
   */
  protected $shoe;
  
  /**
   * The player.
   *
   * @var \Drupal\blackjack\Player
   */
  protected $player;

  /**
   * The dealer.
   *
   * @var \Drupal\blackjack\Dealer
   */
  protected $dealer;

  /**
   * Count the amount of games played.
   *
   * @var int
   */
  protected $games = 0;

  /**
   * Minimum bet.
   *
   * @var int
   */
  protected $betMin;

  /**
   * Maximum bet.
   *
   * @var int
   */
  protected $betMax;

  /**
   * Maximum amount of games to play.
   *
   * @var int
   */
  protected $maxGames;

  /**
   * Maximum amount of hands per game.
   *
   * @var int
   */
  protected $maxHandsPerGame;

  /**
   * The blackjack payout.
   *
   * @var int|float
   */
  protected $blackjackPayout;

  /**
   * Constructs a BlackjackSimulator object.
   *
   * @param \Drupal\blackjack\BlackjackStrategyManagerInterface $strategy_manager
   *   The strategy plugin mangaer.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   */
  public function __construct(BlackjackStrategyManagerInterface $strategy_manager, ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('blackjack.settings');
    $this->strategyManager = $strategy_manager;
    $this->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    // Store the settings we'll need during the simulation.
    $this->betMin = $this->config->get('game.bet_min');
    $this->betMax = $this->config->get('game.bet_max');
    $this->maxGames = $this->config->get('player.max_games');
    $this->maxHandsPerGame = $this->config->get('game.max_hands_per_game');
    $this->blackjackPayout = $this->config->get('game.blackjack_payout');

    // Build the strategy.
    $strategy = $this->strategyManager->createInstance($this->config->get('player.strategy'));
    $strategy->setBetMin($this->betMin);
    $strategy->setBetMax($this->betMax);
    $strategy->setBetSpread($this->config->get('player.spread'));
    $strategy->setStartingBankroll($this->config->get('player.bankroll'));

    // Create the simulation components.
    $this->shoe = new Shoe($this->config->get('shoe.decks'), $this->config->get('shoe.penetration'), $strategy);
    $this->player = new Player($this->config->get('player.bankroll'), $strategy);
    $this->dealer = new Dealer($this->config->get('dealer.hit_soft_17'));

    // Reset the game count.
    $this->games = 0;
  }

  /**
   * {@inheritdoc}
   */
  public function play() {
    // The game continues until the player cannot make a minimum bet,
    // we've reached the maximum amount of games, or the player has
    // decided to stop.
    while (
      ($this->player->canBet($this->betMin)) &&
      ($this->games < $this->maxGames) &&
      !$this->player->stop()) {

      // Increment the game counter.
      $this->games++;

      // Player bets.
      $this->initBet();

      // Deal the game.
      $this->deal();

      // Check that the no one has blackjack.
      if (!$this->dealer->getHand()->isBlackjack() && !$this->player->getHand()->isBlackjack()) {
        // Player plays.
        $this->player->play($this->shoe, $this->maxHandsPerGame, $this->dealer->getHand()->getUpcard());

        // Check if the player is still alive.
        if (!$this->player->isBusted()) {
          // Dealer plays now.
          $this->dealer->play($this->shoe);
        }
      }

      // Finish the game.
      $this->player->endGame($this->dealer->getHand(), $this->blackjackPayout, $this->shoe->remaining());
      $this->dealer->endGame();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function results() {
    return array_merge(
      [
        [t('Games'), $this->games],
        [t('Min. bet'), $this->betMin],
        [t('Max. bet'), $this->betMax],
      ],
      $this->shoe->results(),
      $this->player->results(),
      $this->dealer->results()
    );
  }

  /**
   * Initialize the player's bet to start a game.
   *
   * @return int
   *   The bet amount.
   */
  private function initBet() {
    // Player bets.
    $bet = $this->player->bet();

    // Enforce betting rules.
    $bet = max($this->betMin, min($bet, $this->betMax, $this->player->getBankroll()));

    // No decimal bets.
    // @todo: only allow anything divisible by 2 or 5?
    $bet = floor($bet);

    // Set the updated bet.
    $this->player->setBet($bet);

    return $bet;
  }

  /**
   * Deal the cards to start the game.
   */
  private function deal() {
    // Initialize the dealer and player hands.
    $player_hand = new Hand($this->player->getBet());
    $dealer_hand = new Hand();

    // Deal the cards.
    for ($i = 1; $i <= 2; $i++) {
      $player_hand->addCard($this->shoe->deal());
      $dealer_hand->addCard($this->shoe->deal());
    }

    // Assign the hands.
    $this->player->setHand($player_hand);
    $this->dealer->setHand($dealer_hand);
  }
}
