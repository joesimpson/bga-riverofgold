<?php

namespace ROG\Managers;

use ROG\Helpers\Collection;
use ROG\Models\SHORE_SIDE;
use ROG\Models\ShoreSpace;

/* Class to manage all the ShoreSpace */

class ShoreSpaces
{  

  /**
   * @return array all shore spaces with useful datas for UI
   */
  public static function getUiData()
  {
    $all = new Collection(self::getShoreSpaces());
    return $all->ui();
  } 
   
  /**
   * @param int $position Position on the River
   * @return ShoreSpace
   */
  public static function getShoreSpace($position) : ShoreSpace
  {
    return self::getShoreSpaces()[$position];
  }

  /**
   * @return array of all the different types of ShoreSpace
   */
  public static function getShoreSpaces()
  {
    //function to init spaces
    return [
      //30 spaces
      1  => new ShoreSpace( 1, SHORE_SIDE::RIGHT,  REGION_1, 6 , SHORE_SPACE_BASE,  ),
      2  => new ShoreSpace( 2, SHORE_SIDE::LEFT,   REGION_1, 5 , SHORE_SPACE_BASE,  ),
      3  => new ShoreSpace( 3, SHORE_SIDE::RIGHT,  REGION_1, 8 , SHORE_SPACE_STARTING_BUILDING_FOR_3,  ),
      4  => new ShoreSpace( 4, SHORE_SIDE::LEFT,   REGION_1, 12, SHORE_SPACE_BASE,  ),
      5  => new ShoreSpace( 5, SHORE_SIDE::RIGHT,  REGION_1, 6 , SHORE_SPACE_BASE,  ),

      6  => new ShoreSpace( 6 , SHORE_SIDE::LEFT,  REGION_2, 0  , SHORE_SPACE_IMPERIAL_MARKET,  ),
      7  => new ShoreSpace( 7 , SHORE_SIDE::RIGHT, REGION_2, 8  , SHORE_SPACE_STARTING_BUILDING_FOR_2,  ),
      8  => new ShoreSpace( 8 , SHORE_SIDE::LEFT,  REGION_2, 6  , SHORE_SPACE_BASE,  ),      
      9  => new ShoreSpace( 9 , SHORE_SIDE::RIGHT, REGION_2, 12 , SHORE_SPACE_BASE,  ),      
      10 => new ShoreSpace( 10, SHORE_SIDE::LEFT,  REGION_2, 9  , SHORE_SPACE_BASE,  ),      

      11 => new ShoreSpace( 11, SHORE_SIDE::RIGHT, REGION_3, 8  ,  SHORE_SPACE_STARTING_BUILDING_FOR_3,  ),
      12 => new ShoreSpace( 12, SHORE_SIDE::LEFT,  REGION_3, 12  , SHORE_SPACE_BASE,  ),
      13 => new ShoreSpace( 13, SHORE_SIDE::RIGHT, REGION_3, 6  ,  SHORE_SPACE_BASE,  ),      
      14 => new ShoreSpace( 14, SHORE_SIDE::LEFT,  REGION_3, 5 ,   SHORE_SPACE_STARTING_BUILDING_FOR_2,  ),      
      15 => new ShoreSpace( 15, SHORE_SIDE::RIGHT, REGION_3, 11  , SHORE_SPACE_BASE,  ),   

      16 => new ShoreSpace( 16, SHORE_SIDE::LEFT,  REGION_4, 9  ,  SHORE_SPACE_BASE,  ),
      17 => new ShoreSpace( 17, SHORE_SIDE::LEFT,  REGION_4, 0  ,  SHORE_SPACE_IMPERIAL_MARKET,  ),
      18 => new ShoreSpace( 18, SHORE_SIDE::RIGHT, REGION_4, 12  , SHORE_SPACE_BASE,  ),      
      19 => new ShoreSpace( 19, SHORE_SIDE::LEFT,  REGION_4, 6 ,   SHORE_SPACE_BASE,  ),      
      20 => new ShoreSpace( 20, SHORE_SIDE::RIGHT, REGION_4, 6  ,  SHORE_SPACE_BASE,  ),  
      
      21 => new ShoreSpace( 21, SHORE_SIDE::LEFT,  REGION_5, 12,  SHORE_SPACE_BASE,  ),
      22 => new ShoreSpace( 22, SHORE_SIDE::LEFT,  REGION_5, 8 ,  SHORE_SPACE_STARTING_BUILDING_FOR_3,  ),
      23 => new ShoreSpace( 23, SHORE_SIDE::RIGHT, REGION_5, 9 ,  SHORE_SPACE_STARTING_BUILDING_FOR_2,  ),
      24 => new ShoreSpace( 24, SHORE_SIDE::LEFT,  REGION_5, 6 ,  SHORE_SPACE_BASE,  ),      
      25 => new ShoreSpace( 25, SHORE_SIDE::RIGHT, REGION_5, 11,  SHORE_SPACE_BASE,  ),  
      
      26 => new ShoreSpace( 26, SHORE_SIDE::LEFT,  REGION_6, 13,  SHORE_SPACE_BASE,  ),
      27 => new ShoreSpace( 27, SHORE_SIDE::RIGHT, REGION_6, 6 ,  SHORE_SPACE_BASE,  ),
      28 => new ShoreSpace( 28, SHORE_SIDE::LEFT,  REGION_6, 6 ,  SHORE_SPACE_BASE,  ),      
      29 => new ShoreSpace( 29, SHORE_SIDE::RIGHT, REGION_6, 0 ,  SHORE_SPACE_IMPERIAL_MARKET,  ),
      30 => new ShoreSpace( 30, SHORE_SIDE::RIGHT, REGION_6, 5 ,  SHORE_SPACE_BASE,  ),  
    ];
  }
  
  /**
   * @return array list of spaces id
   */
  public static function getImperialMarketSpaces(){
    return self::getSpacesByType(SHORE_SPACE_IMPERIAL_MARKET);
  }
  /**
   * @param int $nbPlayers number of players in game
   * @return array list of spaces id
   */
  public static function getStartingSpaces($nbPlayers){
    if($nbPlayers >= 4) return [];
    $spacesFor3 = self::getSpacesByType(SHORE_SPACE_STARTING_BUILDING_FOR_3);
    if($nbPlayers == 3) return $spacesFor3;
    $spacesFor2 = self::getSpacesByType(SHORE_SPACE_STARTING_BUILDING_FOR_2);
    //For 2 we use spaces '2' and '2/3'
    return array_merge($spacesFor2,$spacesFor3);
  }
  
  /**
   * @param int $pType the type to search
   * @return array list of spaces id
   */
  public static function getSpacesByType($pType){
    $spaceIds = [];
    $spaces = self::getShoreSpaces();
    foreach ($spaces as $id => $space) {
      if($pType == $space->type){
        $spaceIds[] = $space->id;
      }
    }
    return $spaceIds;
  }
  
  /**
   * @param int $pRegion the region to search
   * @return array list of spaces id
   */
  public static function getSpacesByRegion($pRegion){
    $spaceIds = [];
    $spaces = self::getShoreSpaces();
    foreach ($spaces as $id => $space) {
      if($pRegion == $space->region){
        $spaceIds[] = $space->id;
      }
    }
    return $spaceIds;
  }
  
  /**
   * @param int $pRegion the region to search
   * @return array list of spaces id
   */
  public static function getEmptySpaces($pRegion) : array{
    $spaces = ShoreSpaces::getSpacesByRegion($pRegion);
    $usedSpaces = Tiles::getUsedPositionsOnShore();
    return array_diff($spaces, $usedSpaces);
  }
  
  public static function getAllEmptySpaces() : array{
    $emptySpaces = [];
    foreach (REGIONS as $otherRegion){
      $emptySpaces = array_merge($emptySpaces,ShoreSpaces::getEmptySpaces($otherRegion));
    }
    return $emptySpaces;
  }

  /**
   * Search the 4 adjacent spaces to a given river space
   * @param int $riverSpace the space ON the river
   * @return array list of spaces id
   */
  public static function getAdjacentSpaces($riverSpace){
    switch($riverSpace){
      case 1://Top Starting space
        return [1,2,3,4];
      case 2:
        return [3,4,5,7];
      case 3:
        return [4,6,7,9];
      case 4:
        return [6,8,9,10];
      case 5:
        return [9,10,11,12];
      case 6:
        return [11,12,13,15];
      case 7:
        return [12,14,15,16];
      case 8://Middle Starting space
        return [15,16,17,18];
      case 9:
        return [17,18,19,21];
      case 10:
        return [18,20,21,23];
      case 11:
        return [21,22,23,25];
      case 12:
        return [22,24,25,26];
      case 13:
        return [25,26,27,29];
      case 14:
        return [26,28,29,30];
    }
    return [];
  }
  
  /**
   * Search the adjacent regions to a given river space
   * @param int $riverSpace the space ON the river
   * @return array list of regions id
   */
  public static function getAdjacentRegions($riverSpace){
    switch($riverSpace){
      case 1://Top Starting space
        return [REGION_1];
      case 2:
        return [REGION_1,REGION_2];
      case 3:
        return [REGION_1,REGION_2];
      case 4:
        return [REGION_2];
      case 5:
        return [REGION_2,REGION_3];
      case 6:
        return [REGION_3];
      case 7:
        return [REGION_3,REGION_4];
      case 8://Middle Starting space
        return [REGION_3,REGION_4];
      case 9:
        return [REGION_4,REGION_5];
      case 10:
        return [REGION_4,REGION_5];
      case 11:
        return [REGION_5];
      case 12:
        return [REGION_5,REGION_6];
      case 13:
        return [REGION_5,REGION_6];
      case 14:
        return [REGION_6];
    }
    return [];
  }
  
  public static function getAdjacentRiverSpaces(int $shoreSpace) : array 
  {
    $riverSpaces = [];
    for($k=1;$k<=NB_RIVER_SPACES;$k++){
      $shoresSpaces = ShoreSpaces::getAdjacentSpaces($k);
      if(in_array($shoreSpace,$shoresSpaces)) {
        $riverSpaces[] = $k;
      }
    }
    return $riverSpaces;
  }
  
  public static function getUniqueAdjacentRiverSpaces(array $shoreSpaces) : array 
  {
    $riverSpaces = [];
    foreach($shoreSpaces as $shoreSpace){
      $riverSpaces = array_merge($riverSpaces,ShoreSpaces::getAdjacentRiverSpaces($shoreSpace));
    }
    $uniqueRiverSpaces = array_values(array_unique($riverSpaces));
    return $uniqueRiverSpaces;
  }

  /**
   * @param array $shoreSpacesIds : list of shore spaces ids on the river
   * @param SHORE_SIDE $keepSide
   * @return array : FILTERED list of shore spaces ids on the river, keeping only 1 side
   */
  public static function filterBySide(array $shoreSpacesIds, SHORE_SIDE $keepSide) : array 
  {
    return array_filter($shoreSpacesIds,function ($spaceId) use ($keepSide) {
      $space = ShoreSpaces::getShoreSpace($spaceId);
      return $space->side == $keepSide;
    });
  }
}
