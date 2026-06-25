<?php

namespace ROG\Managers;

use ROG\Helpers\Collection;
use ROG\Models\CitySpace;

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

  /*
  public static function getCitySpace(int $row, int $col) : ?CitySpace
  {
    return (new Collection(self::getAllCitySpaces()))->filter(function(CitySpace $s) use ($row, $col) { return $s->row == $row && $s->column == $col;})->first();
  }
  */

  /**
   * @return array of all the different types of ShoreSpace
   */
  public static function getAllCitySpaces() : array
  {
    return [
      1   => new CitySpace(1 , 1,1, 1, [ BONUS_TYPE_CHOICE => 2, ] ),
      2   => new CitySpace(2 , 1,2, 2, [ BONUS_TYPE_SECOND_MARKER_ON_BUILDING => 1,  ] ),
      3   => new CitySpace(3 , 1,3, 1, [  ] ), //BONUS_TYPE_BUILDING_ROW_REWARDS
      4   => new CitySpace(4 , 1,4, 2, [  ] ), //BONUS_TYPE_DELIVER_TOP

      5   => new CitySpace(5 , 2,1, 3, [ RESOURCE_TYPE_MOON=>1, RESOURCE_TYPE_SUN => 1, ] ),
      6   => new CitySpace(6 , 2,2, 4, [  ] ),
      7   => new CitySpace(7 , 2,3, 3, [  ] ),
      8   => new CitySpace(8 , 2,4, 4, [  ] ),
      
      9   => new CitySpace(9 , 3,1, 5, [ RESOURCE_TYPE_MONEY => 7,  ] ),
      10  => new CitySpace(10, 3,2, 6, [ BONUS_TYPE_POINTS => 5, ] ),
      11  => new CitySpace(11, 3,3, 5, [  ] ),
      12  => new CitySpace(12, 3,4, 6, [  ] ),
    ];
  }
  
  /**
   * @param int $pRegion the region to search
   * @return array list of spaces id
   */
  public static function getSpacesByRegion(int $pRegion) : array {
    $spaceIds = [];
    $spaces = self::getAllCitySpaces();
    foreach ($spaces as $id => $space) {
      if($pRegion == $space->region){
        $spaceIds[] = $space->id;
      }
    }
    return $spaceIds;
  }
  
}
