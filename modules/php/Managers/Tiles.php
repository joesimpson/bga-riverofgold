<?php

namespace ROG\Managers;

use ROG\Core\Game;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Models\Reward;
use ROG\Models\Tile;

/* Class to manage all the tiles */

class Tiles extends \ROG\Helpers\Pieces
{
  protected static $table = 'tiles';
  protected static $prefix = 'tile_';
  protected static $autoIncrement = true;
  protected static $autoremovePrefix = false;
  protected static $customFields = [ 'type', 'subtype'];

  protected static function cast($row)
  {
    $type = isset($row['type']) ? $row['type'] : null;
    $subtype = isset($row['subtype']) ? $row['subtype'] : null;
    switch ($subtype) {
      case TILE_TYPE_SCORING:
        $data = self::getScoringTilesTypes()[$type];
        return new \ROG\Models\ScoringTile($row, $data);
      case TILE_TYPE_MASTERY_CARD:
        $data = self::getMasteryCardsTypes()[$type];
        return new \ROG\Models\MasteryCard($row, $data);
      case TILE_TYPE_BUILDING:
        $data = self::getBuildingTilesTypes()[$type];
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
    $nextEra1Card = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_1);
    $nextEra2Card = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_2);

    $cards = self::getInLocationOrdered(TILE_LOCATION_SCORING)
      ->merge(self::getInLocation(TILE_LOCATION_MASTERY_CARD))
      ->merge(self::getInLocationOrdered(TILE_LOCATION_BUILDING_ROW))
      ->merge(self::getInLocationOrdered(TILE_LOCATION_BUILDING_SHORE));
    if(isset($nextEra1Card)) $cards->append($nextEra1Card);
    if(isset($nextEra2Card)) $cards->append($nextEra2Card);
    return $cards->ui();
  } 
   
  /**
   * @return array of int
   */
  public static function getUsedPositionsOnShore()
  {
    
    return self::DB()->select([self::$prefix.'state'])
      ->where(static::$prefix . 'location', TILE_LOCATION_BUILDING_SHORE)
      ->get()
      ->map(function ($tile) {
        return $tile->state;
      })
      ->toArray();
  } 
  /**
   * @param int $position
   * @return ?BuildingTile tile or null
   */
  public static function getTileOnShoreSpace($position)
  {
    return self::getInLocation(TILE_LOCATION_BUILDING_SHORE,$position)->first();
  }
  /**
   * @param int $subType
   * @param array $tilesTypes
   * @return array of int
   */
  public static function getIdsByType($subType,$tilesTypes)
  {
    return self::DB()->select([self::$prefix.'id'])
      ->where( 'subType', $subType)
      ->whereIn( 'type', $tilesTypes)
      ->get()
      ->map(function ($tile) {
        return $tile->id;
      })
      ->toArray();
  } 
  /**
   * @param int $subType
   * @param array $tilesTypes (array of int)
   * @return Collection of Tile
   */
  public static function getAllByType($subType, $tilesTypes)
  {
    return self::DB()
      ->where( 'subType', $subType)
      ->whereIn( 'type', $tilesTypes)
      ->get();
  } 
  
  /**
   * @return Collection of ScoringTile
   */
  public static function getScoringTiles()
  {
    return self::getAllByType(TILE_TYPE_SCORING,array_keys(self::getScoringTilesTypes()));
  } 
  /**
   * @return Collection of MasteryCard
   */
  public static function getMasteryCards()
  {
    return self::getAllByType(TILE_TYPE_MASTERY_CARD,array_keys(self::getMasteryCardsTypes()));
  } 
  /**
   * @return Collection of BuildingTile
   */
  public static function getBuildingTiles()
  {
    return self::getAllByType(TILE_TYPE_BUILDING,array_keys(self::getBuildingTilesTypes()));
  } 

  /**
   */
  public static function removeLastInBuildingRow()
  {
    $tile = self::getInLocation(TILE_LOCATION_BUILDING_ROW,BUILDING_ROW_END)->first();
    if(isset($tile)){
      Notifications::discardBuildingRow($tile);
      //self::DB()->delete($tile->getId())->run();
      $tile->setLocation(TILE_LOCATION_DISCARD);
    }
  } 
  //////////////////////////////////////////////////////////////////////
  /** Creation of the tiles */
  public static function setupNewGame($players, $options)
  {
    $tiles = [];

    $nbPlayers = count($players);
    $scoringTiles = self::getScoringTilesTypes();
    foreach ($scoringTiles as $type => $tile) {
      if( in_array($nbPlayers,$tile['nbPlayers'])){
        $tiles[] = [
          'location' => TILE_LOCATION_SCORING,
          'type' => $type,
          'subtype' => TILE_TYPE_SCORING,
        ];
      }
    }
    
    $masteryCards = self::getMasteryCardsTypes();
    foreach ($masteryCards as $type => $tile) {
      if( in_array($nbPlayers,$tile['nbPlayers'])){
        $tiles[] = [
          'location' => TILE_LOCATION_MASTERY_CARD,
          'type' => $type,
          'subtype' => TILE_TYPE_MASTERY_CARD,
        ];
      }
    }
    
    $buildingTiles = self::getBuildingTilesTypes();
    foreach ($buildingTiles as $type => $tile) {
      $era = $tile['era'];
      if( $era == 0){
        //manage starting tiles (with 2 of some)
        $tiles[] = [
          'location' => TILE_LOCATION_BUILDING_SHORE,
          'type' => $type,
          'subtype' => TILE_TYPE_BUILDING,
          'nbr' => $tile['nbr'],
        ];
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
      self::shuffle(TILE_LOCATION_BUILDING_SHORE);
      self::shuffle(TILE_LOCATION_BUILDING_DECK_ERA_1);
      self::shuffle(TILE_LOCATION_BUILDING_DECK_ERA_2);

      //Remove 3 mastery cards 
      $masteryCards = self::getTopOf(TILE_LOCATION_MASTERY_CARD,3);
      foreach ($masteryCards as $tileId => $tile) {
        self::DB()->delete($tileId);
      }
      
      //Random place for Imperial Markets & starting tiles
      $startings = self::getInLocationOrdered(TILE_LOCATION_BUILDING_SHORE);
      $marketSpaces = ShoreSpaces::getImperialMarketSpaces();
      $startingSpaces = ShoreSpaces::getStartingSpaces($nbPlayers);
      foreach ($startings as $tileId => $tile) {
        if(BUILDING_TYPE_MARKET == $tile->buildingType){
          $space = $marketSpaces[array_rand($marketSpaces)];
          $marketSpaces = array_diff($marketSpaces,[$space] );
          $tile->setState($space);
        }
        else {
          if(count($startingSpaces) == 0){
            //no more spaces, let's delete tile
            self::DB()->delete($tileId);
          }
          else{
            $space = $startingSpaces[array_rand($startingSpaces)];
            $startingSpaces = array_diff($startingSpaces,[$space] );
            $tile->setState($space);
          }
        }
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
   * Refill each of the 4 spaces of the building row if the deck is not empty
   * @return array  [1=> bool,2=> bool] true when last Era tile is moved to the row,
   * false otherwise
   */
  public static function refillBuildingRow()
  {
    Game::get()->trace("refillBuildingRow()");
    
    $lastEra1TileMoved = false;
    $lastEra2TileMoved = false;
    $slidedTiles = [];
    for($k = BUILDING_ROW_END; $k>0;$k--){
      $buildingTile = self::getInLocation(TILE_LOCATION_BUILDING_ROW,$k)->first();
      if($k > 1){
        //WE MUST SLIDE TILES FROM RIGHT TO LEFT ! Only 1 tile must be missing at a time
        if(!isset($buildingTile)){
          $rightPos = $k -1;
          $buildingTileRight = self::getInLocation(TILE_LOCATION_BUILDING_ROW,$rightPos )->first();
          if(isset($buildingTileRight)){
            $buildingTileRight->setPosition($k);
            $slidedTiles[$rightPos] = $buildingTileRight;
            Game::get()->trace("refillBuildingRow() : sliding from $rightPos to $k");
          }
        }
      }
    }

    if($slidedTiles) Notifications::slideBuildingRow($slidedTiles);
    
    //REFILL MISSING TILE #1 from building board
    $k = 1;
    $buildingTile = self::getInLocation(TILE_LOCATION_BUILDING_ROW,$k)->first();
      //Game::get()->trace("Checking to refill $k : buildingTile is ".json_encode($buildingTile));
      if(!isset($buildingTile)){
        $buildingTile = self::pickOneForLocation(TILE_LOCATION_BUILDING_DECK_ERA_1,TILE_LOCATION_BUILDING_ROW,$k,false);
        if(isset($buildingTile)){
          $nextEra1Card = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_1);
          $nextEra2Card = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_2);
          Notifications::refillBuildingRow($buildingTile,$nextEra1Card,$nextEra2Card);
          if(!isset($nextEra1Card)){
            $lastEra1TileMoved = true;
          }
        }
      }
      if(!isset($buildingTile)){
        Game::get()->trace("Cannot refill $k from Era 1, check Era 2");
        $buildingTile = self::pickOneForLocation(TILE_LOCATION_BUILDING_DECK_ERA_2,TILE_LOCATION_BUILDING_ROW,$k,false);
        if(isset($buildingTile)){
          $nextEra1Card = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_1);
          $nextEra2Card = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_2);
          Notifications::refillBuildingRow($buildingTile,$nextEra1Card,$nextEra2Card);
          if(!isset($nextEra2Card)){
            $lastEra2TileMoved = true;
          }
        }
        else {
          Game::get()->trace("Cannot refill $k from Era 2, we must be near the end");
        }
      }
    return [1=> $lastEra1TileMoved,2=> $lastEra2TileMoved];
  }

 
  /**
   * @return array of all the different types of Scoring Tiles
   */
  public static function getScoringTilesTypes()
  {
    $f = function ($t) {
      return [
        'nbPlayers' => $t[0],
        'scores' => $t[1],
        'checkSpacesBetween' => $t[2],
      ];
    };
    return [
      // 12 unique 
      1 => $f([ [2],   [3,]     ,null  ]), 
      2 => $f([ [2],   [5,2]    ,NB_SPACES_BETWEEN_2P_SCORINGTILE  ]), 
      3 => $f([ [2],   [4,2]    ,NB_SPACES_BETWEEN_2P_SCORINGTILE  ]), 
      4 => $f([ [2],   [8,4]    ,NB_SPACES_BETWEEN_2P_SCORINGTILE  ]), 
      5 => $f([ [2],   [6,3]    ,NB_SPACES_BETWEEN_2P_SCORINGTILE  ]), 
      6 => $f([ [2],   [7,3]    ,NB_SPACES_BETWEEN_2P_SCORINGTILE  ]), 
      7 => $f([ [3,4], [7,3]    ,null ]), 
      8 => $f([ [3,4], [9,5]    ,null ]), 
      9 => $f([ [3,4], [8,4]    ,null ]), 
      10 => $f([[3,4], [12,8,4] ,null ]), 
      11 => $f([[3,4], [10,6,2] ,null ]), 
      12 => $f([[3,4], [11,7,3] ,null ]), 
    ];
  }
  
  /**
   * @return array of all the different types of Mastery Cards
   */
  public static function getMasteryCardsTypes()
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
  public static function getBuildingTilesTypes()
  {
    $f = function ($t) {
      return [
        //0 is for starting tiles
        'era' => $t[0],
        //influence bonus
        'bonus' => $t[1],
        'buildingType' => $t[2],
        'ownerRewardArray' => $t[3],
        'visitorRewardArray' => $t[4],
        'nbr' => isset($t[5]) ? $t[5] : 1,
      ];
    };
    return [
      //49 various tiles - 46 unique
      // 2 identical starting Blue 
      1 => $f([ 0, 0  , BUILDING_TYPE_PORT,   [], [RESOURCE_TYPE_MONEY=>3], 2]), 
      //6 blue
      2 => $f([ 1, 1  , BUILDING_TYPE_PORT,   [RESOURCE_TYPE_RICE=>1],                            [RESOURCE_TYPE_MONEY=>3],  ]), 
      3 => $f([ 1, 0  , BUILDING_TYPE_PORT,   [RESOURCE_TYPE_MONEY=>1, RESOURCE_TYPE_POTTERY=>1,],[RESOURCE_TYPE_MONEY=>3],  ]), 
      4 => $f([ 1, 4  , BUILDING_TYPE_PORT,   [RESOURCE_TYPE_SUN=>1],                             [RESOURCE_TYPE_MONEY=>3],  ]), 
      5 => $f([ 1, 3  , BUILDING_TYPE_PORT,   [BONUS_TYPE_MONEY_PER_PORT=>1],                     [RESOURCE_TYPE_MONEY=>3],  ]), 
      6 => $f([ 1, 3  , BUILDING_TYPE_PORT,   [BONUS_TYPE_POINTS=>1],                             [RESOURCE_TYPE_MONEY=>3],  ]), 
      7 => $f([ 1, 1  , BUILDING_TYPE_PORT,   [RESOURCE_TYPE_SUN=>1, BONUS_TYPE_POINTS=>1],       [RESOURCE_TYPE_MONEY=>3],  ]), 
      //6 green
      8  => $f([ 1, 4  , BUILDING_TYPE_MARKET,[RESOURCE_TYPE_MONEY=>2,],                      [RESOURCE_TYPE_SILK=>1],      ]), 
      9  => $f([ 1, 1  , BUILDING_TYPE_MARKET,[RESOURCE_TYPE_SUN=>1, BONUS_TYPE_POINTS=>1],   [RESOURCE_TYPE_RICE=>1],      ]), 
      10 => $f([ 1, 2  , BUILDING_TYPE_MARKET,[RESOURCE_TYPE_MONEY=>1, BONUS_TYPE_POINTS=>1], [RESOURCE_TYPE_POTTERY=>1],   ]), 
      11 => $f([ 1, 3  , BUILDING_TYPE_MARKET,[BONUS_TYPE_POINTS=>1],                         [RESOURCE_TYPE_SILK=>1],      ]), 
      12 => $f([ 1, 4  , BUILDING_TYPE_MARKET,[RESOURCE_TYPE_SUN=>1],                         [RESOURCE_TYPE_RICE=>1],      ]), 
      13 => $f([ 1, 3  , BUILDING_TYPE_MARKET,[BONUS_TYPE_MONEY_PER_MARKET=>1 ],              [RESOURCE_TYPE_POTTERY=>1],   ]), 
      //6 orange
      14 => $f([ 1, 1  , BUILDING_TYPE_MANOR ,[RESOURCE_TYPE_SILK=>1],                        [BONUS_TYPE_INFLUENCE=>2], ]), 
      15 => $f([ 1, 0  , BUILDING_TYPE_MANOR ,[RESOURCE_TYPE_MONEY=>1, RESOURCE_TYPE_RICE=>1],[BONUS_TYPE_INFLUENCE=>2], ]),
      16 => $f([ 1, 4  , BUILDING_TYPE_MANOR ,[RESOURCE_TYPE_SUN=>1,],                        [BONUS_TYPE_INFLUENCE=>2], ]),
      17 => $f([ 1, 3  , BUILDING_TYPE_MANOR ,[BONUS_TYPE_MONEY_PER_MANOR=>1],                [BONUS_TYPE_INFLUENCE=>2], ]),
      18 => $f([ 1, 4  , BUILDING_TYPE_MANOR ,[BONUS_TYPE_MONEY_PER_CUSTOMER=>1],             [BONUS_TYPE_INFLUENCE=>2], ]),
      19 => $f([ 1, 3  , BUILDING_TYPE_MANOR ,[BONUS_TYPE_POINTS=>1],                         [BONUS_TYPE_INFLUENCE=>2], ]),
      //6 red
      20 => $f([ 1, 1  , BUILDING_TYPE_SHRINE,[RESOURCE_TYPE_POTTERY=>1],                     [BONUS_TYPE_POINTS=>2], ]), 
      21 => $f([ 1, 0  , BUILDING_TYPE_SHRINE,[RESOURCE_TYPE_MONEY=>1, RESOURCE_TYPE_SILK=>1],[BONUS_TYPE_POINTS=>2], ]), 
      22 => $f([ 1, 4  , BUILDING_TYPE_SHRINE,[RESOURCE_TYPE_SUN=>1],                         [BONUS_TYPE_POINTS=>2], ]), 
      23 => $f([ 1, 3  , BUILDING_TYPE_SHRINE,[BONUS_TYPE_MONEY_PER_SHRINE=>1],               [BONUS_TYPE_POINTS=>2], ]), 
      24 => $f([ 1, 2  , BUILDING_TYPE_SHRINE,[RESOURCE_TYPE_MONEY=>1, BONUS_TYPE_POINTS=>1], [BONUS_TYPE_POINTS=>2], ]), 
      25 => $f([ 1, 3  , BUILDING_TYPE_SHRINE,[BONUS_TYPE_POINTS=>1],                         [BONUS_TYPE_POINTS=>2], ]), 
      
      //4 blue ERA 2
      26 => $f([ 2, 3  , BUILDING_TYPE_PORT,  [BONUS_TYPE_POINTS=>2],                         [RESOURCE_TYPE_MONEY=>5],  ]),
      27 => $f([ 2, 4  , BUILDING_TYPE_PORT,  [BONUS_TYPE_CHOICE=>1],                         [RESOURCE_TYPE_MONEY=>5],  ]),
      28 => $f([ 2, 4  , BUILDING_TYPE_PORT,  [RESOURCE_TYPE_RICE=>1, RESOURCE_TYPE_SUN=>1],  [RESOURCE_TYPE_MONEY=>5],  ]),
      29 => $f([ 2, 2  , BUILDING_TYPE_PORT,  [BONUS_TYPE_POINTS=>3],                         [RESOURCE_TYPE_MONEY=>5],  ]),
      //4 green ERA 2
      30 => $f([ 2, 5  , BUILDING_TYPE_MARKET,[RESOURCE_TYPE_SUN=>1],     [BONUS_TYPE_CHOICE=>1],  ]),
      31 => $f([ 2, 5  , BUILDING_TYPE_MARKET,[BONUS_TYPE_INFLUENCE=>1],  [BONUS_TYPE_CHOICE=>1],  ]),
      32 => $f([ 2, 2  , BUILDING_TYPE_MARKET,[BONUS_TYPE_POINTS=>3],     [BONUS_TYPE_CHOICE=>1],  ]),
      33 => $f([ 2, 3  , BUILDING_TYPE_MARKET,[BONUS_TYPE_POINTS=>2],     [BONUS_TYPE_CHOICE=>1],  ]),
      //4 orange ERA 2
      34 => $f([ 2, 5  , BUILDING_TYPE_MANOR, [BONUS_TYPE_INFLUENCE=>1],                        [BONUS_TYPE_INFLUENCE=>3], ]),
      35 => $f([ 2, 4  , BUILDING_TYPE_MANOR, [RESOURCE_TYPE_POTTERY=>1, RESOURCE_TYPE_SUN=>1], [BONUS_TYPE_INFLUENCE=>3], ]),
      36 => $f([ 2, 3  , BUILDING_TYPE_MANOR, [BONUS_TYPE_POINTS=>2],                           [BONUS_TYPE_INFLUENCE=>3], ]),
      37 => $f([ 2, 2  , BUILDING_TYPE_MANOR, [BONUS_TYPE_POINTS=>3],                           [BONUS_TYPE_INFLUENCE=>3], ]),
      //4 red ERA 2
      38 => $f([ 2, 5  , BUILDING_TYPE_SHRINE,[BONUS_TYPE_INFLUENCE=>1],  [BONUS_TYPE_POINTS=>3], ]), 
      39 => $f([ 2, 5  , BUILDING_TYPE_SHRINE,[RESOURCE_TYPE_SILK=>1],    [BONUS_TYPE_POINTS=>3], ]), 
      40 => $f([ 2, 4  , BUILDING_TYPE_SHRINE,[BONUS_TYPE_CHOICE=>1],     [BONUS_TYPE_POINTS=>3], ]), 
      41 => $f([ 2, 5  , BUILDING_TYPE_SHRINE,[RESOURCE_TYPE_SUN=>1],     [BONUS_TYPE_POINTS=>3], ]), 

      // 2 identical starting orange 
      42 => $f([ 0, 0  , BUILDING_TYPE_MANOR, [], [BONUS_TYPE_INFLUENCE=>2], 2]), 
      // 2 identical starting red 
      43 => $f([ 0, 0  , BUILDING_TYPE_SHRINE,[], [BONUS_TYPE_POINTS=>2], 2]), 
      // 3 starting green 
      44 => $f([ 0, 0  , BUILDING_TYPE_MARKET,[], [RESOURCE_TYPE_POTTERY=>1,RESOURCE_TYPE_SUN=>1]  ]), 
      45 => $f([ 0, 0  , BUILDING_TYPE_MARKET,[], [RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_SUN=>1]     ]), 
      46 => $f([ 0, 0  , BUILDING_TYPE_MARKET,[], [RESOURCE_TYPE_SILK=>1,BONUS_TYPE_DRAW =>1]      ]), 
    ];
  }
  
  /**
   * @param int $pType the type to search
   * @return array list of types
   */
  public static function getTilesTypesByBuilding($pType){
    $types = [];
    $buildingTiles = self::getBuildingTilesTypes();
    foreach ($buildingTiles as $type => $tile) {
      if($pType == $tile['buildingType']){
        $types[] = $type;
      }
    }
    return $types;
  }
}
