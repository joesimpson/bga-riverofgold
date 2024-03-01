<?php

namespace ROG\States;

trait BeforeTurnTrait
{
   
  public function stBeforeTurn()
  { 
    self::trace("stBeforeTurn()");

    //TODO JSA darling favor

    $this->gamestate->nextState('next');
  }
}
