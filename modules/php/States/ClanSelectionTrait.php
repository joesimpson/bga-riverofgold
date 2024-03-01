<?php

namespace ROG\States;

trait ClanSelectionTrait
{
   
  public function stClanSelection()
  { 
    self::trace("stClanSelection()");

    //TODO JSA when expansion -> go to a activeplayer state to select OR random clan

    //TODO JSA give clan colors to players 

    $this->gamestate->nextState('next');
  }
}
