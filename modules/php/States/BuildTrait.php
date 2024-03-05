<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Collection;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;

trait BuildTrait
{
   
  public function argBuild()
  { 
    $activePlayer = Players::getActive();
    $possibleSpaces = $this->listPossibleSpacesToBuild($activePlayer);
    $args = [
      'spaces' => $possibleSpaces,
    ];
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

    Players::spendMoney($player,$shoreSpace->cost);

    $tile->setLocation(TILE_LOCATION_BUILDING_SHORE);
    $tile->setPosition($position);

    Notifications::build($player,$tile,$previousPosition);

    if(BUILDING_ROW_END == $previousPosition){
      //TODO JSA gain 1 divine favor
      Notifications::message("gain 1 divine favor...");
    }

    Meeples::addClanMarkerOnShoreSpace($tile,$player);
    Players::gainInfluence($player,$shoreSpace->region,$tile->getBonus());
    //TODO JSA scoreWhenBuild (depends on clan patron)

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
        $possibleSpaces->append($space);
      }
    }
    return $possibleSpaces;
  }
  
  /**
   * @param Player $player
   * @return ShoreSpace $space
   */
  public function canBuildOnSpace($player,$space)
  { 
    if($space->cost > $player->getMoney() ) return false;

    return true;
  }

}
