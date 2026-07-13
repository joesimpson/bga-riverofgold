<?php

namespace ROG\Managers;

use ROG\Core\Game;
use ROG\Helpers\Collection;
use ROG\Models\CitySpace;
use ROG\Models\CityTileSpace;
use ROG\Models\ScoringCityTile;

/* Class to manage all the CitySpace */

class CitySpaces
{  
  /**
   * @return array all shore spaces with useful datas for UI
   */
  public static function getUiData() : array
  {
    $all = new Collection(self::getAllCitySpaces());
    return $all->ui();
  } 

  public static function getCitySpaceById(int $id) : ?CitySpace
  {
    return self::getAllCitySpaces()[$id];
  }

  public static function getCitySpace(int $row, int $col) : ?CitySpace
  {
    return (new Collection(self::getAllCitySpaces()))->filter(function(CitySpace $s) use ($row, $col) { return $s->row == $row && $s->column == $col;})->first();
  }

  /**
   * @return array of all the different types of ShoreSpace
   */
  public static function getAllCitySpaces() : array
  {
    return [
      1   => new CitySpace(1 , 1,1, 1, [ BONUS_TYPE_CHOICE => 2, ] ),
      2   => new CitySpace(2 , 1,2, 2, [ BONUS_TYPE_SECOND_MARKER_ON_BUILDING => 1,  ] ),
      3   => new CitySpace(3 , 1,3, 1, [ BONUS_TYPE_BUILDING_ROW_REWARDS => 1, ] ), 
      4   => new CitySpace(4 , 1,4, 2, [ BONUS_TYPE_DELIVER_TOP_DECK => 1, ] ), 

      5   => new CitySpace(5 , 2,1, 3, [ RESOURCE_TYPE_MOON=>1, RESOURCE_TYPE_SUN => 1, ] ),
      6   => new CitySpace(6 , 2,2, 4, [ BONUS_TYPE_INC_HAND_LIMIT => 1,  ] ),
      7   => new CitySpace(7 , 2,3, 3, [ BONUS_TYPE_EMPTY_SHORE_POINTS  => 1, ] ),
      8   => new CitySpace(8 , 2,4, 4, [ BONUS_TYPE_BUILD_NEAR_SHIPS => 1 ] ),
      
      9   => new CitySpace(9 , 3,1, 5, [ RESOURCE_TYPE_MONEY => 7,  ] ),
      10  => new CitySpace(10, 3,2, 6, [ BONUS_TYPE_POINTS => 5, ] ),
      11  => new CitySpace(11, 3,3, 5, [ BONUS_TYPE_REWARDS_SELECT_REGION => 2, ] ),
      12  => new CitySpace(12, 3,4, 6, [ BONUS_TYPE_FREE_SAIL => 1 ] ),

      101 => new CityTileSpace(101, 1, ),
      102 => new CityTileSpace(102, 2, ),
      103 => new CityTileSpace(103, 3, ),
      104 => new CityTileSpace(104, 4, ),
      105 => new CityTileSpace(105, 5, ),
    ];
  }
  
  /**
   * @param int $pRegion the region to search
   * @return array list of spaces id
   */
  public static function getSpacesByRegion(int $pRegion) : array {
    $spaces = array_filter(CitySpaces::getAllCitySpaces(), function (CitySpace $space) use ($pRegion){ return $space->region == $pRegion;} ,);
    $spaceIds = array_keys($spaces);

    //Game::get()->trace("getSpacesByRegion($pRegion)... before tiles spaces : ".json_encode($spaceIds));

    $cityTiles = Tiles::getInLocation(TILE_LOCATION_CITYSCORING_BOARD);
    $cityTiles->map(function(ScoringCityTile $t) use ($pRegion, &$spaceIds) { 
      if($t->getRegion() == $pRegion){
        $spaceIds[] = $t->getCitySpace()->id; 
      }
    });
    //$spaceIds = array_values($spaceIds);

    //Game::get()->trace("getSpacesByRegion($pRegion)... after tiles spaces : ".json_encode($spaceIds));

    return $spaceIds;
  }
  
  /**
   * @param int $pColumn the column to search
   * @return array list of spaces id
   */
  public static function getSpacesByColumn(int $pColumn) : array {
    $spaceIds = [];
    if($pColumn == CITY_COLUMN_TILES){
      $cityTiles = Tiles::getInLocation(TILE_LOCATION_CITYSCORING_BOARD);
      $cityTiles->map(function(ScoringCityTile $t) use ( &$spaceIds) { 
        $spaceIds[] = $t->getCitySpace()->id; 
      });
    }
    else {
      $spaces = array_filter(CitySpaces::getAllCitySpaces(), function (CitySpace $space) use ($pColumn){ return $space->column == $pColumn;} ,);
      $spaceIds = array_keys($spaces);
    }
    return $spaceIds;
  }
  
  /**
   * @param int $pRegion the region to search
   * @return array list of spaces id
   */
  public static function getEmptySpaces(int $pRegion) : array{
    $spaces = CitySpaces::getSpacesByRegion($pRegion);
    $usedSpaces = Meeples::getUsedPositionsOnCity();
    return array_values( array_diff($spaces, $usedSpaces) );
  }
  
  /**
   * @return array list of spaces id
   */
  public static function getAllEmptySpaces() : array{
    $spaces = CitySpaces::getAllCitySpaces();
    $spaceIds = array_keys($spaces);
    $usedSpaces = Meeples::getUsedPositionsOnCity();
    return array_values( array_diff($spaceIds, $usedSpaces) );
  }
  
  /**
   * @return array list of spaces id
   */
  public static function getEmptySpacesInColumn(int $pColumn) : array{
    $spaces = CitySpaces::getSpacesByColumn($pColumn);
    $usedSpaces = Meeples::getUsedPositionsOnCity();
    return array_values( array_diff($spaces, $usedSpaces) );
  }
}
