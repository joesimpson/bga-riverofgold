<?php

namespace ROG\Models;

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

  public function onAssignment(Player $player, ?int $markerId)
  {
    switch($this->getEffect()){
      case CITY_CARD_EFFECT::PREDICT->value : 
        //When you take this card, place a clan marker from another clan on it.
        $otherPlayer = Players::get($markerId);
        Meeples::addClanMarkerOnHiddenCard($otherPlayer,$this);
        break;
    }
  }
}
