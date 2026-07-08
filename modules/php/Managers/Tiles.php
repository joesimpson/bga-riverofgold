<?php

namespace ROG\Managers;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Helpers\Utils;
use ROG\Models\BuildingTile;
use ROG\Models\MAIN_ACTION;
use ROG\Models\Meeple;
use ROG\Models\Player;
use ROG\Models\Reward;
use ROG\Models\SCORING_CITY_TYPE;
use ROG\Models\ScoringCityTile;
use ROG\Models\ScoringTile;
use ROG\Models\Tile;

/* Class to manage all the tiles */

class Tiles extends \ROG\Helpers\Pieces
{
  protected static $table = 'tiles';
  protected static $prefix = 'tile_';
  protected static $autoIncrement = true;
  protected static $autoremovePrefix = false;
  protected static $customFields = [ 'player_id', 'type', 'subtype'];

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
      case TILE_TYPE_CITY_SCORING:
        $data = self::getScoringCityTilesTypes()[$type];
        return new ScoringCityTile($row, $data);
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
      ->merge(self::getInLocationOrdered(TILE_LOCATION_MASTERY_CARD))
      ->merge(self::getInLocation(TILE_LOCATION_MASTERY_RESERVED))
      ->merge(self::getInLocationOrdered(TILE_LOCATION_BUILDING_ROW))
      ->merge(self::getInLocationOrdered(TILE_LOCATION_BUILDING_SHORE))
      ->merge(self::getInLocationOrdered(TILE_LOCATION_CITYSCORING_BOARD))
      ;
    if(isset($nextEra1Card) && !Utils::hideTopEraDeck(1)) $cards->append($nextEra1Card);
    if(isset($nextEra2Card) && !Utils::hideTopEraDeck(2)) $cards->append($nextEra2Card);
    return $cards->ui();
  } 
  
  public static function revealTopEraTiles()
  {
    $nextEra1Card = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_1);
    $nextEra2Card = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_2);
    Notifications::revealTopEraTiles($nextEra1Card,$nextEra2Card);
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
  public static function getTileOnShoreSpace($position) : ?BuildingTile
  {
    return self::getInLocation(TILE_LOCATION_BUILDING_SHORE,$position)->first();
  }
  
  public static function getBuiltTilesIdsNearRiverSpaces(array $riverSpaces) : array
  {
    Game::get()->trace("getBuiltTilesIdsNearRiverSpaces()".json_encode($riverSpaces));
    $spaces = ShoreSpaces::getUniqueSpacesAdjacentToRiver($riverSpaces);
    return self::DB()->select([self::$prefix.'id'])
      ->where(static::$prefix . 'location', TILE_LOCATION_BUILDING_SHORE)
      ->whereIn(static::$prefix . 'state', $spaces)
      ->get()
      ->getIds();
  }
  
  public static function countBuiltTilesNearPlayerShips(int $player_id, int $buildingType) : int
  {
    $boats = Meeples::getBoats($player_id);
    $boatsRiverSpaces = array_unique($boats->map(function(Meeple $b) {return $b->getPosition();})->toArray());
    $tilesIds = Tiles::getBuiltTilesIdsNearRiverSpaces($boatsRiverSpaces);
    $nbTiles = Tiles::getMany($tilesIds)->filter(function(BuildingTile $t) use ($buildingType){
        return ($buildingType == $t->getBuildingType());
      })->count();
      return $nbTiles;
  }
  
  public static function getBuiltTilesIdsInRegion(int $region) : array
  {
    Game::get()->trace("getBuiltTilesIdsInRegion($region)");
    $spaces = ShoreSpaces::getSpacesByRegion($region);
    return self::DB()->select([self::$prefix.'id'])
      ->where(static::$prefix . 'location', TILE_LOCATION_BUILDING_SHORE)
      ->whereIn(static::$prefix . 'state', $spaces)
      ->get()
      ->getIds();
  }
  /**
   * @param int $subType
   * @param array $tilesTypes
   * @return array of int
   */
  public static function getIdsByType($subType,$tilesTypes)
  {
    return self::DB()->select([self::$prefix.'id'])
      ->where( 'subtype', $subType)
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
      ->where( 'subtype', $subType)
      ->whereIn( 'type', $tilesTypes)
      ->get();
  } 
  
  /**
   * @return Collection of ScoringTile
   */
  public static function getScoringTiles() : Collection
  {
    return self::getAllByType(TILE_TYPE_SCORING,array_keys(self::getScoringTilesTypes()));
  } 
  
  /**
   * @return array of ScoringTile, ordered by best first place score DESC
   */
  public static function getOrderedScoringTiles() : array
  {
    $scoringTiles = Tiles::getScoringTiles()->toArray();
    usort($scoringTiles, function (ScoringTile $a,ScoringTile $b)  {
      $scoreA = $a->getFirstPlaceScore();
      $scoreB = $b->getFirstPlaceScore();
      if ($scoreA == $scoreB) return 0;
      return ($scoreA < $scoreB) ? 1 : -1;
    });
    return $scoringTiles;
  } 
  /**
   * @return Collection of MasteryCard
   */
  public static function getMasteryCards(): Collection
  {
    return self::getAllByType(TILE_TYPE_MASTERY_CARD,array_keys(self::getMasteryCardsTypes()));
  } 
  
  public static function countMasteriesInDeck(): int
  {
    return Tiles::countInLocation(TILE_LOCATION_MASTERY_DECK) + Tiles::countInLocation(TILE_LOCATION_MASTERY_CARD) ;
  } 
  /**
   * @return Collection ordered from left or right depending on param $fromLeft
   */
  public static function getMasteryToClaim(bool $fromLeft = true): Collection
  {
    return self::getInLocationOrdered(TILE_LOCATION_MASTERY_CARD,null,$fromLeft);
  } 
  public static function getMasteryReserved(Player $player): Collection
  {
    return self::DB()->wherePlayer($player->getId())
      ->where(self::$prefix.'location', TILE_LOCATION_MASTERY_RESERVED)
      ->get();
  } 
  /**
   * @return Collection of BuildingTile
   */
  public static function getBuildingTiles()
  {
    return self::getAllByType(TILE_TYPE_BUILDING,array_keys(self::getBuildingTilesTypes()));
  } 

  public static function getPlayerBuildingTilesIds(int $player_id) : array
  {
    $markersOnBuildings = Meeples::getPlayerBuildingsMarkers($player_id);
    $tilesIds = array_unique( $markersOnBuildings->map( function($meeple) { 
      return $meeple->getBuildingTileId();
    })->toArray());
    return $tilesIds;
  } 

  public static function getPlayerBuildingTilesShoreSpace(int $player_id) : array
  {
    $tilesIds = Tiles::getPlayerBuildingTilesIds($player_id);
    return self::DB()->select([self::$prefix.'state'])
        ->where(static::$prefix . 'location', TILE_LOCATION_BUILDING_SHORE)
        ->whereIn(static::$prefix . 'id', $tilesIds)
        ->get()
        ->map(function ($tile) {
          return $tile->state;
        })
        ->toArray();
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
  
  public static function discardStartingBuildings()
  {
    $startings = Tiles::getInLocationOrdered(TILE_LOCATION_BUILDING_SHORE);
    $spacesToRemove = [SHORE_SPACE_STARTING_BUILDING_FOR_2, SHORE_SPACE_STARTING_BUILDING_FOR_3];
    $tilesToRemove = $startings->filter(function ($tile) use ($spacesToRemove){
      $shoreSpace = ShoreSpaces::getShoreSpace($tile->getPosition());
      return in_array($shoreSpace->type,$spacesToRemove);
    });
    Notifications::discardTiles($tilesToRemove, clienttranslate('Starting buildings are removed from the game'));
    foreach ($tilesToRemove as $tileId => $tile) {
      $tile->setLocation(TILE_LOCATION_DISCARD);
    }
  } 
  
  public static function getCityBoardTile(int $row, int $col) : ?ScoringCityTile
  {
    return self::DB()
        ->where(static::$prefix . 'location', TILE_LOCATION_CITYSCORING_BOARD)
        ->where(static::$prefix . 'state', $row)
        ->get()
        ->first();
  } 
  //////////////////////////////////////////////////////////////////////
  /** Creation of the tiles */
  public static function setupNewGame($players, $options)
  {
    $tiles = [];

    $nbPlayers = count($players);
    $nbPlayersWithAutoma = $nbPlayers + (Utils::isGameWithAutoma() ? 1 : 0);
    $scoringTiles = self::getScoringTilesTypes();
    foreach ($scoringTiles as $type => $tile) {
      if( in_array($nbPlayersWithAutoma,$tile['nbPlayers'])){
        $tiles[] = [
          'location' => TILE_LOCATION_SCORING,
          'type' => $type,
          'subtype' => TILE_TYPE_SCORING,
        ];
      }
    }
    
    $masteryCards = self::getMasteryCardsTypes();
    foreach ($masteryCards as $type => $tile) {
      if( in_array($nbPlayersWithAutoma,$tile['nbPlayers'])){
        $tiles[] = [
          'location' => TILE_LOCATION_MASTERY_DECK,
          'type' => $type,
          'subtype' => TILE_TYPE_MASTERY_CARD,
        ];
      }
    }
    
    $optionMarketSetup = Globals::getOptionImperialMarkets();
    $imperialMarketTiles = [];
    $buildingTiles = self::getBuildingTilesTypes();
    foreach ($buildingTiles as $type => $tile) {
      $era = $tile['era'];
      $option = $tile['option'];
      if( $era == 0){
        if(BUILDING_TYPE_MARKET == $tile['buildingType']){
          if($option == $optionMarketSetup || $optionMarketSetup == OPTION_MARKETS_RANDOM ){
            $imperialMarketTiles[] = ['type'=>$type, 'tile' => $tile];
          }
          continue;
          //manage 3 random imperial markets after the loop
        }

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

    shuffle($imperialMarketTiles);
    //KEEP ONLY 3 Imperial Markets
    $nbImperialMarkets = 0;
    foreach ($imperialMarketTiles as $tileDatas) {
      $tile = $tileDatas['tile'];
      $tiles[] = [
        'location' => TILE_LOCATION_BUILDING_SHORE,
        'type' => $tileDatas['type'],
        'subtype' => TILE_TYPE_BUILDING,
        'nbr' => $tile['nbr'],
      ];
      $nbImperialMarkets++;
      if($nbImperialMarkets >= NB_IMPERIAL_MARKETS) break;
    }

    if(Utils::isGameWithCityOfLies()){
      $scoringCityTiles = self::getScoringCityTilesTypes();
      foreach ($scoringCityTiles as $type => $tile) {
        $tiles[] = [
          'location' => TILE_LOCATION_CITYSCORING_DECK,
          'type' => $type,
          'subtype' => TILE_TYPE_CITY_SCORING,
        ];
      }
    }

    if(count($tiles)>0){
      self::create($tiles);
      self::shuffle(TILE_LOCATION_SCORING);
      self::shuffle(TILE_LOCATION_MASTERY_DECK);
      self::shuffle(TILE_LOCATION_BUILDING_SHORE);
      self::shuffle(TILE_LOCATION_BUILDING_DECK_ERA_1);
      self::shuffle(TILE_LOCATION_BUILDING_DECK_ERA_2);
      self::shuffle(TILE_LOCATION_CITYSCORING_DECK);

      //Pick 3 mastery cards for the game
      $masteryCards = self::pickForLocation(3,TILE_LOCATION_MASTERY_DECK,TILE_LOCATION_MASTERY_CARD);
      self::shuffle(TILE_LOCATION_MASTERY_CARD);
      
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

      //Keep 18/16 /14/12 era 1 tiles <=> remove 6/8/10/12 tiles
      $nbBuildingToRemove = [1=>12, 2=>12, 3=>10, 4=>8, 5=>6,];
      $buildingTiles = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_1,$nbBuildingToRemove[$nbPlayers],false);
      foreach ($buildingTiles as $tileId => $tile) {
        self::DB()->delete($tileId);
      }

      //Keep 15/13 /11/9 era 2 tiles <=> remove 1/3/5/7 tiles
      $nbBuildingToRemove = [1=>7, 2=>7, 3=>5, 4=>3, 5=>1,];
      $buildingTiles = self::getTopOf(TILE_LOCATION_BUILDING_DECK_ERA_2,$nbBuildingToRemove[$nbPlayers], false);
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
      
      if(Utils::isGameWithCityOfLies()){
        $nbScoringCityTiles = [1=>3, 2=>3, 3=>4, 4=>5, 5=>5,];
        //$scoringCityTiles = self::pickForLocation($nbScoringCityTiles[$nbPlayersWithAutoma],TILE_LOCATION_CITYSCORING_DECK,TILE_LOCATION_CITYSCORING_BOARD);
          //only 1 tile per region : 
          $nbRegionsToPick = $nbScoringCityTiles[$nbPlayersWithAutoma];
          $pickedRegions = array_rand(array_flip(REGIONS), $nbRegionsToPick);
          $randomTypes = [];
          foreach ($pickedRegions as $region) {
            $tilesTypesfromRegion = Tiles::getScoringCityTilesTypesByRegion($region);
            $randomTypes[] = $tilesTypesfromRegion[array_rand($tilesTypesfromRegion)];
          }
          $scoringCityTiles = self::DB()
            ->where( 'subtype', TILE_TYPE_CITY_SCORING)
            ->whereIn('type', $randomTypes)
            ->where(static::$prefix . 'location', TILE_LOCATION_CITYSCORING_DECK)
            ->get();
        $k = 0;
        foreach ($scoringCityTiles as $tileId => $tile) {
          $k++;
          $tile->setLocation(TILE_LOCATION_CITYSCORING_BOARD);
          $tile->setState($k);
        }
      }

    }
  }

  /**
   * Refill each of the 4 spaces of the building row if the deck is not empty
   * @return array  [1=> bool,2=> bool] true when last Era tile is moved to the row,
   * false otherwise
   */
  public static function refillBuildingRow() : array
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
    if(Globals::getTurnMainActionDone() == MAIN_ACTION::BUILD->value) {
      if(Globals::getLastBuiltLocationOrigin() == TILE_LOCATION_BUILDING_DECK_ERA_1 
        && Tiles::countInLocation(TILE_LOCATION_BUILDING_DECK_ERA_1) == 0
      )
      {
        //Shindoshi 3 may build the last tile of the stack
        $lastEra1TileMoved = true;
      }
      else if(Globals::getLastBuiltLocationOrigin() == TILE_LOCATION_BUILDING_DECK_ERA_2 
        && Tiles::countInLocation(TILE_LOCATION_BUILDING_DECK_ERA_2) == 0
      )
      {
        //Shindoshi 3 may build the last tile of the stack
        $lastEra2TileMoved = true;
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
      7 => $f([ [3,4,5, ], [7,3]    ,null ]), 
      8 => $f([ [3,4,5, ], [9,5]    ,null ]), 
      9 => $f([ [3,4,5, ], [8,4]    ,null ]), 
      10 => $f([[3,4,5, ], [12,8,4] ,null ]), 
      11 => $f([[3,4,5, ], [10,6,2] ,null ]), 
      12 => $f([[3,4,5, ], [11,7,3] ,null ]), 
    ];
  }
  
  /**
   * @return array of all the different types of Mastery Cards
   */
  public static function getMasteryCardsTypes() : array
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
      7 => $f([ [3,4,5, ], [7,5,3] , MASTERY_TYPE_AIR     ]), 
      8 => $f([ [3,4,5, ], [7,5,3] , MASTERY_TYPE_COURTS  ]), 
      9 => $f([ [3,4,5, ], [7,5,3] , MASTERY_TYPE_EARTH   ]), 
      10 => $f([[3,4,5, ], [7,5,3] , MASTERY_TYPE_FIRE    ]), 
      11 => $f([[3,4,5, ], [7,5,3] , MASTERY_TYPE_VOID    ]), 
      12 => $f([[3,4,5, ], [7,5,3] , MASTERY_TYPE_WATER   ]), 
      //New in V2
      13 => $f([ [2],   [5]     , MASTERY_TYPE_WAVES       ]), 
      14 => $f([ [2],   [5]     , MASTERY_TYPE_SUN_MOON    ]), 
      15 => $f([ [2],   [5]     , MASTERY_TYPE_LIGHTNING   ]), 
      16 => $f([[3,4,5, ], [7,5,3] , MASTERY_TYPE_WAVES       ]), 
      17 => $f([[3,4,5, ], [7,5,3] , MASTERY_TYPE_SUN_MOON    ]), 
      18 => $f([[3,4,5, ], [7,5,3] , MASTERY_TYPE_LIGHTNING   ]), 
    ];
  }
  
  /**
   * @param int $masteryCardType mastery tile type (2player face or 3player face)
   * @return int $type Mastery tile type which meets the same tile but on 2 player side
   */
  public static function get2PlayerSideMasteryCardType(int $masteryCardType) : int
  {
    $allTypes = Tiles::getMasteryCardsTypes();
    $currentMasteryCard = $allTypes[$masteryCardType];
    $currentMasteryCardScoringType = $currentMasteryCard['scoringType'];
    foreach($allTypes as $type => $typeDatas){
      if(in_array(2,$typeDatas['nbPlayers'])
        && $typeDatas['scoringType'] == $currentMasteryCardScoringType 
      ){
        return $type;
      }
    }
    //Should not happen :
    return $masteryCardType;
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
        'option' => isset($t[6]) ? $t[6] : null,
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
      44 => $f([ 0, 0  , BUILDING_TYPE_MARKET,[], [RESOURCE_TYPE_POTTERY=>1,RESOURCE_TYPE_SUN=>1]  , 1,  OPTION_MARKETS_BASE ]), 
      45 => $f([ 0, 0  , BUILDING_TYPE_MARKET,[], [RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_SUN=>1]     , 1,  OPTION_MARKETS_BASE ]), 
      46 => $f([ 0, 0  , BUILDING_TYPE_MARKET,[], [RESOURCE_TYPE_SILK=>1,BONUS_TYPE_DRAW =>1]      , 1,  OPTION_MARKETS_BASE ]), 
      
      // V2 : +3 starting green 
      47 => $f([ 0, 0  , BUILDING_TYPE_MARKET,[], [BONUS_TYPE_DRAW => 1, RESOURCE_TYPE_SUN=>1,    ]   , 1,  OPTION_MARKETS_NIGHT  ]), 
      48 => $f([ 0, 0  , BUILDING_TYPE_MARKET,[], [BONUS_TYPE_DRAW => 1, BONUS_TYPE_TRADE_KOKU=>1,]   , 1,  OPTION_MARKETS_NIGHT  ]), 
      49 => $f([ 0, 0  , BUILDING_TYPE_MARKET,[], [BONUS_TYPE_DRAW => 1, BONUS_TYPE_TRADE_POINTS=>1,] , 1,  OPTION_MARKETS_NIGHT  ] ), 
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

  public static function getScoringCityTilesTypesByRegion(int $region) : array {
    $types = [];
    $datas = self::getScoringCityTilesTypes();
    foreach ($datas as $type => $data) {
      if($region == $data['region']){
        $types[] = $type;
      }
    }
    return $types;
  }
  
  /**
   * @return array of all the different types of Scoring City Tiles
   */
  public static function getScoringCityTilesTypes() : array
  {
    $f = function ($t) {
      return [
        'region' => $t[0],
        'scoreByElement' => $t[1],
        'scoredElement' => $t[2]->value,
      ];
    };
    return [
      // 12 unique 
      1  => $f([ REGION_1,  2,   SCORING_CITY_TYPE::BUILDING        ]), 
      2  => $f([ REGION_1,  1,   SCORING_CITY_TYPE::TRADE_GOOD      ]), 
      3  => $f([ REGION_2,  3,   SCORING_CITY_TYPE::PORT            ]), 
      4  => $f([ REGION_2,  3,   SCORING_CITY_TYPE::MARKET          ]), 
      5  => $f([ REGION_3,  10,  SCORING_CITY_TYPE::NOTHING         ]), 
      6  => $f([ REGION_3,  4,   SCORING_CITY_TYPE::IMPERIAL_FLOWER ]), 
      7  => $f([ REGION_4,  3,   SCORING_CITY_TYPE::SHRINE          ]), 
      8  => $f([ REGION_4,  3,   SCORING_CITY_TYPE::MANOR           ]), 
      9  => $f([ REGION_5,  3,   SCORING_CITY_TYPE::MONEY           ]), 
      10 => $f([ REGION_5,  3,   SCORING_CITY_TYPE::DELIVERIES      ]), 
      11 => $f([ REGION_6,  3,   SCORING_CITY_TYPE::SUN             ]), 
      12 => $f([ REGION_6,  4,   SCORING_CITY_TYPE::MASTERIES       ]), 
    ];
  }
}
