<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
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

    $possibleSpaces = $this->listPossibleSpacesToBuild($player);
    if(!in_array($position, $possibleSpaces)){
      throw new UnexpectedException(10,"You cannot build on $position");
    }
    $shoreSpace = ShoreSpaces::getShoreSpace($position);
    if( $shoreSpace->cost > $player->getMoney()){
      throw new UnexpectedException(11,"You cannot pay this shore space");
    }
    $tile = Tiles::get($tileId);
    if( TILE_LOCATION_BUILDING_ROW != $tile->getLocation()){
      throw new UnexpectedException(12,"You cannot build tile $tileId");
    }
    $previousPosition = $tile->getPosition();

    //TODO JSA spend money

    $tile->setLocation(TILE_LOCATION_BUILDING_SHORE);
    $tile->setPosition($position);

    Notifications::build($player,$tile,$previousPosition);

    if(BUILDING_ROW_END == $previousPosition){
      //TODO JSA gain 1 divine favor
      Notifications::message("gain 1 divine favor...");
    }

    //TODO JSA Add Meeple clan marker
    //TODO JSA influenceWhenBuild (depends on clan patron)
    $n = $tile->getBonus();
    Notifications::message("gain $n influence...");
    //TODO JSA scoreWhenBuild (depends on clan patron)

    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @return array if int
   */
  public function listPossibleSpacesToBuild($player)
  { 
    $region = $player->getDie();

    $possibleSpaces = ShoreSpaces::getEmptySpaces($region);
    //TODO JSA filter cost
    //TODO JSA canBuild for master engineer
    return $possibleSpaces;
  }

}
