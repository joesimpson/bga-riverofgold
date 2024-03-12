<?php

namespace ROG\States;

use ROG\Managers\Players;

trait ClanSelectionTrait
{
   
  public function stClanSelection()
  { 
    self::trace("stClanSelection()");

    //TODO JSA when expansion -> go to a activeplayer state to select OR random clan

    //give clan colors to players 
    $players = Players::getAll();
    $k = 0;
    foreach($players as $player){
      $player->setClan(CLANS_COLORS[$player->getColor()]);
      $k++;
    }

    $this->gamestate->nextState('next');
  }
}
