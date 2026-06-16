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
    $cardsDatas = $cards->map(function ($card) use ($scenarios) {
            $datas = $card->getUiData();
            $datas['scenario_ids'] = $scenarios->filter(function ($sc) use ($card) {
              return $card->getClan() == $sc->getClan(); 
            })->getIds();
            return $datas;
        })->toAssoc();

    $args = [
      'cards' => $cardsDatas,
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
    
    $player = Players::getCurrent();
    $isModeMultiActive =  ST_DRAFT_PLAYER_MULTIACTIVE == intval($this->gamestate->getCurrentMainStateId());

    if( $isModeMultiActive ) {
      $args = $this->argDraftMulti();
      $possibleCards = $args['_private'][$player->getId()]['cards'];
    }
    else {
      $args = $this->argDraft();
      $possibleCards = $args['cards'];
    }

    //ANTICHEAT :
    if(!in_array($cardId,array_keys($possibleCards))){
      throw new UnexpectedException(406,"Card $cardId is not selectable");
    }

    $cardDatas = $possibleCards[$cardId];
    //assign scenario before patron
    $possibleScenarios = $cardDatas['scenario_ids'];
    if(isset($scenarioCardId) ){
      if(!in_array($scenarioCardId,$possibleScenarios)){
        throw new UnexpectedException(407,"Scenario $scenarioCardId is not selectable");
      }
      $scenarioCard = Cards::get($scenarioCardId);
      Cards::assignScenario($player,$scenarioCard);
    }
    else {
      if(count($possibleScenarios) > 0){
        throw new UnexpectedException(409,"You need to select a Scenario for clan patron $cardId");
      }
    }

    $card = Cards::get($cardId);
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
    $scenarios = Cards::getInLocation(CARD_SCENARIO_LOCATION_DRAFT);

    foreach($players as $player_id => $player){
      $cardsDatas = $cards->filter( function($card) use($player_id) { return $card->getPId() == $player_id;} )
        ->map(function ($card) use ($scenarios) {
            $datas = $card->getUiData();
            $datas['scenario_ids'] = $scenarios->filter(function ($sc) use ($card) {
              return $card->getClan() == $sc->getClan(); 
            })->getIds();
            return $datas;
        })->toAssoc();

      $privateDatas[$player_id] = [
        'cards' => $cardsDatas,
      ];
    }

    $args = [
      '_private' => $privateDatas,
      'scenarios' => $scenarios->uiAssoc(),
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
