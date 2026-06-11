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
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Models\CustomerCard;
use ROG\Models\MAIN_ACTION;
use ROG\Models\Player;
use ROG\Models\ShoreSpace;

trait DeliverTrait
{
   
  public function argDeliver()
  { 
    $activePlayer = Players::getActive();
    $player_id = $activePlayer->getId();
    $privateDatas = array ();

    $cards = $this->listPossibleCardsToDeliver($activePlayer);
    //Beware cards in hand are private !
    $privateDatas[$player_id] = array(
      'c' => $cards,
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
  )
  { 
    $this->checkVersion($version);
    self::checkAction('actDeliverSelect'); 
    self::trace("actDeliverSelect($cardId)");

    $player = Players::getCurrent();
    $this->addStep();

    $card = Cards::get($cardId);
    if(CARD_LOCATION_HAND != $card->getLocation() || $player->getId() != $card->getPId()){
      throw new UnexpectedException(30,"You cannot Deliver this card");
    }
    if(!$this->isPossibleCardToDeliver($player,$card)){
      throw new UnexpectedException(31,"You cannot Deliver card $cardId");
    }  

    $this->processDeliver($player, $card);
  } 
  
  public function processDeliver(Player &$player, CustomerCard $card, ?array $replaceGoods = null)
  { 
    $card->setLocation(CARD_LOCATION_DELIVERED);
    Notifications::deliver($player,$card);
    Globals::setTurnMainActionDone(MAIN_ACTION::DELIVER->value);
    
    $playerPatron = $player->getPatron();
    if(isset($playerPatron)){
      $playerPatron->scoreWhenDeliver($player,$card);
      $playerPatron->addBonuses($player);
    }

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
    $card->playDeliveryAbility($player);
    Utils::playTradersAbilities($player);
    
    $shoreSpaces = ShoreSpaces::getSpacesByRegion($card->getRegion());
    $riverSpaces = ShoreSpaces::getUniqueAdjacentRiverSpaces($shoreSpaces);
    Utils::moveRogueShipFrom($player,$riverSpaces);

    Players::claimMasteries($player);

    //Delay Draw 2 cards
    Globals::addBonus($player,BONUS_TYPE_REFILL_HAND,'',false);
    
    Stats::inc("nbActionsDeliver", $player->getId());

    if($this->goToBonusStepIfNeeded($player)) return;
    $this->gamestate->nextState('next');

  }

  /**
   * @param int $cardId
   */
  #[PossibleAction]
  public function actDeliverReplace(int $cardId, int $silk, int $rice, int $pottery, 
    int $version,
  )
  { 
    $this->checkVersion($version);
    self::checkAction('actDeliverReplace'); 
    self::trace("actDeliverReplace($cardId,$silk, $rice, $pottery)");

    $player = Players::getCurrent();
    $this->addStep();

    $argsDeliver = $this->argDeliver()['_private'][$player->getId()];
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

    $this->processDeliver($player, $card,$replaceGoods);
    
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

    $resources = $player->getResources();
    foreach($card->getCost() as $neededType => $neededAmount){
      $isTradeGood = in_array($neededType, [RESOURCE_TYPE_SILK,RESOURCE_TYPE_RICE,RESOURCE_TYPE_POTTERY]);
      if($resources[$neededType] < $neededAmount && (!$isTradeGood || !$canReplaceGoods)) return false;
    }
    if($card->getCostAsTradeGoods() > $sumGoods) return false;

    return true;
  }

}
