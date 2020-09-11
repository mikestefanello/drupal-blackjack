<?php

namespace Drupal\blackjack\Form;

use Drupal\blackjack\BlackjackStrategyManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure the blackjack simulator.
 */
class BlackjackSimulatorForm extends ConfigFormBase {

  /**
   * The blackjack strategy manager.
   *
   * @var \Drupal\blackjack\BlackjackStrategyManagerInterface
   */
  protected $strategyManager;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'blackjack_simulation_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['blackjack.settings'];
  }

  /**
   * Constructs a BlackjackSimulatorForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\blackjack\BlackjackStrategyManagerInterface $strategy_manager
   *   The webform element manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, BlackjackStrategyManagerInterface $strategy_manager) {
    parent::__construct($config_factory);
    $this->strategyManager = $strategy_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.blackjack.strategy')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('blackjack.settings');
    $strategies = $this->strategyManager->getDefinitions();

    // Strategy settings.
    $form['strategies'] = [
      '#type' => 'details',
      '#title' => $this->t('Strategy'),
      '#description' => $this->t('Strategies dictate the player action and betting rules.'),
      '#open' => TRUE,
    ];
    $form['strategies']['strategy'] = [
      '#type' => 'select',
      '#options' => [],
      '#default_value' => $config->get('player.strategy'),
    ];
    $form['strategies']['descriptions'] = [];

    // Iterate the strategy plugins.
    foreach ($strategies as $id => $definition) {
      // Add a select option.
      $form['strategies']['strategy']['#options'][$definition['id']] = $definition['name'];

      // Add the description which will dynamically show based on the selection.
      $form['strategies']['descriptions'][$definition['id']] = [
        '#type' => 'item',
        '#markup' => $definition['description'],
        '#states' => [
          'visible' => [
            ':input[name="strategy"]' => ['value' => $definition['id']],
          ],
        ],
      ];
    }

    // Player settings.
    $form['player'] = [
      '#type' => 'details',
      '#title' => $this->t('Player'),
      '#open' => TRUE,
    ];
    $form['player']['bankroll'] = [
      '#type' => 'number',
      '#title' => $this->t('Bankroll'),
      '#min' => 1,
      '#description' => $this->t('The amount of money to start with.'),
      '#required' => TRUE,
      '#default_value' => $config->get('player.bankroll'),
    ];
    $form['player']['spread'] = [
      '#type' => 'number',
      '#title' => $this->t('Spread'),
      '#min' => 1,
      '#description' => $this->t('The betting spread. Some strategies make use of this number.'),
      '#required' => TRUE,
      '#default_value' => $config->get('player.spread'),
    ];
    $form['player']['max_games'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum amount of games'),
      '#min' => 1,
      '#description' => $this->t('The maximum amount of games to be played.'),
      '#required' => TRUE,
      '#default_value' => $config->get('player.max_games'),
    ];

    // Dealer settings.
    $form['dealer'] = [
      '#type' => 'details',
      '#title' => $this->t('Dealer'),
      '#open' => TRUE,
    ];
    $form['dealer']['hit_soft_17'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hit on soft 17'),
      '#default_value' => $config->get('dealer.hit_soft_17'),
    ];

    // Game settings.
    $form['game'] = [
      '#type' => 'details',
      '#title' => $this->t('Game'),
      '#open' => TRUE,
    ];
    $form['game']['blackjack_payout'] = [
      '#type' => 'number',
      '#title' => $this->t('Blackjack payout'),
      '#min' => 1,
      '#step' => 0.25,
      '#description' => $this->t('The payout for being dealt a blackjack.'),
      '#required' => TRUE,
      '#default_value' => $config->get('game.blackjack_payout'),
    ];
    $form['game']['max_hands_per_game'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum hands per game'),
      '#min' => 1,
      '#max' => 12,
      '#description' => $this->t('The maxmimum amount of hands that can be played at a time via splits.'),
      '#required' => TRUE,
      '#default_value' => $config->get('game.max_hands_per_game'),
    ];
    $form['game']['bet_min'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum bet'),
      '#min' => 1,
      '#description' => $this->t('The minimum allowed bet size.'),
      '#required' => TRUE,
      '#default_value' => $config->get('game.bet_min'),
    ];
    $form['game']['bet_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum bet'),
      '#min' => 1,
      '#description' => $this->t('The maximum allowed bet size.'),
      '#required' => TRUE,
      '#default_value' => $config->get('game.bet_max'),
    ];

    // Shoe settings.
    $form['shoe'] = [
      '#type' => 'details',
      '#title' => $this->t('Shoe'),
      '#open' => TRUE,
    ];
    $form['shoe']['decks'] = [
      '#type' => 'number',
      '#title' => $this->t('Decks'),
      '#min' => 1,
      '#max' => 100,
      '#description' => $this->t('The amount of decks to add to each shoe.'),
      '#required' => TRUE,
      '#default_value' => $config->get('shoe.decks'),
    ];
    $form['shoe']['penetration'] = [
      '#type' => 'number',
      '#title' => $this->t('Penetration'),
      '#min' => .1,
      '#max' => 1,
      '#step' => .01,
      '#description' => $this->t('How deep to go in to each shoe. Can be a value between 0.1 and 1.'),
      '#required' => TRUE,
      '#default_value' => $config->get('shoe.penetration'),
    ];

    // Results settings.
    $form['results'] = [
      '#type' => 'details',
      '#title' => $this->t('Results'),
      '#open' => TRUE,
    ];
    $form['results']['output_hands'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Output all hands'),
      '#default_value' => $config->get('results.output_hands'),
      '#description' => $this->t('@todo This has not yet been implemented.'),
    ];

    // Submit buttons.
    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#submit' => ['::save'],
    ];
    $form['run'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and run simulation'),
      '#submit' => ['::save', '::run'],
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Nothing needed here.
  }

  /**
   * Submit callback to save the settings.
   */
  public function save(array &$form, FormStateInterface $form_state) {
    $this->config('blackjack.settings')
      ->set('shoe.decks', $form_state->getValue('decks'))
      ->set('shoe.penetration', $form_state->getValue('penetration'))
      ->set('game.blackjack_payout', $form_state->getValue('blackjack_payout'))
      ->set('game.max_hands_per_game', $form_state->getValue('max_hands_per_game'))
      ->set('game.bet_min', $form_state->getValue('bet_min'))
      ->set('game.bet_max', $form_state->getValue('bet_max'))
      ->set('player.strategy', $form_state->getValue('strategy'))
      ->set('player.bankroll', $form_state->getValue('bankroll'))
      ->set('player.spread', $form_state->getValue('spread'))
      ->set('player.max_games', $form_state->getValue('max_games'))
      ->set('dealer.hit_soft_17', $form_state->getValue('hit_soft_17'))
      ->set('results.output_hands', $form_state->getValue('output_hands'))
      ->save();
  }

  /**
   * Submit callback to run the simulator.
   */
  public function run(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('blackjack.simulator');
  }

}
