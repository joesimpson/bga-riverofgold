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

    //give clan colors to players 
    $players = Players::getAll();
    $k = 0;
    foreach($players as $player){
      $clan_id = CLANS_COLORS[$player->getColor()];
      $player->setClan($clan_id);
      $k++;
        
      if(Globals::isExpansionClansAlternative()){
        Cards::initClanPatronsAlternative($player, $clan_id);
      }
    }
    //TODO JSA notify clan (for waiting screen)

    if(Globals::isExpansionClansAlternative()){
      //Alternative setup : clan is random at start, but player can choose the patron card
      $this->gamestate->nextState('draftMulti');
      return;
    }

    $this->gamestate->nextState('next');
  }
   
}
