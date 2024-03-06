<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Managers\Players;

trait NextTurnTrait
{
   
  public function stNextTurn()
  { 
    self::trace("stNextTurn()");

    //TODO JSA new turn -> next era
    Globals::setEra(1);
    Globals::setupNewTurn();
    $turn = Globals::getTurn();
    if($turn==1){
      $playerId = Globals::getFirstPlayer();
      $nextPlayer = Players::get($playerId);
    }
    else {
      $activePlayer = Players::getActive();
      $nextPlayer = Players::getNextPlayerNotEliminated($activePlayer->id);
    }
    Players::changeActive($nextPlayer->id);
    $nextPlayer->giveExtraTime();

    $this->addCheckpoint(ST_BEFORE_TURN);
    $this->gamestate->nextState('next');
  }
}
