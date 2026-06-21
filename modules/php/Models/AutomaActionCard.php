<?php

namespace ROG\Models;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\States\SailTrait;

class AutomaActionCard extends Card
{ 
  
  protected $staticAttributes = [
    ['title', 'string'],
  ];

  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  } 

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['subtype'] = CARD_TYPE_AUTOMA_ACTION;
    return $data;
  }

  public function play(AutomaPlayer $player)
  {
    Game::get()->trace(__CLASS__.".".__FUNCTION__);
    $action = $this->getType();
    switch($action){
      case AutomaActionType::SAIL_HIGHER->value : 
        $this->playSail($player,true);
        break;
      case AutomaActionType::SAIL_LOWER->value : 
        $this->playSail($player,false);
        break;
      case AutomaActionType::DELIVER->value : 
        $this->playDeliver($player);
        break;
      case AutomaActionType::BUILD->value : 
        $this->playBuild($player);
        break;
      case AutomaActionType::ADVANCE_CITY->value : 
        break;
    }
  }
  
  public function playSail(AutomaPlayer $player, bool $higherShip)
  {
    Game::get()->trace(__CLASS__.".".__FUNCTION__."($higherShip)");
    
    //Seishin moves the ship shown on the card the number of river spaces shown on her die.
    $playerDieBefore = $player->getDie();
    $selectedShip = null;
    $boats = Meeples::getBoats($player->getId());
    foreach($boats as $boat){
      if(!isset($selectedShip)) $selectedShip = $boat;
      //higher ship means lowest position
      if(!$higherShip && ($boat->getPosition() >= $selectedShip->getPosition()) ){
        $selectedShip = $boat;
      }
      else if($higherShip && ($boat->getPosition() <= $selectedShip->getPosition()) ){
        $selectedShip = $boat;
      }

    }
    $toRiverSpace = ($selectedShip->getPosition() + $playerDieBefore -1) % NB_RIVER_SPACES +1;
    Game::get()->process_Sail($player,$selectedShip,$toRiverSpace);
  }
  
  public function playDeliver(AutomaPlayer $player)
  {
    Game::get()->trace(__CLASS__.".".__FUNCTION__);
    Globals::setTurnMainActionDone(MAIN_ACTION::DELIVER->value);
    //Seishin gains 3 influence in the region matching her die. She gains any point or influence rewards she reaches or passes along the influence track; she ignores all other rewards.
    $region = $player->getDie();
    Players::gainInfluence($player,$region,NB_INFLUENCE_SEISHIN_DELIVER);

    $customerCardInRegion = Cards::getInLocation(CARD_LOCATION_MAP_REGION.$region)->first();
    if(isset($customerCardInRegion)){
      //When Seishin delivers to a customer, if there is a Noble in the region matching its die, it delivers to that Noble instead of a facedown customer from the deck.
      $customerCardInRegion->setLocation(CARD_LOCATION_DELIVERED);
      $customerCardInRegion->setPId($player->getId());
      Notifications::deliver($player,$customerCardInRegion);
    }
    else {
      //Seishin takes the top card of the customer deck and places it facedown beside her board without revealing it. (She will reveal it when the game ends to score its endgame rewards.)
      $missingInDeck = Cards::drawCardsToHand($player,1);
      if($missingInDeck == 1) return;
      $customerCard = Cards::getPlayerHandOrders($player->getId())->first();
      //Keep cards ordered :
      Cards::insertOnTop($customerCard->getId(), CARD_LOCATION_DELIVERED_HIDDEN);
      Notifications::deliverHidden($player,$customerCard);
    }
  }
  
  public function playBuild(AutomaPlayer $player)
  {
    Game::get()->trace(__CLASS__.".".__FUNCTION__);
    $playerDieBefore = $player->getDie();
    $indexDieFace = array_search($playerDieBefore,DIE_FACES);
    $nbDieFaces = count(DIE_FACES);
    $buildSpaces = Game::get()->listPossibleSpacesToBuild($player);
    $k = 1;
    while(count($buildSpaces) == 0 && $k < $nbDieFaces){//BREAK LOOP after 6 turns
      //If there are no empty shore spaces in the region, increase her die’s value by 1 and then repeat this for the next region until she chooses an empty shore space (if her die is a 6, change it to a 1)
      $dieFace = DIE_FACES[Utils::positive_modulo($indexDieFace + $k,$nbDieFaces)];
      Game::get()->trace("Automa auto increase die from $playerDieBefore to $dieFace to look for shore space to build");
      $player->setDie($dieFace);
      Notifications::setDieFace($player,$dieFace);
      $buildSpaces = Game::get()->listPossibleSpacesToBuild($player);
      $k++;
    }
    if(count($buildSpaces) == 0){
      return;
    }
    //Seishin chooses the empty shore space with the lowest Koku cost in the region matching her die. 
    //If multiple empty shore spaces in that region have the lowest Koku cost, then she chooses whichever 1 of them is farther downriver. 
    $lowestCost = null;
    $shoreSpace = null;
    foreach($buildSpaces as $buildSpace){//list ordered from top to bottom of the river
      if(!isset($lowestCost) || $lowestCost >= $buildSpace->cost){
        $lowestCost = $buildSpace->cost;
        $shoreSpace = $buildSpace;
      }
    }

    $possibleTiles = Tiles::getInLocationOrdered(TILE_LOCATION_BUILDING_ROW);
    if(count($possibleTiles) == 0){
      //should not happen with game end before end of building row 
      return;
    }
    //Seishin chooses the building tile from the building row that has the build bonus with the most influence and places it on the chosen empty shore space. 
    //If multiple building tiles have the most influence for their build bonuses, then she chooses whichever 1 of them is closest to the building row’s end space (the space with the divine favor reward).
    $maxInfluenceBonus = 0;
    $tile = null;
    foreach($possibleTiles as $possibleTile){//list ordered from right to left of the building row
      $tileInfluence = $possibleTile->getBonus();
      if( $maxInfluenceBonus <= $tileInfluence){
        $maxInfluenceBonus = $tileInfluence;
        $tile = $possibleTile;
      }
    }

    //CORE ACTION : Use same main code as in player action "actBuildSelect()"
    //Seishin places 1 of her clan markers on the building tile’s build bonus and gains that much influence in the building’s region. 
    $previousPosition = $tile->getPosition();
    $previousLocation = $tile->getLocation();
    $tile->setLocation(TILE_LOCATION_BUILDING_SHORE);
    $tile->setPosition($shoreSpace->id);
    Notifications::build($player,$tile,$previousPosition,$previousLocation);
    Meeples::addClanMarkerOnShoreSpace($tile,$player);
    Globals::setLastBuiltTile($tile->getId());
    Globals::setLastBuiltLocationOrigin($previousLocation);
    Globals::setTurnMainActionDone(MAIN_ACTION::BUILD->value);
    //She gains any point or influence rewards she reaches or passes along the influence track; she ignores all other rewards.
    Players::gainInfluence($player,$shoreSpace->region,$tile->getBonus());

    Cards::getAssignedScenarios()->map(function(ScenarioCard $scenario) use ($player,$tile,$shoreSpace) {
      $scenario->abilityOnAutomaBuild($player,$tile,$shoreSpace);
    });
  }
}
