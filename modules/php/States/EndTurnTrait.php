<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Managers\Players;
use ROG\Managers\Tiles;

trait EndTurnTrait
{
   
  public function stEndTurn()
  { 
    self::trace("stEndTurn()");
    $this->addCheckpoint(ST_END_TURN);

    Tiles::refillBuildingRow();

    //TODO JSA run Emperor Visit at end of Era 1

    //RULE : roll your die at the end of your turn, before others play
    $activePlayer = Players::getActive();
    $playerPatron = $activePlayer->getPatron();
    if(isset($playerPatron) && PATRON_DARLING == $playerPatron->getType()){
      //TODO JSA darling may decide to not roll the die
      $activePlayer->rollDie();
    }
    else {
      $activePlayer->rollDie();
    }
    
    $this->addCheckpoint(ST_NEXT_TURN);
    $this->gamestate->nextState('next');
  }
}
