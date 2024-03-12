<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Managers\Players;

trait BeforeTurnTrait
{
   
  public function stBeforeTurn()
  { 
    self::trace("stBeforeTurn()");

    $this->addCheckpoint(ST_PLAYER_TURN);
    $this->gamestate->nextState('next');
  }
}
