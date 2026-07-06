<?php

namespace ROG\Models;

use ROG\Core\Notifications;
use ROG\Managers\Meeples;
use ROG\Managers\Players;

class CityCard extends Card
{ 
  
  protected $staticAttributes = [
    ['inner', 'bool'],
    ['effect', 'string'],
    ['title', 'string'],
  ];

  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  } 

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['subtype'] = CARD_TYPE_CITY;
    return $data;
  }

  /**
   * @return array list of possible player ids for clan markers to be placed on the card
   */
  public function listPossibleClanMarkerIds(Player $player) : array
  {
    $list = [];
    switch($this->getEffect()){
      case CITY_CARD_EFFECT::PREDICT->value : 
        //When you take this card, place a clan marker from another clan on it.
        $list = Players::getAllWithAutoma()->filter(function (Player $p) use($player){
            return $p->id != $player->getId(); 
          })->getIds();
        break;
    }
    return $list;
  }

  public function canPlayOnAdvance(Player $player,) : bool
  {
    $canPlay = false;
    switch($this->getType()){
      case CITY_CARD_TYPE::BRIBERY->value : 
        $canPlay = ! $this->isPlayed();
        break;
    }
    return $canPlay;
  }

  public function onAssignment(Player $player, ?int $markerId)
  {
    switch($this->getEffect()){
      case CITY_CARD_EFFECT::PREDICT->value : 
        //When you take this card, place a clan marker from another clan on it.
        $otherPlayer = Players::get($markerId);
        $clanMarker = Meeples::addClanMarkerOnHiddenCard($otherPlayer,$this,false);
        Notifications::cityCardPredict($player,$clanMarker, $otherPlayer);
        break;
    }
  }
  
  public function reveal(Player &$player, int $sumInfluence)
  {
    $this->setLocation(CARD_CITY_LOCATION_REVEALED);
    $this->setPlayed(true);
    Notifications::revealCityCard($player,$this);
    switch($this->getType()){
      case CITY_CARD_TYPE::BRIBERY->value : 
        Players::giveMoney($player, $sumInfluence);
        break;
    }
  }
}
