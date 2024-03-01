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
    return self::getInLocation(TILE_LOCATION_SCORING)->ui();
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

    if(count($tiles)>0){

      self::create($tiles);
      self::shuffle(TILE_LOCATION_SCORING);
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
  
}
