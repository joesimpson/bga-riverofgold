<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\BuildingTile;
use ROG\Models\Meeple;
use ROG\Models\Player;
use ROG\Models\Tile;

/**
 * Actions related to Monk ability
 */
trait BonusSecondMarkerTrait
{
   
  public function argBonusSecondMarker()
  { 
    $activePlayer = Players::getActive();
    $currentBonus = Globals::getCurrentBonus();
    $possibles = $this->listPossibleTilesToSecondBuild($activePlayer,$currentBonus);
    $args = [
      'p' => $possibles,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $tileId
   */
  public function actBonusSecondMarker($tileId)
  { 
    self::checkAction('actBonusSecondMarker'); 
    self::trace("actBonusSecondMarker($tileId)");

    $player = Players::getCurrent();
    $this->addStep();

    $currentBonus = Globals::getCurrentBonus();
    $tile = Tiles::get($tileId);
    if(!$this->isPossibleTileToSecondBuild($player,$tile,$currentBonus)){
      throw new UnexpectedException(405,"You cannot second build this tile");
    }

    Meeples::addClanMarkerOnShoreSpace($tile,$player,2, BONUS_TYPE_SECOND_MARKER_ON_BUILDING != $currentBonus);
    Players::claimMasteries($player);
    
    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @param int $currentBonus
   * @return array of tiles id
   */
  public function listPossibleTilesToSecondBuild($player,$currentBonus)
  { 
    $possibles = [];
    $tiles = Tiles::getInLocation(TILE_LOCATION_BUILDING_SHORE);
    foreach($tiles as $tile){
      if($this->isPossibleTileToSecondBuild($player,$tile,$currentBonus)){
        $possibles[] = $tile->getId();
      }
    }
    return $possibles;
  }
  
  /**
   * @param Player $player
   * @param BuildingTile $tile
   * @param int $currentBonus
   * @return bool true if this player can add a second marker on this building tile,
   * false otherwise
   * 
   */
  public function isPossibleTileToSecondBuild($player,$tile,$currentBonus)
  { 
    if(!($tile instanceof BuildingTile)) return false;
    if(TILE_LOCATION_BUILDING_SHORE != $tile->getLocation()) return false;

    $meeples = $tile->getMeeples();
    if(count($meeples) != 1) return false;
    $meeple = $meeples->first();

    if(BONUS_TYPE_SECOND_MARKER_ON_BUILDING == $currentBonus && $meeple->getPId() != $player->getId()){
      return false;
    }
    if(BONUS_TYPE_SECOND_MARKER_ON_OPPONENT == $currentBonus && $meeple->getPId() == $player->getId()){
      return false;
    }

    return true;
  }

}
;