<?php

namespace ROG\Managers;

use ROG\Helpers\Collection;
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
  public static function getShoreSpace($position)
  {
    return self::getShoreSpaces()[$position];
  }

  /**
   * @return array of all the different types of ShoreSpace
   */
  public static function getShoreSpaces()
  {
    //function to init spaces
    $f = function ($t) {
      return new ShoreSpace($t[0], $t[1], $t[2],$t[3]);
    };
    return [
      //30 spaces
      1 => $f([ 1,  REGION_1, 6 , SHORE_SPACE_BASE,  ]),
      2 => $f([ 2,  REGION_1, 5 , SHORE_SPACE_BASE,  ]),
      3 => $f([ 3,  REGION_1, 8 , SHORE_SPACE_STARTING_BUILDING_FOR_3,  ]),
      4 => $f([ 4,  REGION_1, 12, SHORE_SPACE_BASE,  ]),
      5 => $f([ 5,  REGION_1, 6 , SHORE_SPACE_BASE,  ]),

      6  => $f([ 6 ,  REGION_2, 0  , SHORE_SPACE_IMPERIAL_MARKET,  ]),
      7  => $f([ 7 ,  REGION_2, 8  , SHORE_SPACE_STARTING_BUILDING_FOR_2,  ]),
      8  => $f([ 8 ,  REGION_2, 6  , SHORE_SPACE_BASE,  ]),      
      9  => $f([ 9 ,  REGION_2, 12 , SHORE_SPACE_BASE,  ]),      
      10 => $f([ 10,  REGION_2, 9  , SHORE_SPACE_BASE,  ]),      

      11 => $f([ 11,  REGION_3, 8  ,  SHORE_SPACE_STARTING_BUILDING_FOR_3,  ]),
      12 => $f([ 12,  REGION_3, 12  , SHORE_SPACE_BASE,  ]),
      13 => $f([ 13,  REGION_3, 6  ,  SHORE_SPACE_BASE,  ]),      
      14 => $f([ 14,  REGION_3, 5 ,   SHORE_SPACE_STARTING_BUILDING_FOR_2,  ]),      
      15 => $f([ 15,  REGION_3, 11  , SHORE_SPACE_BASE,  ]),   

      16 => $f([ 16,  REGION_4, 9  ,  SHORE_SPACE_BASE,  ]),
      17 => $f([ 17,  REGION_4, 0  ,  SHORE_SPACE_IMPERIAL_MARKET,  ]),
      18 => $f([ 18,  REGION_4, 12  , SHORE_SPACE_BASE,  ]),      
      19 => $f([ 19,  REGION_4, 6 ,   SHORE_SPACE_BASE,  ]),      
      20 => $f([ 20,  REGION_4, 6  ,  SHORE_SPACE_BASE,  ]),  
      
      21 => $f([ 21,  REGION_5, 12,  SHORE_SPACE_BASE,  ]),
      22 => $f([ 22,  REGION_5, 8 ,  SHORE_SPACE_STARTING_BUILDING_FOR_3,  ]),
      23 => $f([ 23,  REGION_5, 9 ,  SHORE_SPACE_STARTING_BUILDING_FOR_2,  ]),      
      24 => $f([ 24,  REGION_5, 6 ,  SHORE_SPACE_BASE,  ]),      
      25 => $f([ 25,  REGION_5, 11,  SHORE_SPACE_BASE,  ]),  
      
      26 => $f([ 26,  REGION_6, 13,  SHORE_SPACE_BASE,  ]),
      27 => $f([ 27,  REGION_6, 6 ,  SHORE_SPACE_BASE,  ]),
      28 => $f([ 28,  REGION_6, 6 ,  SHORE_SPACE_BASE,  ]),      
      29 => $f([ 29,  REGION_6, 0 ,  SHORE_SPACE_IMPERIAL_MARKET,  ]),      
      30 => $f([ 30,  REGION_6, 5 ,  SHORE_SPACE_BASE,  ]),  
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
    if($nbPlayers == 4) return [];
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
  public static function getEmptySpaces($pRegion){
    $spaces = ShoreSpaces::getSpacesByRegion($pRegion);
    $usedSpaces = Tiles::getUsedPositionsOnShore();
    return array_diff($spaces, $usedSpaces);
  }
}
