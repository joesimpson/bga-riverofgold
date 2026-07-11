<?php

namespace ROG\States;

use Bga\GameFramework\Actions\Types\IntArrayParam;
use Bga\GameFramework\Actions\Types\JsonParam;
use Bga\GameFramework\States\PossibleAction;
use Bga\Games\RiverOfGoldNightMarket\States\AdvanceCity;
use Bga\Games\RiverOfGoldNightMarket\States\BonusBuildingReward;
use ROG\Models\BonusBuildingRewardChoice;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\ClientAnswer;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\CityCards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;
use ROG\Models\AFTER_ACTION;
use ROG\Models\BEFORE_ACTION;
use ROG\Models\CityCard;
use ROG\Models\CustomerCard;
use ROG\Models\Player;
use ROG\Models\ScenarioCard;
use ROG\Models\TURN_ACTION;

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
    
    if(Utils::isGameWithCityOfLies() 
      && count(AdvanceCity::listPossibleSpacesToAdvance($activePlayer))>0 
    ){
      $actions[] = 'actAdvance';
    }
    $die_face = $activePlayer->getDie();
    $args = [
      'a' => $actions,
      'die_face' => $die_face,
    ];
    $this->addArgsForPlayCards($args,$activePlayer);
    $this->addArgsForUndo($args);
    return $args;
  } 

  #[PossibleAction]
  public function actSpendFavor(int $version)
  { 
    $this->checkVersion($version);
    self::checkAction('actSpendFavor'); 
    self::trace("actSpendFavor()");

    $this->addStep();

    $this->gamestate->nextState('favor');
  }

  #[PossibleAction]
  public function actTrade(int $version)
  { 
    $this->checkVersion($version);
    self::checkAction('actTrade'); 
    self::trace("actTrade()");

    $this->addStep();
    $currentState = $this->gamestate->getCurrentMainStateId();
    Globals::setStateBeforeTrade($currentState);

    $this->gamestate->nextState('trade');
  }

  #[PossibleAction]
  public function actBuild(int $version)
  { 
    $this->checkVersion($version);
    self::checkAction('actBuild'); 
    self::trace("actBuild()");

    $this->addStep();

    $this->gamestate->nextState('build');
  }

  #[PossibleAction]
  public function actSail(int $version)
  { 
    $this->checkVersion($version);
    self::checkAction('actSail'); 
    self::trace("actSail()");
    $this->addStep();

    $this->gamestate->nextState('sail');
  }
  
  #[PossibleAction]
  public function actDeliver(int $version)
  { 
    $this->checkVersion($version);
    self::checkAction('actDeliver'); 
    self::trace("actDeliver()");
    $this->addStep();

    $this->gamestate->nextState('deliver');
  }
  
  #[PossibleAction]
  public function actAdvance(int $version)
  { 
    $this->checkVersion($version);
    self::checkAction('actAdvance'); 
    self::trace("actAdvance()");
    $this->addStep();

    $this->gamestate->nextState('advance');
  }

  #[PossibleAction]
  public function actPlayCard(
    #[JsonParam] ClientAnswer $answer,
    int $version,
  )
  { 
    $this->checkVersion($version);
    self::trace("actPlayCard(".json_encode($answer).")");
    $player = Players::getCurrent();
    $this->addStep();

    $cardId = $answer->cardId;
    $markerId = $answer->markerId;
    $action = $answer->action;
    $source = $answer->source;
    $dest = $answer->dest;
    
    $args = $this->argPlayerTurn();
    $possibleCards = isset($args['p_cards']) ? $args['p_cards'] : [];
    $privateArgs = isset($args['_private']) ? $args['_private'][$player->getId()] : [];
    if(isset($privateArgs['p_cards'])){
      $possiblePrivateCards = $privateArgs['p_cards'];
      foreach($possiblePrivateCards as $i => $d){
        $possibleCards[$i] = $d;
      }
    }
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
    
    $doCheckPoint = false;
    $card = Cards::get($cardId);
    $card->setPlayed(true);
    if($card instanceof CustomerCard){
      Notifications::playCustomerAbility($player,$card);
    }
    else if($card instanceof ScenarioCard){
      Notifications::scenarioAbility($player,$card);
    }
    else if($card instanceof CityCard){
      $card->reveal($player,);
    }
    
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
        Meeples::removeResourcesOnShoreSpace(ShoreSpaces::getShoreSpace($dest),$player, false);
        Players::claimMasteriesSubset($player,[MASTERY_TYPE_WAVES]);
        break;
      case TURN_ACTION::DIVINE_CYCLING->value:
        $player->giveResource(-1,RESOURCE_TYPE_SUN);
        $masteryDeckSize = Tiles::countMasteriesInDeck();
        $oldMastery = Tiles::getTopOf(TILE_LOCATION_MASTERY_CARD);
        Tiles::insertAtBottom($oldMastery->getId(),TILE_LOCATION_MASTERY_DECK);
        Notifications::masteryBottom($player, $oldMastery,);
        $mastery = Tiles::pickOneForLocation(TILE_LOCATION_MASTERY_DECK,TILE_LOCATION_MASTERY_CARD);
        Notifications::masteryDeck($masteryDeckSize,$mastery,);
        //try to claim next mastery (and cascade...)
        Players::claimMasteries($player);
        $doCheckPoint = true;
        break;
      case BEFORE_ACTION::GAIN_INFLUENCE->value:
        $destRegions = $actionDatas['regions'];
        if(!in_array($dest, $destRegions)){
          throw new UnexpectedException(48,"You cannot gain influence in region $dest");
        }
        $region = $dest;
        $amount = $actionDatas['n'];
        Players::gainInfluence($player,$region,$amount);
        //We may go to bonus choice if needed BEFORE MAIN ACTION 
        if(Utils::isPlayerActionDone()){
          Players::claimMasteries($player);
        }
        break;
      case AFTER_ACTION::GAIN_INFLUENCE->value:
        $destRegions = $actionDatas['regions'];
        if(!in_array($dest, $destRegions)){
          throw new UnexpectedException(48,"You cannot gain influence in region $dest");
        }
        $bonusDatas = Globals::removeBonus($player,$source,$markerId);
        self::trace("actPlayCard(CityCard $cardId) ... bonusDatas=".json_encode($bonusDatas).")");
        if(!isset($bonusDatas)){
          throw new UnexpectedException(47,"Missing informations to gain influence with card $cardId");
        }
        $region = $dest;
        $amount = $actionDatas['n'];
        Players::gainInfluence($player,$region,$amount);
        Players::claimMasteries($player);
        break;
      case AFTER_ACTION::BUILDING_REWARD->value:
        $bonusDatas = Globals::removeBonus($player,$source,$markerId);
        self::trace("actPlayCard(CityCard $cardId) ... bonusDatas=".json_encode($bonusDatas).")");
        if(!isset($bonusDatas)){
          throw new UnexpectedException(47,"Missing informations with card $cardId");
        }
        $tileId = $answer->tileId;
        $possibleTiles = $actionDatas['tiles'];
        if(!array_key_exists($tileId, $possibleTiles)){
          throw new UnexpectedException(48,"You cannot gain reward for tile $tileId");
        }
        $tileDatas = $possibleTiles[$tileId];
        $rewardChoice = $dest;
        $possibleRewards = $tileDatas['choices'];
        if(!in_array($rewardChoice, $possibleRewards)){
          throw new UnexpectedException(48,"You cannot gain reward $rewardChoice for tile $tileId");
        }
        BonusBuildingReward::processBuildingReward($player,BonusBuildingRewardChoice::from($rewardChoice), $tileId);
        break;
      case BEFORE_ACTION::TRADE_FOR_RESOURCES->value:
        $trades = $actionDatas['trades'];
        // MULTIPLES TRADES AT ONCE to keep card secret (and because we don't know how many we will do)
        $wantedResources = $answer->res;
        if(!isset($wantedResources)){
          throw new UnexpectedException(404,"Missing resources");
        }
        $checkresources = [ 
          RESOURCE_TYPE_SILK    => $wantedResources->silk, 
          RESOURCE_TYPE_POTTERY => $wantedResources->pottery, 
          RESOURCE_TYPE_RICE    => $wantedResources->rice, 
          RESOURCE_TYPE_MONEY   => $wantedResources->money, 
        ];
        $balanceResources = 0;
        $totalChange = 0;
        foreach($checkresources as $res_type => $amount){
          $tradeDatas = $trades[$res_type];
          if($tradeDatas['max'] < $amount){
            throw new UnexpectedException(503,"Invalid amount of $res_type : $amount, see max in ".json_encode($tradeDatas));
          }
          if(intval($tradeDatas['min']) > intval($amount)){
            throw new UnexpectedException(503,"Invalid amount of $res_type : $amount, see min in ".json_encode($tradeDatas));
          }
          $current = $player->getResource($res_type);
          $quantity = $amount - $current;
          $player->giveResource($quantity, $res_type);
          $totalChange += abs($quantity);
          $balanceResources += $quantity / $tradeDatas['delta'];
        }
        if($totalChange == 0){
          throw new UnexpectedException(503,"Don't use this card if you don't change your resources !");
        }
        if($balanceResources != 0){
          //We want a 0 balance because we exchange a*x to b*y
          throw new UnexpectedException(503,"Invalid balance of resources : $balanceResources");
        }
        break;
    }

    if($doCheckPoint){
      $this->addCheckpoint($this->gamestate->getCurrentMainStateId());
    }
    if($this->goToBonusStepIfNeeded($player)) return;
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
    return Utils::goToBonusStepIfNeeded($player, $changeActivePlayer);
  }

  /**
   * @return array [ cardId => ['marker' =>1, 'actions' => [Action1 => datas1, Action2 =>datas2]], cardId => ['marker' =>2, 'actions' => [Action3=>datas3, ]], ]
   */
  function listPossibleCardsToPlay(Player $player) : array{
    $cards = [];
    $mainActionDone = Utils::isPlayerActionDone();
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
      
    $playerScenario = $player->getScenario();
    if(isset($playerScenario)) {
      $cardId = $playerScenario->getId();
      $actions = $playerScenario->listPossibleActions($player);
      if(count($actions) > 0){
        $cards[$cardId]['marker'] = null;
        $cards[$cardId]['actions'] = $actions;
      }
    }
    return $cards;
  }

  
  function listPossiblePrivateCardsToPlay(Player $player) : array{
    $cards = [];

    $privateBonuses = $player->filterPossibleBonuses(false);
    if(isset($privateBonuses['datas'])){
      $privateBonuses = $privateBonuses['datas'];
      foreach($privateBonuses as $type => $list){
        foreach($list as $key => $data){
          switch($type){
            case BONUS_TYPE_REVEAL_CARD: 
              $cardId = $data['cardId'];
              $cards[$cardId]['marker'] = $key;
              $cards[$cardId]['source'] = $type;
              $cards[$cardId]['actions'] = $data['actions'];
              break;
          }
        }
      } 
    }
    
    $cityCards = CityCards::getPlayerHand($player->getId());
    $cityCards->map(function(CityCard $c) use (&$player, &$cards){
        $playableDatas = $c->playCardDatasOnTurn($player,);
        if(count($playableDatas) > 0){
          $cards[$c->getId()] = $playableDatas;
        }
      });

    return $cards;
  }

  /**
   * Update state args with some parameters used for playing cards actions. 
   * 
   */
  public function addArgsForPlayCards(array &$args, Player $activePlayer)
  {
    $turnPlayerId = Globals::getTurnPlayer();
    if($activePlayer->getId() != $turnPlayerId){
      return;
    }

    $playableCards = $this->listPossibleCardsToPlay($activePlayer);
    if(count($playableCards)>0 ){
      $args['a'][] = 'actPlayCard';
      $args['p_cards'] = $playableCards;
    }
    $playablePrivateCards = $this->listPossiblePrivateCardsToPlay($activePlayer);
    if(count($playablePrivateCards)>0 ){
      $args['a'][] = 'actPlayCard';
      $args['_private'][$activePlayer->getId()]['p_cards'] = $playablePrivateCards;
    }
  }
}
