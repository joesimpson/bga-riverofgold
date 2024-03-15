<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Cards;
use ROG\Managers\Players;
use ROG\Models\Card;

trait DeliverTrait
{
   
  public function argDeliver()
  { 
    $activePlayer = Players::getActive();
    $cards = $this->listPossibleCardsToDeliver($activePlayer);
    $args = [
      'c' => $cards,
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

    $possibleCards = $this->listPossibleCardsToDeliver($player);
    if(!in_array($cardId, $possibleCards)){
      throw new UnexpectedException(30,"You cannot Deliver card $cardId, see : ".json_encode($possibleCards));
    }  

    //TODO JSA DELIVERY

    Players::claimMasteries($player);

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
