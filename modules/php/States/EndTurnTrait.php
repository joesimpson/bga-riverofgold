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
    //TODO JSA darling favor may act before rolling die
    $activePlayer = Players::getActive();
    $activePlayer->rollDie();

    $this->addCheckpoint(ST_NEXT_TURN);
    $this->gamestate->nextState('next');
  }
}
