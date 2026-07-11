<?php

namespace ROG\Models;

use ROG\Models\BonusBuildingRewardChoice;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Helpers\Utils;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
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

  //public function canPlayOnDeliver(Player $player,) : bool
  //{
  //  $canPlay = false;
  //  switch($this->getType()){
  //    case CITY_CARD_TYPE::CARTEL->value : 
  //      $canPlay = true;
  //      break;
  //  }
  //  return $canPlay;
  //}
  
  /**
   * @return array datas used by actPlayCard and args
   */
  public function playCardActionOnDeliver(Player $player, int $region) : array
  {
    $actions = [];
    switch($this->getType()){
      case CITY_CARD_TYPE::CARTEL->value : 
        $nbBuildingsInRegion = $player->getNbBuildingsInRegion($region);
        $playDatas['n'] = 2 * $nbBuildingsInRegion; 
        $playDatas['regions'] = [$region]; 
        if($playDatas['n'] > 0){
          $actions[AFTER_ACTION::GAIN_INFLUENCE->value] = $playDatas;
        }
        break;
    }
    return $actions;
  }
  
  /**
   * @return array datas used by actPlayCard and args
   */
  public function playCardActionOnBuild(Player $player, ShoreSpace $shoreSpace) : array
  {
    $actions = [];
    switch($this->getType()){
      case CITY_CARD_TYPE::OPPORTUNIST->value : 
        $nbShipsNearBuilding = Meeples::countPlayerShipsNearShoreSpace($shoreSpace, $player->getId());
        $playDatas['n'] = 1 * $nbShipsNearBuilding; 
        $playDatas['regions'] = [$shoreSpace->region]; 
        if($playDatas['n'] > 0){
          $actions[AFTER_ACTION::GAIN_INFLUENCE->value] = $playDatas;
        }
        break;
    }
    return $actions;
  }
  
  /**
   * @return array datas used by actPlayCard and args
   */
  public function playCardActionOnCompleteJourney(Player $player, ?BuildingTile $removedTile) : array
  {
    $actions = [];
    switch($this->getType()){
      case CITY_CARD_TYPE::NIGHT_MARKET->value : 
        if(isset($removedTile)){
          $playDatas['tiles'][$removedTile->getId()] = [ 
            'id' => $removedTile->getId(), 
            'type' => $removedTile->getType(),  
            'choices' => [ BonusBuildingRewardChoice::OWNER->value, BonusBuildingRewardChoice::VISITOR->value, ],
          ]; 
          $actions[AFTER_ACTION::BUILDING_REWARD->value] = $playDatas;
        }
        break;
    }
    return $actions;
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
  
  public function playCardDatasOnTurn(Player &$player,) : array
  {
    $playDatas = [];
    if($this->isPlayed()) return [];
    switch($this->getType()){
      case CITY_CARD_TYPE::OFFLOAD->value : 
        if(!Utils::isPlayerActionDone()){
          $actionDatas = [];
          $actionDatas['tiles'] = [];
          //REWARDS adjacent to river space of ships if opponents ships
          $ships = Meeples::getBoats($player->getId());
          $ships->map(function(Meeple $ship) use ($player) {
              $nbOpponentShips = Meeples::countOpponentShipsInLocation($player->getId(),$ship->getPosition()); 
              if($nbOpponentShips > 0) {
                return $ship->getPosition();
              }
              return null;
            })
            ->filter(function(?int $pos) {return isset($pos); })
            ->map(function(int $riverSpace) use (&$actionDatas) {
              $adjacentSpaces = ShoreSpaces::getAdjacentSpaces($riverSpace);
              foreach($adjacentSpaces as $adjacentSpace){
                $tile = Tiles::getTileOnShoreSpace($adjacentSpace);
                if(!isset($tile)){
                  //EMPTY SHORE SPACE REWARD : 1 koku
                  if(!array_key_exists(-1,$actionDatas['tiles'])){
                    $actionDatas['tiles'][-1] = [ 
                      'id' => -1, 
                      'type' => [RESOURCE_TYPE_MONEY => EMPTY_SPACE_REWARD],  
                      'space' => $adjacentSpace,
                      'choices' => [ BonusBuildingRewardChoice::VISITOR->value, ],
                    ]; 
                  }
                }
                else {
                  if(!array_key_exists($tile->getId(),$actionDatas['tiles'])){
                    $actionDatas['tiles'][$tile->getId()] = [ 
                      'id' => $tile->getId(), 
                      'type' => $tile->getType(),  
                      'choices' => [ BonusBuildingRewardChoice::VISITOR->value, ],
                    ]; 
                  }
                }
              }
            });
          if(count($actionDatas['tiles']) > 0){
            $playDatas['actions'][BEFORE_ACTION::BUILDING_REWARD->value] = $actionDatas;
          }
        }
        break;
      case CITY_CARD_TYPE::BLACK_MARKET->value : 
        $actionDatas = [];
        $actionDatas['trades'] = [
          RESOURCE_TYPE_SILK    => ['type' => RESOURCE_TYPE_SILK   , 'delta'=>1, 'min' =>0, 'max'=>RESOURCES_LIMIT[RESOURCE_TYPE_SILK   ], ],
          RESOURCE_TYPE_RICE    => ['type' => RESOURCE_TYPE_RICE   , 'delta'=>1, 'min' =>0, 'max'=>RESOURCES_LIMIT[RESOURCE_TYPE_RICE   ], ],
          RESOURCE_TYPE_POTTERY => ['type' => RESOURCE_TYPE_POTTERY, 'delta'=>1, 'min' =>0, 'max'=>RESOURCES_LIMIT[RESOURCE_TYPE_POTTERY], ],
          RESOURCE_TYPE_MONEY   => ['type' => RESOURCE_TYPE_MONEY  , 'delta'=>2, 'min' =>$player->getResource(RESOURCE_TYPE_MONEY), 'max'=> RESOURCES_LIMIT[RESOURCE_TYPE_MONEY], ],
        ];

        $playDatas['actions'][BEFORE_ACTION::TRADE_FOR_RESOURCES->value] = $actionDatas;
        $playDatas['marker'] = null;
        break;
      case CITY_CARD_TYPE::TRAVEL_TRO->value : 
        $actionDatas = [];
          $actionDatas['n'] = 1; 
          $actionDatas['regions'] = REGIONS; 
        $playDatas['actions'][BEFORE_ACTION::GAIN_INFLUENCE->value] = $actionDatas;
        $playDatas['marker'] = null;
        break;
    }
    return $playDatas;
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

  public function revealUpdate(Player $player,) : Collection
  {
    $meeples = $this->getMeeplesWhenHidden();
    $this->setLocation(CARD_CITY_LOCATION_REVEALED);
    foreach($meeples as $meeple){
      $meeple->setLocation(MEEPLE_LOCATION_CARD.$this->getId());
    }
    Notifications::revealCityCard($player,$this,$meeples);
    return $meeples;
  }
  
  public function reveal(Player &$player, ?int $sumInfluence = 0,)
  {
    $this->setPlayed(true);
    $meeples = $this->revealUpdate($player);
    switch($this->getType()){
      case CITY_CARD_TYPE::BRIBERY->value : 
        Players::giveMoney($player, $sumInfluence);
        break;
      case CITY_CARD_TYPE::CARTEL->value : 
      case CITY_CARD_TYPE::OPPORTUNIST->value : 
      case CITY_CARD_TYPE::TRAVEL_TRO->value : 
        //Done in actPlayCard
        break;
    }
  }
  
  /**
   * @return int score gained here
   */
  public function onEndReveal(Player &$player,) : int
  {
    $score = null;
    $meeples = $this->revealUpdate($player);
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
