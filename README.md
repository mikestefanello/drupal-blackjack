# Blackjack Simulation Engine

----
## Intro
This is a blackjack simulation engine with pluggable strategies written as a Drupal 8 module.

## Usage
Install the module as you would any other. There are no module or library dependencies.

#### Web
Navigate to /blackjack to configure and run the simulation.

#### Drush
Execute *drush blackjack* to run the simulation and output the results.

**Example output:**

```
 Games                  500            
 Min. bet               10             
 Max. bet               5000           
 Shuffles               12             
 Starting bankroll      500.00         
 Bankroll               280 (56.00%)   
 Change                 -220 (-44.00%) 
 Hands                  509            
 Wins                   222 (43.61%)   
 Loses                  245 (48.13%)   
 Pushes                 42 (8.25%)     
 Doubles                58 (11.39%)    
 Splits                 8.5 (1.67%)    
 Blackjacks             22 (4.32%)     
 Busts                  72 (14.15%)    
 Strategy               Ace Five       
 Bets in count          95             
 Bets out of count      405            
 Highest count          6              
 Lowest count           -7             
 Bet spread             3              
 Dealer blackjacks      23             
 Dealer busts           126            
 Dealer hit on soft 17  Yes
```

## Strategies
Strategies are plugins located within src/Plugin/BlackjackStrategy. Extend BlackjackStrategyBase to implement your own.
