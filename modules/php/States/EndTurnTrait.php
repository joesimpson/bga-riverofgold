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


    $this->addCheckpoint(ST_NEXT_TURN);
    $this->gamestate->nextState('next');
  }
}
