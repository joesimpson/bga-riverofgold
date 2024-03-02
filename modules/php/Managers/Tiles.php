<?php

namespace ROG\Managers;

use ROG\Models\Tile;

/* Class to manage all the tiles */

class Tiles extends \ROG\Helpers\Pieces
{
  protected static $table = 'tiles';
  protected static $prefix = 'tile_';
  protected static $autoIncrement = true;
  protected static $autoremovePrefix = false;
  protected static $customFields = ['player_id', 'type', 'subtype'];

  protected static function cast($row)
  {
    $type = $row['type'];
    $subtype = $row['subtype'];
    switch ($subtype) {
      case TILE_TYPE_SCORING:
        $data = self::getScoringTiles()[$type];
        return new \ROG\Models\ScoringTile($row, $data);
      case TILE_TYPE_MASTERY_CARD:
        $data = self::getMasteryCards()[$type];
        return new \ROG\Models\MasteryCard($row, $data);
      case TILE_TYPE_BUILDING:
        $data = self::getBuildingTiles()[$type];
        return new \ROG\Models\BuildingTile($row, $data);
    }
    $data = [];
    return new Tile($row, $data);
  }

  /**
   * @param int $currentPlayerId Id of current player loading the game
   * @return array all tiles visible by this player
   */
  public static function getUiData($currentPlayerId)
  {
    return self::getInLocation(TILE_LOCATION_SCORING)
      ->merge(self::getInLocation(TILE_LOCATION_MASTERY_CARD))
      ->merge(self::getInLocation(TILE_LOCATION_BUILDING_ROW))
      ->ui();
  } 
   

  /* Creation of the tiles */
  public static function setupNewGame($players, $options)
  {
    $tiles = [];

    $nbPlayers = count($players);
    $scoringTiles = self::getScoringTiles();
    foreach ($scoringTiles as $type => $tile) {
      if( in_array($nbPlayers,$tile['nbPlayers'])){
        $tiles[] = [
          'location' => TILE_LOCATION_SCORING,
          'type' => $type,
          'subtype' => TILE_TYPE_SCORING,
        ];
      }
    }
    
    $masteryCards = self::getMasteryCards();
    foreach ($masteryCards as $type => $tile) {
      if( in_array($nbPlayers,$tile['nbPlayers'])){
        $tiles[] = [
          'location' => TILE_LOCATION_MASTERY_CARD,
          'type' => $type,
          'subtype' => TILE_TYPE_MASTERY_CARD,
        ];
      }
    }
    
    $buildingTiles = self::getBuildingTiles();
    foreach ($buildingTiles as $type => $tile) {
      $era = $tile['era'];
      if( $era == 0){
        //TODO JSA manage starting tiles (with 2 of some)
      }
      else {
        $tiles[] = [
          'location' => TILE_LOCATION_BUILDING_DECK.$era,
          'type' => $type,
          'subtype' => TILE_TYPE_BUILDING,
        ];
      }
    }

    if(count($tiles)>0){
      self::create($tiles);
      self::shuffle(TILE_LOCATION_SCORING);
      self::shuffle(TILE_LOCATION_MASTERY_CARD);
      self::shuffle(TILE_LOCATION_BUILDING_DECK_ERA_1);
      self::shuffle(TILE_LOCATION_BUILDING_DECK_ERA_2);

      //Remove 3 mastery cards 
      $masteryCards = self::getTopOf(TILE_LOCATION_MASTERY_CARD,3);
      foreach ($masteryCards as $tileId => $tile) {
        self::DB()->delete($tileId);
      }
      
      //Keep 16 /14/12 era 1 tiles <=> remove 8/10/12 tiles
      $nbBuildingToRemove = [2=>12, 3=>10, 4=>8];
      $buildingTiles = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_1,$nbBuildingToRemove[$nbPlayers]);
      foreach ($buildingTiles as $tileId => $tile) {
        self::DB()->delete($tileId);
      }

      //Keep 13 /11/9 era 2 tiles <=> remove 3/5/7 tiles
      $nbBuildingToRemove = [2=>7, 3=>5, 4=>3];
      $buildingTiles = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_2,$nbBuildingToRemove[$nbPlayers]);
      foreach ($buildingTiles as $tileId => $tile) {
        self::DB()->delete($tileId);
      }

      //Draw 4
      $buildingTiles = self::pickForLocation(4,TILE_LOCATION_BUILDING_DECK_ERA_1,TILE_LOCATION_BUILDING_ROW);
      $k = 0;
      foreach ($buildingTiles as $tileId => $tile) {
        $k++;
        $tile->setState($k);
      }
    }
  }
 
  /**
   * @return array of all the different types of Scoring Tiles
   */
  public static function getScoringTiles()
  {
    $f = function ($t) {
      return [
        'nbPlayers' => $t[0],
        'scores' => $t[1],
      ];
    };
    return [
      // 12 unique 
      1 => $f([ [2],   [3,]      ]), 
      2 => $f([ [2],   [5,2]     ]), 
      3 => $f([ [2],   [4,2]     ]), 
      4 => $f([ [2],   [8,4]     ]), 
      5 => $f([ [2],   [6,3]     ]), 
      6 => $f([ [2],   [7,3]     ]), 
      7 => $f([ [3,4], [7,3]     ]), 
      8 => $f([ [3,4], [9,5]     ]), 
      9 => $f([ [3,4], [8,4]     ]), 
      10 => $f([[3,4], [12,8,4]  ]), 
      11 => $f([[3,4], [10,6,2]  ]), 
      12 => $f([[3,4], [11,7,3]  ]), 
    ];
  }
  
  /**
   * @return array of all the different types of Mastery Cards
   */
  public static function getMasteryCards()
  {
    $f = function ($t) {
      return [
        'nbPlayers' => $t[0],
        'scores' => $t[1],
        'scoringType' => $t[2],
      ];
    };
    return [
      // 12 unique 
      1 => $f([ [2],   [5]     , MASTERY_TYPE_AIR     ]), 
      2 => $f([ [2],   [5]     , MASTERY_TYPE_COURTS  ]), 
      3 => $f([ [2],   [5]     , MASTERY_TYPE_EARTH   ]), 
      4 => $f([ [2],   [5]     , MASTERY_TYPE_FIRE    ]), 
      5 => $f([ [2],   [5]     , MASTERY_TYPE_VOID    ]), 
      6 => $f([ [2],   [5]     , MASTERY_TYPE_WATER   ]), 
      7 => $f([ [3,4], [7,5,3] , MASTERY_TYPE_AIR     ]), 
      8 => $f([ [3,4], [7,5,3] , MASTERY_TYPE_COURTS  ]), 
      9 => $f([ [3,4], [7,5,3] , MASTERY_TYPE_EARTH   ]), 
      10 => $f([[3,4], [7,5,3] , MASTERY_TYPE_FIRE    ]), 
      11 => $f([[3,4], [7,5,3] , MASTERY_TYPE_VOID    ]), 
      12 => $f([[3,4], [7,5,3] , MASTERY_TYPE_WATER   ]), 
    ];
  }
  
  /**
   * @return array of all the different types of Building Tiles
   */
  public static function getBuildingTiles()
  {
    $f = function ($t) {
      return [
        //0 is for starting tiles
        'era' => $t[0],
        //influence bonus
        'bonus' => $t[1],
        'buildingType' => $t[2],
      ];
    };
    return [
      //49 various tiles - 46 unique
      // 2 identical starting Blue 
      1 => $f([ 0, 0  , BUILDING_TYPE_PORT  ]), 
      //6 blue
      2 => $f([ 1, 1  , BUILDING_TYPE_PORT  ]), 
      3 => $f([ 1, 0  , BUILDING_TYPE_PORT  ]), 
      4 => $f([ 1, 4  , BUILDING_TYPE_PORT  ]), 
      5 => $f([ 1, 3  , BUILDING_TYPE_PORT  ]), 
      6 => $f([ 1, 3  , BUILDING_TYPE_PORT  ]), 
      7 => $f([ 1, 1  , BUILDING_TYPE_PORT  ]), 
      //6 green
      8  => $f([ 1, 4  , BUILDING_TYPE_MARKET  ]), 
      9  => $f([ 1, 1  , BUILDING_TYPE_MARKET  ]), 
      10 => $f([ 1, 2  , BUILDING_TYPE_MARKET  ]), 
      11 => $f([ 1, 3  , BUILDING_TYPE_MARKET  ]), 
      12 => $f([ 1, 4  , BUILDING_TYPE_MARKET  ]), 
      13 => $f([ 1, 3  , BUILDING_TYPE_MARKET  ]), 
      //6 orange
      14 => $f([ 1, 1  , BUILDING_TYPE_MANOR  ]), 
      15 => $f([ 1, 0  , BUILDING_TYPE_MANOR  ]), 
      16 => $f([ 1, 4  , BUILDING_TYPE_MANOR  ]), 
      17 => $f([ 1, 3  , BUILDING_TYPE_MANOR  ]), 
      18 => $f([ 1, 4  , BUILDING_TYPE_MANOR  ]), 
      19 => $f([ 1, 3  , BUILDING_TYPE_MANOR  ]), 
      //6 red
      20 => $f([ 1, 1  , BUILDING_TYPE_SHRINE  ]), 
      21 => $f([ 1, 0  , BUILDING_TYPE_SHRINE  ]), 
      22 => $f([ 1, 4  , BUILDING_TYPE_SHRINE  ]), 
      23 => $f([ 1, 3  , BUILDING_TYPE_SHRINE  ]), 
      24 => $f([ 1, 2  , BUILDING_TYPE_SHRINE  ]), 
      25 => $f([ 1, 3  , BUILDING_TYPE_SHRINE  ]), 
      
      //4 blue ERA 2
      26 => $f([ 2, 3  , BUILDING_TYPE_PORT  ]), 
      27 => $f([ 2, 4  , BUILDING_TYPE_PORT  ]), 
      28 => $f([ 2, 4  , BUILDING_TYPE_PORT  ]), 
      29 => $f([ 2, 2  , BUILDING_TYPE_PORT  ]), 
      //4 green ERA 2
      30 => $f([ 2, 5  , BUILDING_TYPE_MARKET  ]), 
      31 => $f([ 2, 5  , BUILDING_TYPE_MARKET  ]), 
      32 => $f([ 2, 2  , BUILDING_TYPE_MARKET  ]), 
      33 => $f([ 2, 3  , BUILDING_TYPE_MARKET  ]), 
      //4 orange ERA 2
      34 => $f([ 2, 5  , BUILDING_TYPE_MANOR  ]), 
      35 => $f([ 2, 4  , BUILDING_TYPE_MANOR  ]), 
      36 => $f([ 2, 3  , BUILDING_TYPE_MANOR  ]), 
      37 => $f([ 2, 2  , BUILDING_TYPE_MANOR  ]), 
      //4 red ERA 2
      38 => $f([ 2, 5  , BUILDING_TYPE_SHRINE  ]), 
      39 => $f([ 2, 5  , BUILDING_TYPE_SHRINE  ]), 
      40 => $f([ 2, 4  , BUILDING_TYPE_SHRINE  ]), 
      41 => $f([ 2, 5  , BUILDING_TYPE_SHRINE  ]), 

      // 2 identical starting orange 
      42 => $f([ 0, 0  , BUILDING_TYPE_MANOR  ]), 
      // 2 identical starting red 
      43 => $f([ 0, 0  , BUILDING_TYPE_SHRINE  ]), 
      // 3 starting green 
      44 => $f([ 0, 0  , BUILDING_TYPE_MARKET  ]), 
      45 => $f([ 0, 0  , BUILDING_TYPE_MARKET  ]), 
      46 => $f([ 0, 0  , BUILDING_TYPE_MARKET  ]), 
    ];
  }
  
}
