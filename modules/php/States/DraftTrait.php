<?php

namespace ROG\States;

use Bga\GameFramework\Actions\Types\IntParam;
use Bga\GameFramework\States\PossibleAction;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Players;
use ROG\Models\ClanPatronCard;
use ROG\Models\Player;

trait DraftTrait
{
   
  public function argDraft()
  { 
    $cards = Cards::getInLocation(CARD_CLAN_LOCATION_DRAFT);
    $scenarios = Cards::getInLocation(CARD_SCENARIO_LOCATION_DRAFT);
    $args = [
      'cards' => $cards->ui(),
      'scenarios' => $scenarios->uiAssoc(),
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
  #[PossibleAction]
  function actTakeCard(
    #[IntParam(name: 'c')] int $cardId,
    int $version,
    #[IntParam(name: 'sc')] int|null $scenarioCardId = null,
  ){
    $this->checkVersion($version);
    self::checkAction( 'actTakeCard' ); 
    self::trace("actTakeCard($cardId,$scenarioCardId)");

    $args = $this->argDraft();
    
    $card = Cards::get($cardId);
    $player = Players::getCurrent();
    $isModeMultiActive =  ST_DRAFT_PLAYER_MULTIACTIVE == intval($this->gamestate->getCurrentMainStateId());

    //ANTICHEAT :
    if($card->getLocation() != CARD_CLAN_LOCATION_DRAFT){
      throw new UnexpectedException(405,"Card $cardId is not selectable");
    }
    if( $isModeMultiActive && $card->getPId() != $player->getId()){
      throw new UnexpectedException(406,"Card $cardId is not selectable");
    }

    $this->assignClanPatron($player,$card);
    
    $possibleScenarios = array_keys($args['scenarios']);
    if(isset($scenarioCardId) ){
      if(!in_array($scenarioCardId,$possibleScenarios)){
        throw new UnexpectedException(407,"Scenario $scenarioCardId is not selectable");
      }
      $scenarioCard = Cards::get($scenarioCardId);
      if($scenarioCard->getClan() !== $card->getClan() ){
        throw new UnexpectedException(408,"Scenario $scenarioCardId must match patron clan");
      }
      Cards::assignScenario($player,$scenarioCard);
    }

    if( $isModeMultiActive){
      $this->gamestate->setPlayerNonMultiactive($player->getId(), 'next');
      return;
    }
    //ELSE classic ST_DRAFT_PLAYER is expected
    $this->gamestate->nextState('next');
  }
  
  /**
   * @param Player $player
   * @param ClanPatronCard $card
   */
  function assignClanPatron(Player $player,ClanPatronCard $card){
    $player->setClan($card->getClan());
    $player_color = Utils::getPlayerClanColor($card->getClan());
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
