<?php

namespace ROG\Models;

use ROG\Core\Notifications;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;

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
  
  public function reveal(Player &$player, int $sumInfluence = 0)
  {
    $meeples = $this->getMeeplesWhenHidden();
    $this->setLocation(CARD_CITY_LOCATION_REVEALED);
    $this->setPlayed(true);
    Notifications::revealCityCard($player,$this,$meeples);
    switch($this->getType()){
      case CITY_CARD_TYPE::BRIBERY->value : 
        Players::giveMoney($player, $sumInfluence);
        break;
    }
  }
  
  /**
   * @return int score gained here
   */
  public function onEndReveal(Player &$player,) : int
  {
    $score = null;
    $meeples = $this->getMeeplesWhenHidden();
    $this->setLocation(CARD_CITY_LOCATION_REVEALED);
    foreach($meeples as $meeple){
      $meeple->setLocation(MEEPLE_LOCATION_CARD.$this->getId());
    }
    Notifications::revealCityCard($player,$this,$meeples);
    switch($this->getType()){
      case CITY_CARD_TYPE::SHARED_CLI->value : 
        //If that clan has the most customers
        $score = 0;
        $targetPid = $meeples->first()->getPId();
        if(Players::isPlayerWithMaxDeliveries($targetPid)){
          $score += 4;
        }
        break;
      case CITY_CARD_TYPE::SHARED_ENG->value : 
        //If that clan has the most buildings
        $score = 0;
        $targetPid = $meeples->first()->getPId();
        if(Players::isPlayerWithMaxBuildings($targetPid)){
          $score += 4;
        }
        break;
      case CITY_CARD_TYPE::SHARED_ENV->value : 
        //If that clan has the most score
        $score = 0;
        $targetPid = $meeples->first()->getPId();
        if(Players::isPlayerWithMaxScore($targetPid)){
          $score += 4;
        }
        break;
      case CITY_CARD_TYPE::SUMMONS->value : 
        //LOOK FOR each manor adjacent to ships
        $scorePerElement = 2;
        $nbTiles = Tiles::countBuiltTilesNearPlayerShips($this->getPId(),BUILDING_TYPE_MANOR,);
        $score = $scorePerElement * $nbTiles;
        break;
      case CITY_CARD_TYPE::FULL_STOR->value : 
        $scorePerFullStorage = 5;
        $score = 0;
        if($player->getResource(RESOURCE_TYPE_SILK)>= NB_MAX_RESOURCE) {
          $score += $scorePerFullStorage;
        }
        if($player->getResource(RESOURCE_TYPE_POTTERY)>= NB_MAX_RESOURCE) {
          $score += $scorePerFullStorage;
        }
        if($player->getResource(RESOURCE_TYPE_RICE)>= NB_MAX_RESOURCE) {
          $score += $scorePerFullStorage;
        }
        break;
      case CITY_CARD_TYPE::KIMONO_DRESS->value : 
        $scorePerElement = 1;
        $score = $scorePerElement * $player->getResource(RESOURCE_TYPE_SILK);
        break;
      case CITY_CARD_TYPE::CALL_TO_PORT->value : 
        $scorePerElement = 2;
        $nbTiles = Tiles::countBuiltTilesNearPlayerShips($this->getPId(),BUILDING_TYPE_PORT,);
        $score = $scorePerElement * $nbTiles;
        break;
      case CITY_CARD_TYPE::SHRINE_PIL->value : 
        $scorePerElement = 2;
        $nbTiles = Tiles::countBuiltTilesNearPlayerShips($this->getPId(),BUILDING_TYPE_SHRINE,);
        $score = $scorePerElement * $nbTiles;
        break;
      case CITY_CARD_TYPE::TEAHOUSE->value : 
        $scorePerElement = 1;
        $score = $scorePerElement * $player->getResource(RESOURCE_TYPE_POTTERY);
        break;
      case CITY_CARD_TYPE::SAKE_BREW->value : 
        $scorePerElement = 1;
        $score = $scorePerElement * $player->getResource(RESOURCE_TYPE_RICE);
        break;
    }
    if(isset($score)){
      $player->addPoints($score,false);
      Notifications::scoreCityCard($player,$this,$score);
    }
    else {
      $score = 0;
    }
    return $score;
  }
  
  public function scoreAsFirstCityCard() : bool
  {
    switch($this->getType()){
      case CITY_CARD_TYPE::SHARED_ENV->value : 
        return true;
    }
    return false;
  }


}
