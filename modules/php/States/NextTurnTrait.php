<?php

namespace ROG\States;

trait NextTurnTrait
{
   
  public function stNextTurn()
  { 
    self::trace("stNextTurn()");

    //TODO JSA new turn

    $this->gamestate->nextState('next');
  }
}
