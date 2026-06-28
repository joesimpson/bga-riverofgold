<?php

namespace ROG\States;

use Bga\GameFramework\Actions\Types\IntParam;
use Bga\GameFramework\Actions\Types\JsonParam;
use Bga\GameFramework\States\PossibleAction;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\ClientCardResources;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Models\AutomaPlayer;
use ROG\Models\CustomerCard;
use ROG\Models\MAIN_ACTION;
use ROG\Models\Player;
use ROG\Models\ScenarioType;
use ROG\Models\ShoreSpace;

trait DeliverTrait
{
   
  public function argDeliver()
  { 
    $activePlayer = Players::getActive();
    $player_id = $activePlayer->getId();
    $privateDatas = array ();
    $cardsWithResources = [];
    
    $scenario = $activePlayer->getScenario();
    if(isset($scenario) && $scenario->getType() == ScenarioType::UNICORN_1->value){
      if($scenario->getResources() && count($scenario->getResources()) > 0 ){

        $cardsWithResources[$scenario->getId()] = 
          $scenario->getUiData();
      }
    }

    $cards = $this->listPossibleCardsToDeliver($activePlayer);
    $cardsCosts = Cards::getMany($cards)->map(function($card){return $card->getCost();})->toAssoc();
    //Beware cards in hand are private !
    $privateDatas[$player_id] = array(
      'c' => $cards,
      'cardsCosts' => $cardsCosts,
      'canReplaceGoods' => [],
    );
    
    $shindoshi2 = Utils::getShindoshiMarker($player_id,CARD_SHINDOSHI_2);
    if(isset($shindoshi2)){
      $cardsWithReplacableGoods = $this->listPossibleCardsToDeliver($activePlayer, true, true);
      $cardsCosts = Cards::getMany($cardsWithReplacableGoods)->map(function($card){return $card->getCost();})->toAssoc();
      $privateDatas[$player_id]['canReplaceGoods'] = [
        'marker' => $shindoshi2->getId(),
        'cards' => $cardsWithReplacableGoods,
        'cardsCosts' => $cardsCosts,
      ];
    }

    $args = [
      '_private' => $privateDatas,
    ];
    if(count($cardsWithResources)>0) $args['cardsWithResources'] = $cardsWithResources;
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $cardId
   */
  #[PossibleAction]
  public function actDeliverSelect(
    #[IntParam(name: 'c')] int $cardId,
    int $version, 
    #[JsonParam] ClientCardResources|null $cardRes = null,
  )
  { 
    $this->checkVersion($version);
    self::checkAction('actDeliverSelect'); 
    self::trace("actDeliverSelect($cardId)");

    $player = Players::getCurrent();
    $this->addStep();

    $args = $this->argDeliver();
    $argsDeliver = $args['_private'][$player->getId()];
    $possiblecards = $argsDeliver['c'];
    if( !in_array($cardId, $possiblecards) ){
      throw new UnexpectedException(30,"You cannot Deliver card $cardId, see ".json_encode($possiblecards));
    }

    $card = Cards::get($cardId);
    
    $replaceGoods = null;
    $this->processSpendResourcesFromCard($player,$card,$args,$replaceGoods,$cardRes,);

    $this->processDeliverAction($player, $card,$replaceGoods);
  } 
  
  public function processDeliverAction(Player &$player, CustomerCard $card, ?array $replaceGoods = null,)
  { 

    Globals::setTurnMainActionDone(MAIN_ACTION::DELIVER->value);
    $this->processDeliver($player, $card,$replaceGoods,false);
    Utils::playTradersAbilities($player);
    
    Stats::inc("nbActionsDeliver", $player->getId());
    
    if($this->goToBonusStepIfNeeded($player)) return;
    $this->gamestate->nextState('next');
  }

  public function processDeliver(Player &$player, CustomerCard $card, ?array $replaceGoods = null, bool $free = false)
  { 
    $previousLocation = $card->getLocation();
    $card->setLocation(CARD_LOCATION_DELIVERED);
    $card->setPId($player->getId());
    Notifications::deliver($player,$card);
    
    $playerPatron = $player->getPatron();
    if(isset($playerPatron)){
      $playerPatron->scoreWhenDeliver($player,$card);
      $playerPatron->addBonuses($player);
    }

    if(!$free){
      if(isset($replaceGoods)){
        //LOOP TRADE GOODS in this array
        foreach($replaceGoods as $neededType => $replacedAmount){
          $player->giveResource(-$replacedAmount,$neededType);
        }
        //LOOP OTHER GOODS 
        foreach($card->getCost() as $neededType => $neededAmount){
          if(!array_key_exists($neededType,$replaceGoods)){
            //should not be needed with isPossibleCardToDeliver() controls
            //if($player->getResource($neededType) < $neededAmount){
            //  throw new UnexpectedException(64,"You cannot spend $neededAmount of resource type $neededType");
            //}
            $player->giveResource(-$neededAmount,$neededType);
          }
        }
      }
      else {//PAY CARD COSTS
        foreach($card->getCost() as $neededType => $neededAmount){
          $player->giveResource(-$neededAmount,$neededType);
        }
      }
    }
    $card->playDeliveryAbility($player);
    
    $shoreSpaces = ShoreSpaces::getSpacesByRegion($card->getRegion());
    $riverSpaces = ShoreSpaces::getUniqueAdjacentRiverSpaces($shoreSpaces);
    Utils::moveRogueShipFrom($player,$riverSpaces);

    Players::claimMasteries($player);

    if($previousLocation == CARD_LOCATION_HAND){//not always with some Scenarios
      //Delay Draw 2 cards
      Globals::addBonus($player,BONUS_TYPE_REFILL_HAND,'',false);
    }

  }

  /**
   * @param int $cardId
   */
  #[PossibleAction]
  public function actDeliverReplace(
    int $cardId, int $silk, int $rice, int $pottery, 
    int $version,
    #[JsonParam] ClientCardResources|null $cardRes = null,
  )
  { 
    $this->checkVersion($version);
    self::checkAction('actDeliverReplace'); 
    self::trace("actDeliverReplace($cardId,$silk, $rice, $pottery)");

    $player = Players::getCurrent();
    $this->addStep();

    $args = $this->argDeliver();
    $argsDeliver = $args['_private'][$player->getId()];
    if( count($argsDeliver['canReplaceGoods']) == 0 
      || count($argsDeliver['canReplaceGoods']['cards']) == 0
    ){
      throw new UnexpectedException(61,"You cannot Deliver cards and replace goods");
    }
    $cards = $argsDeliver['canReplaceGoods']['cards'];
    if( !in_array($cardId, $cards) ){
      throw new UnexpectedException(62,"You cannot Deliver card $cardId and replace goods");
    }

    $card = Cards::get($cardId);
    $totalNeeded = $card->getCostAsTradeGoods();
    $sumGoods = $silk + $pottery + $rice;
    if($totalNeeded != $sumGoods){
      throw new UnexpectedException(63,"Wrong amount to replace goods : $totalNeeded != $sumGoods ");
    }
    $replaceGoods = [
      RESOURCE_TYPE_SILK => $silk,
      RESOURCE_TYPE_RICE => $rice,
      RESOURCE_TYPE_POTTERY => $pottery,
    ];
    
    $this->processSpendResourcesFromCard($player,$card,$args,$replaceGoods,$cardRes,);

    foreach($replaceGoods as $type => $amount){
      $q = $player->getResource($type);
      if($q < $amount){
        throw new UnexpectedException(64,"You cannot spend $amount of resource type $type");
      }
    }

    $markerId = $argsDeliver['canReplaceGoods']['marker'];
    $marker = Meeples::get($markerId);
    $cardMarker = Cards::get($marker->getCardId());
    Notifications::playCustomerAbility($player,$cardMarker);
    Meeples::removeClanMarkerById($player,$markerId);

    $this->processDeliverAction($player, $card,$replaceGoods);
  } 
  
  /**
   * @param array $replaceGoods is updated accordingly 
   */
  public function processSpendResourcesFromCard(Player &$player, CustomerCard $card, array $args, array|null &$replaceGoods = null,ClientCardResources|null $cardRes = null,)
  { 

    if(isset($cardRes)){
      if(!isset($replaceGoods)){
        $replaceGoods = [
          RESOURCE_TYPE_SILK => 0,
          RESOURCE_TYPE_RICE => 0,
          RESOURCE_TYPE_POTTERY => 0,
        ];
        foreach($card->getCost() as $type => $cost) $replaceGoods[$type] = $cost;
      }
      $cardsWithResources = isset($args['cardsWithResources']) ? $args['cardsWithResources'] : [];
      if( !in_array($cardRes->cardId, array_keys($cardsWithResources)) ){
        throw new UnexpectedException(60,"You cannot spend goods from card ".($cardRes->cardId));
      }
      $cardWithResources = Cards::get($cardRes->cardId);
      $goodsFromCard = [
        RESOURCE_TYPE_SILK => $cardRes->silk,
        RESOURCE_TYPE_RICE => $cardRes->rice,
        RESOURCE_TYPE_POTTERY => $cardRes->pottery,
      ];
      
      foreach($goodsFromCard as $type => $amount){
        $currentCardAmount = $cardWithResources->getResource($type);
        if($currentCardAmount < $amount ){
          throw new UnexpectedException(64,"You cannot spend $amount of resource type $type (max $currentCardAmount) from card".($cardRes->cardId));
        }
        $cardWithResources->addResource($player,-$amount,$type);
        $replaceGoods[$type] -= $amount;
        if($replaceGoods[$type] < 0 ){
          throw new UnexpectedException(64,"Invalid amount of good $type");
        }
      }
    }
  }
  /**
   * @param Player $player
   * @return array of cardId
   */
  public function listPossibleCardsToDeliver(Player $player, bool $ignoreDie = false, bool $canReplaceGoods = false )
  { 
    $possibleCards = [];
    $cards = Cards::getPlayerHandOrders($player->getId());
    foreach($cards as $card){
      if(!$this->isPossibleCardToDeliver($player,$card,$ignoreDie,$canReplaceGoods)) continue;
      $possibleCards[] = $card->getId();
    }

    //Dragon Scenario Noble cards
    $cardsInRegions = Cards::getInLocationOrdered(CARD_LOCATION_MAP_REGION."%");
    foreach($cardsInRegions as $card){
      $region = $card->getRegion();
      $automaPlayer = Players::automaPlayer();
      //if you have more influence than Seishin in the region
      if( !($player instanceof AutomaPlayer) && ($player->getInfluence($region) <= $automaPlayer->getInfluence($region))) continue;
      if(!$this->isPossibleCardToDeliver($player,$card,$ignoreDie,$canReplaceGoods)) continue;
      $possibleCards[] = $card->getId();
    }
    return $possibleCards;
  }

  /**
   * 
   * @param Player $player
   * @param Card $card
   * @return bool true when the card
   */
  public function isPossibleCardToDeliver(Player $player,CustomerCard $card, bool $ignoreDie = false, bool $canReplaceGoods = false )
  { 
    $region = $player->getDie();
    $regions = [$region];

    //For mantis clan Yoritomo: consider all regions
    $playerPatron = $player->getPatron();
    if(isset($playerPatron) && PATRON_SON_OF_STORM == $playerPatron->getType()){
      $ignoreDie = true;
    }
    if($ignoreDie){
      $regions = REGIONS;
    }

    if(!in_array($card->getRegion(), $regions )) return false;

    //we may deliver cards ignoring die face and resource type if we have enough goods
    $sumGoods = $player->getResource(RESOURCE_TYPE_SILK)
              + $player->getResource(RESOURCE_TYPE_RICE)
              + $player->getResource(RESOURCE_TYPE_POTTERY);

    $playerScenario = $player->getScenario();

    $resources = $player->getResources();
    
    //check Unicorn Scenario resources
    if(isset($playerScenario) && $playerScenario->getType() == ScenarioType::UNICORN_1->value){
      $sumGoods += $playerScenario->getResource(RESOURCE_TYPE_SILK)
                + $playerScenario->getResource(RESOURCE_TYPE_RICE)
                + $playerScenario->getResource(RESOURCE_TYPE_POTTERY);
      foreach($resources as $type => &$q){
        $q += $playerScenario->getResource($type);
      }
    }

    foreach($card->getCost() as $neededType => $neededAmount){
      $isTradeGood = in_array($neededType, [RESOURCE_TYPE_SILK,RESOURCE_TYPE_RICE,RESOURCE_TYPE_POTTERY]);
      if($resources[$neededType] < $neededAmount && (!$isTradeGood || !$canReplaceGoods)) return false;
    }
    if($card->getCostAsTradeGoods() > $sumGoods) return false;
    
    if(isset($playerScenario) && !$playerScenario->canDeliver($player,$card->getRegion())) return false;

    return true;
  }

}
