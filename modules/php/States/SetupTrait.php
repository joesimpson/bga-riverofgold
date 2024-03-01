<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Preferences;
use ROG\Core\Stats;
use ROG\Managers\Cards;
use ROG\Managers\Players;

trait SetupTrait
{
  
  /*
      setupNewGame:
      
      This method is called only once, when a new game is launched.
      In this method, you must setup the game according to the game rules, so that
      the game is ready to be played.
  */
  protected function setupNewGame($players, $options = [])
  {
    $playersDatas = Players::setupNewGame($players, $options);
    Globals::setupNewGame($players, $options);
    Preferences::setupNewGame($players, $this->player_preferences);
    Stats::setupNewGame($playersDatas);
    Cards::setupNewGame($players,$options);

    $this->setGameStateInitialValue('logging', true); 

    // Activate first player (which is in general a good idea :) )
    $this->activeNextPlayer();
    /************ End of the game initialization *****/
  }
}
