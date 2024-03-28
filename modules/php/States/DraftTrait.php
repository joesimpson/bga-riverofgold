<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
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
    $nbDraftCards = Cards::countInLocation(CARD_CLAN_LOCATION_DRAFT);
    if($nbDraftCards == 1){
      //auto assign last card (possible in a 4p game)
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

    //ANTICHEAT :
    if($card->getLocation() != CARD_CLAN_LOCATION_DRAFT){
      throw new UnexpectedException(405,"Card $cardId is not selectable");
    }

    $player = Players::getActive();
    $this->assignClanPatron($player,$card);

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
  }
}
