<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Cards;
use ROG\Managers\Players;
use ROG\Models\CustomerCard;

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
    );

    $args = [
      '_private' => $privateDatas,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $cardId
   */
  public function actDeliverSelect($cardId)
  { 
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

    $card->setLocation(CARD_LOCATION_DELIVERED);
    Notifications::deliver($player,$card);

    foreach($card->getCost() as $neededType => $neededAmount){
      $player->giveResource(-$neededAmount,$neededType);
    }
    $card->playDeliveryAbility($player);

    Players::claimMasteries($player);

    //Delay Draw 2 cards
    Globals::addBonus($player,BONUS_TYPE_REFILL_HAND);

    $playerBonuses = Globals::getBonuses();
    if(isset($playerBonuses)){
      $this->gamestate->nextState('bonus');
      return;
    }

    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @return array of cardId
   */
  public function listPossibleCardsToDeliver($player)
  { 
    $possibleCards = [];
    $cards = Cards::getPlayerHandOrders($player->getId());
    foreach($cards as $card){
      if(!$this->isPossibleCardToDeliver($player,$card)) continue;
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
  public function isPossibleCardToDeliver($player,$card)
  { 
    $region = $player->getDie();
    $regions = [$region];
    //TODO JSA canDeliver for mantis clan #6: consider all regions
    if(!in_array($card->getRegion(), $regions )) return false;

    $resources = $player->getResources();
    foreach($card->getCost() as $neededType => $neededAmount){
      if($resources[$neededType] < $neededAmount) return false;
    }

    return true;
  }

}
