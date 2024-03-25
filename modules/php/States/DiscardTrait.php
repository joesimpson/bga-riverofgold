<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Cards;
use ROG\Managers\Players;
use ROG\Models\CustomerCard;

/**
 * Actions related to the choice of 1 card to discard VS cards to keep
 */
trait DiscardTrait
{
   
  public function argDiscardCard()
  { 
    $activePlayer = Players::getActive();
    $player_id = $activePlayer->getId();
    $privateDatas = array ();

    $cards = $this->listPossibleCardsToDiscard($activePlayer);
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
  public function actDiscardCard($cardId)
  { 
    self::checkAction('actDiscardCard'); 
    self::trace("actDiscardCard($cardId)");

    $player = Players::getCurrent();
    $this->addStep();

    $card = Cards::get($cardId);
    if(CARD_LOCATION_HAND != $card->getLocation() || $player->getId() != $card->getPId()){
      throw new UnexpectedException(30,"You cannot discard this card");
    } 

    $card->setLocation(CARD_LOCATION_DISCARD);
    Notifications::discard($player,$card);

    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @return array of cardId
   */
  public function listPossibleCardsToDiscard($player)
  { 
    $possibleCards = [];
    $cards = Cards::getPlayerHandOrders($player->getId());
    foreach($cards as $card){
      $possibleCards[] = $card->getId();
    }
    return $possibleCards;
  }

}
