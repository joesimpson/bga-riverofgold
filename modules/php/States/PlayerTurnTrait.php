<?php

namespace ROG\States;

use Bga\GameFramework\Actions\Types\IntArrayParam;
use Bga\GameFramework\Actions\Types\JsonParam;
use Bga\GameFramework\States\PossibleAction;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\ClientAnswer;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;
use ROG\Models\BEFORE_ACTION;
use ROG\Models\Player;

trait PlayerTurnTrait
{
   
  public function argPlayerTurn()
  { 
    $actions = [];
    
    $activePlayer = Players::getActive();
    if(count($this->listPossibleDieFacesToBuy($activePlayer))>0 ){
      $actions[] = 'actSpendFavor';
    }
    if(count($this->listPossibleTrades($activePlayer))>0 ){
      $actions[] = 'actTrade';
    }
    if(count($this->listPossibleSpacesToBuild($activePlayer))>0 ){
      $actions[] = 'actBuild';
    }
    //Sail always possible
    $actions[] = 'actSail';
    $argDeliver = $this->argDeliver()['_private'][$activePlayer->getId()];
    if(count($argDeliver['c'])>0 
    || count($argDeliver['canReplaceGoods'])>0 && count($argDeliver['canReplaceGoods']['cards'])>0
    ){
      $actions[] = 'actDeliver';
    }
    $playableCards = $this->listPossibleCardsToPlay($activePlayer);
    if(count($playableCards)>0 ){
      $actions[] = 'actPlayCard';
    }
    $die_face = $activePlayer->getDie();
    $args = [
      'a' => $actions,
      'die_face' => $die_face,
      'p_cards' => $playableCards,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 

  public function actSpendFavor()
  { 
    self::checkAction('actSpendFavor'); 
    self::trace("actSpendFavor()");

    $this->addStep();

    $this->gamestate->nextState('favor');
  }

  public function actTrade()
  { 
    self::checkAction('actTrade'); 
    self::trace("actTrade()");

    $this->addStep();
    $currentState = $this->gamestate->getCurrentMainStateId();
    Globals::setStateBeforeTrade($currentState);

    $this->gamestate->nextState('trade');
  }

  public function actBuild()
  { 
    self::checkAction('actBuild'); 
    self::trace("actBuild()");

    $this->addStep();

    $this->gamestate->nextState('build');
  }

  public function actSail()
  { 
    self::checkAction('actSail'); 
    self::trace("actSail()");
    $this->addStep();

    $this->gamestate->nextState('sail');
  }
  
  public function actDeliver()
  { 
    self::checkAction('actDeliver'); 
    self::trace("actDeliver()");
    $this->addStep();

    $this->gamestate->nextState('deliver');
  }

  #[PossibleAction]
  public function actPlayCard(
    #[JsonParam] ClientAnswer $answer,
  )
  { 
    self::trace("actPlayCard(".json_encode($answer).")");
    $player = Players::getCurrent();
    $this->addStep();

    $cardId = $answer->cardId;
    $markerId = $answer->markerId;
    $action = $answer->action;
    $source = $answer->source;
    $dest = $answer->dest;
    
    $args = $this->argPlayerTurn();
    $possibleCards = $args['p_cards'];
    if(!in_array($cardId, array_keys($possibleCards))){
      throw new UnexpectedException(45,"You cannot play card $cardId");
    }
    $cardPlayDatas = $possibleCards[$cardId];
    $possibleMarker = $cardPlayDatas['marker'];
    if($possibleMarker != $markerId){
      throw new UnexpectedException(45,"You cannot play card $cardId with marker $markerId");
    }
    $possibleActions = $cardPlayDatas['actions'];
    if(!in_array($action, array_keys($possibleActions))){
      throw new UnexpectedException(45,"You cannot play card $cardId with Action ".json_encode($action).", see ".json_encode(array_keys($possibleActions)));
    }
    $actionDatas = $possibleActions[$action];
    
    $customer = Cards::get($cardId);
    Notifications::playCustomerAbility($player,$customer);
    
    switch($action){
      case BEFORE_ACTION::SWAP_BOATS->value: 
        $sourceShips = $actionDatas['source'];
        $destShips = $actionDatas['dest'];
        if(!in_array($source, $sourceShips)){
          throw new UnexpectedException(45,"You cannot swap ships from $source");
        }
        if(!in_array($dest, $destShips)){
          throw new UnexpectedException(45,"You cannot swap ships from $source to $dest");
        }
        Meeples::removeClanMarkerById($player,$markerId);
        $ship1 = Meeples::get($source);
        $ship2 = Meeples::get($dest);
        $sourcePosition = $ship1->getPosition();
        $destPosition = $ship2->getPosition();
        if($sourcePosition == $destPosition){
          throw new UnexpectedException(45,"You cannot swap ships in the same space");
        }
        $ship2Owner = Players::get($ship2->getPId());
        $ship1->setPosition($destPosition);
        $ship2->setPosition($sourcePosition);
        Notifications::swapBoats($player,$ship1, $ship2Owner,$ship2);

        break;
      case BEFORE_ACTION::MOVE_BUILDING->value: 
        $sourceTiles = $actionDatas['tiles'];
        $destSpaces = $actionDatas['spaces'];
        if(!in_array($source, $sourceTiles)){
          throw new UnexpectedException(46,"You cannot move tile $source");
        }
        if(!in_array($dest, $destSpaces)){
          throw new UnexpectedException(46,"You cannot move tile $source to $dest");
        }
        Meeples::removeClanMarkerById($player,$markerId);
        $tile = Tiles::get($source);
        $previousLocation = $tile->getLocation();
        $previousPosition = $tile->getPosition();
        $tile->setLocation(TILE_LOCATION_BUILDING_SHORE);
        $tile->setPosition($dest);
        Notifications::moveBuilding($player,$tile,$previousPosition,$previousLocation);
        Players::claimMasteriesSubset($player,[MASTERY_TYPE_WAVES]);
        break;
    }

    //stay in this state 
    $this->gamestate->nextState('continue');
  }

  function stConfirmChoices()
  {
    $this->gamestate->nextState('');
  }

  
  /**
   * Go to bonus transition after current turn action
   * @param Player $player
   * @param bool $changeActivePlayer (Default false) 
   * @return bool true if state changed
   */
  function goToBonusStepIfNeeded($player, $changeActivePlayer = false)
  {
    if(!isset($player)) return false;
    //refresh datas
    $player = Players::get($player->getId());
    $bonuses = $player->getBonuses();
    if(isset($bonuses) && count($bonuses)>0){
      $player->giveExtraTime();
      if($changeActivePlayer){
        //Change active player when in a game state !
        Players::changeActive($player->getId());
        $this->addCheckpoint(ST_BONUS_CHOICE);
      }
      $this->gamestate->nextState('bonus');
      return true;
    }
    return false;
  }

  /**
   * @return array [ cardId => ['marker' =>1, 'actions' => [Action1 => datas1, Action2 =>datas2]], cardId => ['marker' =>2, 'actions' => [Action3=>datas3, ]], ]
   */
  function listPossibleCardsToPlay(Player $player) : array{
    $cards = [];
    $mainActionDone = Globals::getTurnMainActionDone();
    $mainActionDone = isset($mainActionDone) && $mainActionDone !='null' && $mainActionDone !='';
    if(!$mainActionDone){
      //ACTIONS to take BEFORE main action :
      $shindoshi4 = Utils::getShindoshiMarker($player->getId(),CARD_SHINDOSHI_4);
      if(isset($shindoshi4)){
        $cardId = $shindoshi4->getCardId();
        $cards[$cardId]['marker'] = $shindoshi4->getId();
        $cards[$cardId]['actions'] = [];
        $playDatas = [];
        $playDatas['source'] = Meeples::getBoats($player->getId())->getIds();
        $playDatas['dest'] = Meeples::getBoats(null)->getIds();
        $cards[$cardId]['actions'][BEFORE_ACTION::SWAP_BOATS->value] = $playDatas;
      }
      $shindoshi5 = Utils::getShindoshiMarker($player->getId(),CARD_SHINDOSHI_5);
      if(isset($shindoshi5)){
        $cardId = $shindoshi5->getCardId();
        $cards[$cardId]['marker'] = $shindoshi5->getId();
        $cards[$cardId]['actions'] = [];
        $playDatas = [];
        $playDatas['tiles'] = Tiles::getPlayerBuildingTilesIds($player->getId());
        $playDatas['spaces'] = ShoreSpaces::getAllEmptySpaces();
        if(count($playDatas['tiles'])> 0 && count($playDatas['spaces'])> 0 ){
          $cards[$cardId]['actions'][BEFORE_ACTION::MOVE_BUILDING->value] = $playDatas;
        }
      }
    }
    return $cards;
  }
}
