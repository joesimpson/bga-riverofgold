<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Managers\Cards;
use ROG\Managers\Players;

trait ClanSelectionTrait
{

  public function stClanSelection()
  { 
    self::trace("stClanSelection()");

    if(Globals::isExpansionClansDraft()){
      //ACTIVATE next COUNTER CLOCKWISE player (because active player is currently the first player)
      $player_id = $this->activePrevPlayer();
      self::giveExtraTime( $player_id );
      Cards::initClanPatronsDraft();
      $this->gamestate->nextState('draft');
      return;
    }
    //TODO JSA Alternative setup

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
