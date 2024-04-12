<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Cards;
use ROG\Managers\Players;

trait DraftTrait
{
   
  public function argDraft()
  { 
    $cards = Cards::getInLocation(CARD_CLAN_LOCATION_DRAFT);
    $args = [
      'cards' => $cards->ui(),
    ];
    return $args;
  }
  
  function stDraftNextPlayer()
  {
    // Active previous player (COUNTER CLOCKWISE )
    $player_id = $this->activePrevPlayer();
    self::giveExtraTime( $player_id );

    //END DRAFT CONDITIONS
    $nbPlayers = Players::count();
    $nbDraftCards = Cards::countInLocation(CARD_CLAN_LOCATION_DRAFT);
    $nbCardsToAssign = $nbPlayers - Cards::countInLocation(CARD_CLAN_LOCATION_ASSIGNED);
    if($nbCardsToAssign < 1){
      //When everyone has a card
      $this->gamestate->nextState('end');
      return;
    }
    else if($nbDraftCards == 1 && $nbCardsToAssign == 1){
      //auto assign last card (possible in a 4p game, but not 2p/3p)
      $card = Cards::getTopOf(CARD_CLAN_LOCATION_DRAFT);
      $player = Players::getActive();
      $this->assignClanPatron($player,$card);
      $this->gamestate->nextState('end');
      return;
    }

    $this->gamestate->nextState('next');
  }
  
  /**
   * USer action
   * @param int $cardId
   */
  function actTakeCard($cardId){
    self::checkAction( 'actTakeCard' ); 
    self::trace("actTakeCard($cardId)");
    
    $card = Cards::get($cardId);
    $player = Players::getCurrent();
    $isModeMultiActive =  ST_DRAFT_PLAYER_MULTIACTIVE == intval($this->gamestate->state_id());

    //ANTICHEAT :
    if($card->getLocation() != CARD_CLAN_LOCATION_DRAFT){
      throw new UnexpectedException(405,"Card $cardId is not selectable");
    }
    if( $isModeMultiActive && $card->getPId() != $player->getId()){
      throw new UnexpectedException(406,"Card $cardId is not selectable");
    }

    $this->assignClanPatron($player,$card);

    if( $isModeMultiActive){
      $this->gamestate->setPlayerNonMultiactive($player->getId(), 'next');
      return;
    }
    //ELSE classic ST_DRAFT_PLAYER is expected
    $this->gamestate->nextState('next');
  }
  
  /**
   * @param Player $player
   * @param Card $card
   */
  function assignClanPatron($player,$card){
    $player->setClan($card->getClan());
    $player_color = array_search($card->getClan(),CLANS_COLORS);
    $player->setColor($player_color);
    self::reloadPlayersBasicInfos();
    Notifications::newPlayerColor($player);
    Cards::giveClanCardTo($player,$card);
    Stats::set("playedClan",$player,$card->getClan());
    Stats::set("playedClanPatron",$player,$card->getType());
  }

  //////////////////////////////////////////////////////////////
  // MULTIACTIVE VERSION
  //////////////////////////////////////////////////////////////

  public function argDraftMulti()
  { 
    $privateDatas = array ();
    $players = Players::getAll();

    $cards = Cards::getInLocation(CARD_CLAN_LOCATION_DRAFT);
    
    foreach($players as $player_id => $player){
      $privateDatas[$player_id] = [
        'cards' => $cards->filter( function($card) use($player_id) { return $card->getPId() == $player_id;} )->ui(),
      ];
    }

    $args = [
      '_private' => $privateDatas,
    ];
    return $args;
  }

  /**
   * Activation of everyone for clan selection
   */
  function stDraftMulti()
  { 
      self::trace("stDraftMulti()");
      /*  Moved before state start in order to have right activity status on UI when entering state
      $this->gamestate->setAllPlayersMultiactive();
      $players = Players::getAll();
      foreach($players as $player_id => $player){ 
        $player->giveExtraTime();
      }
      */
  }
}
