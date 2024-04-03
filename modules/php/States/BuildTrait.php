<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Collection;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;
use ROG\Models\ShoreSpace;

trait BuildTrait
{
   
  public function argBuild()
  { 
    $activePlayer = Players::getActive();
    $possibleSpaces = $this->listPossibleSpacesToBuild($activePlayer);
    $args = [
      'spaces' => $possibleSpaces,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $position
   * @param int $tileId
   */
  public function actBuildSelect($position,$tileId)
  { 
    self::checkAction('actBuildSelect'); 
    self::trace("actBuildSelect($position,$tileId)");

    $player = Players::getCurrent();
    $pId = $player->id;
    $this->addStep();

    $possibleSpaces = $this->listPossibleSpacesToBuild($player);
    $possibleSpacesIds = $possibleSpaces->map(function($space) {return $space->id;})->toArray();
    if(!in_array($position, $possibleSpacesIds)){
      throw new UnexpectedException(10,"You cannot build on $position, see ids: ".json_encode($possibleSpaces->getIds()));
    }
    $shoreSpace = ShoreSpaces::getShoreSpace($position); 
    $tile = Tiles::get($tileId);
    if( TILE_LOCATION_BUILDING_ROW != $tile->getLocation()){
      throw new UnexpectedException(12,"You cannot build tile $tileId");
    }
    $previousPosition = $tile->getPosition();

    $cost = $this->buildingCost($player,$shoreSpace);
    Players::spendMoney($player,$cost);

    $tile->setLocation(TILE_LOCATION_BUILDING_SHORE);
    $tile->setPosition($position);

    Notifications::build($player,$tile,$previousPosition);

    if(BUILDING_ROW_END == $previousPosition){
      Players::gainDivineFavor($player,BUILDING_ROW_END_FAVOR);
    }

    Meeples::addClanMarkerOnShoreSpace($tile,$player);
    
    //TODO JSA scoreWhenBuild (depends on clan patron)

    Players::gainInfluence($player,$shoreSpace->region,$tile->getBonus());
    Players::claimMasteries($player);
    
    if(Globals::getBonuses()){
      $this->gamestate->nextState('bonus');
      return;
    }

    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @return Collection of ShoreSpace
   */
  public function listPossibleSpacesToBuild($player)
  { 
    $region = $player->getDie();
    //TODO JSA canBuild for master engineer
    $possibleSpaces = new Collection();
    $emptySpaces = ShoreSpaces::getEmptySpaces($region);
    foreach($emptySpaces as $key => $spaceId){
      $space = ShoreSpaces::getShoreSpace($spaceId);
      if($this->canBuildOnSpace($player,$space)){
        //update cost for UI
        $space->cost = $this->buildingCost($player,$space);
        $possibleSpaces->append($space);
      }
    }
    return $possibleSpaces;
  }
  
  /**
   * @param Player $player
   * @param ShoreSpace $space
   * @return true
   */
  public function canBuildOnSpace($player,$space)
  { 
    $cost = $this->buildingCost($player,$space);
    if($cost > $player->getMoney() ) return false;

    return true;
  }

  /**
   * @param Player $player
   * @param ShoreSpace $space
   * @return int
   */
  public function buildingCost($player,$space)
  { 
    $cost = $space->cost;
    $region = $space->region;
    $artisanMarker = Meeples::getMarkerOnArtisanSpace($player->getId(),$region);
    if(isset($artisanMarker)){
      $cost = max(0, $cost - ARTISAN_COST_REDUCTION);
    }
    return $cost;
  }
}
