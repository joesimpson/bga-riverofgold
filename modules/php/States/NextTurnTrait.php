<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Managers\Players;

trait NextTurnTrait
{
   
  public function stNextTurn()
  { 
    self::trace("stNextTurn()");

    if(Players::everyOnePlayedLastTurn()){
      //END GAME TRIGGER
      Notifications::triggerEnd();
      $this->addCheckpoint(ST_END_SCORING);
      $this->gamestate->nextState('end');
      return;
    }

    Globals::setupNewTurn();
    $turn = Globals::getTurn();
    if($turn==1){
      $playerId = Globals::getFirstPlayer();
      $nextPlayer = Players::get($playerId);
    }
    else {
      //$activePlayer = Players::getActive();
      //Current active player is not always the player whose turn ended because of bonuses choice
      $activePlayerId = Globals::getTurnPlayer();
      $nextPlayer = Players::getNextPlayerNotEliminated($activePlayerId);
    }
    Players::changeActive($nextPlayer->id);
    $nextPlayer->giveExtraTime();
    Globals::setTurnPlayer($nextPlayer->id);

    $this->addCheckpoint(ST_BEFORE_TURN);
    $this->gamestate->nextState('next');
  }
}
