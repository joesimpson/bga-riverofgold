<?php

namespace ROG\Managers;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Models\Card;
use ROG\Models\ClanPatronCard;
use ROG\Models\MasteryCard;
use ROG\Models\Meeple;
use ROG\Models\Player;
use ROG\Models\ShoreSpace;

/* Class to manage all the meeples (clan markers/ships) */

class Meeples extends \ROG\Helpers\Pieces
{
  protected static $table = 'meeples';
  protected static $prefix = 'meeple_';
  protected static $autoIncrement = true;
  protected static $autoremovePrefix = false;
  protected static $customFields = ['type', 'player_id'];

  protected static function cast($row)
  {
    $data = []; 
    return new \ROG\Models\Meeple($row, $data);
  }

  public static function getUiData()
  {
    return self::DB()
      ->get()
      ->map(function ($meeple) {
        return $meeple->getUiData();
      })
      ->toArray();
  }

  /**
   * @param BuildingTile $tile
   * @param Player $player
   * @param int $position (Optional) default 1
   * @param boolean $increaseBuildingsCounter (Optional) default true
   * @return Meeple
   */
  public static function addClanMarkerOnShoreSpace($tile,$player,$position = 1, $increaseBuildingsCounter = true)
  {
    $meeple = [
      'type' => MEEPLE_TYPE_CLAN_MARKER,
      'location' => MEEPLE_LOCATION_TILE.$tile->id,
      'player_id' => $player->getId(),
      'state' => $position,
    ];
    $elt = self::singleCreate($meeple);
    Notifications::newClanMarker($player,$elt,$tile, $increaseBuildingsCounter ? 1 : 0 );
    return $elt;
  }
  
  public static function removeClanMarkerOnShoreSpace(int $shoreSpace,Player $player,ClanPatronCard $playerPatron) : void
  {
    $meeple = Meeples::getInLocation(MEEPLE_LOCATION_SHORE.$shoreSpace)->first();
    if(!isset($meeple)) return;
    Notifications::removeClanMarker($player,$meeple);
    self::DB()->delete($meeple->getId());
    if(PATRON_LIONS_LADY == $playerPatron->getType()){
      Globals::addBonus($player,BONUS_TYPE_BUILDING_REWARD,'',false);
      Globals::addBonus($player,BONUS_TYPE_PLACE_LION,'',false);
    }
  }
  
  /**
   * @param MasteryCard $tile
   * @param Player $player
   * @param int $position
   * @return Meeple
   */
  public static function addClanMarkerOnMasteryCard($tile,$player,$position)
  {
    $meeple = [
      'type' => MEEPLE_TYPE_CLAN_MARKER,
      'location' => MEEPLE_LOCATION_TILE.$tile->getId(),
      'player_id' => $player->getId(),
      'state' => $position,
    ];
    $elt = self::singleCreate($meeple);
    Notifications::newClanMarker($player,$elt);
    return $elt;
  }
  /**
   * @param Player $player
   * @param int $region
   * @param bool $sendNotif (optional) default true, means send a notif to UI
   * @return Meeple
   */
  public static function addClanMarkerOnInfluence($player,$region,$sendNotif = true)
  {
    $meeple = [
      'type' => MEEPLE_TYPE_CLAN_MARKER,
      'location' => MEEPLE_LOCATION_INFLUENCE.$region,
      'player_id' => $player->getId(),
      'state' => 0,
    ];
    $elt = self::singleCreate($meeple);
    if($sendNotif) Notifications::newClanMarker($player,$elt);
    return $elt;
  }
  
  /**
   * @param Player $player
   * @param int $region
   * @return Meeple
   */
  public static function addClanMarkerOnArtisanSpace($player,$region)
  {
    $meeple = [
      'type' => MEEPLE_TYPE_CLAN_MARKER,
      'location' => MEEPLE_LOCATION_ARTISAN.$region,
      'player_id' => $player->getId(),
      'state' => 0,
    ];
    $elt = self::singleCreate($meeple);
    Notifications::newClanMarker($player,$elt);
    return $elt;
  }
  
  /**
   * @param Player $player
   * @param int $region
   * @return Meeple
   */
  public static function addClanMarkerOnElderSpace($player,$region)
  {
    $meeple = [
      'type' => MEEPLE_TYPE_CLAN_MARKER,
      'location' => MEEPLE_LOCATION_ELDER.$region,
      'player_id' => $player->getId(),
      'state' => 0,
    ];
    $elt = self::singleCreate($meeple);
    Notifications::newClanMarker($player,$elt);
    return $elt;
  }
  /**
   * Add a marker if not already placed
   * @param Player $player
   * @return Meeple
   */
  public static function addClanMarkerOnMerchantSpace($player)
  {
    //Add clan marker on merchant space (only the first time)
    $meeple = self::getMarkerOnMerchantSpace($player->getId());
    if( isset($meeple)) return;
    $meeple = [
      'type' => MEEPLE_TYPE_CLAN_MARKER,
      'location' => MEEPLE_LOCATION_MERCHANT,
      'player_id' => $player->getId(),
      'state' => 0,
    ];
    $elt = self::singleCreate($meeple);
    Notifications::newClanMarker($player,$elt);
    return $elt;
  }
  
  public static function addClanMarkerOnCard(Player $player, Card $card)
  {
    $meeple = [
      'type' => MEEPLE_TYPE_CLAN_MARKER,
      'location' => MEEPLE_LOCATION_CARD.$card->getId(),
      'player_id' => $player->getId(),
      'state' => 1,
    ];
    $elt = self::singleCreate($meeple);
    Notifications::newClanMarker($player,$elt);
    return $elt;
  }
  
  /**
   * @param Player $player
   * @return Meeple
   * @param bool $sendNotif (optional) default true, means send a notif to UI
   */
  public static function addBoatOnRiverSpace($player,$position,$sendNotif = true) : Meeple
  {
    $meeple = [
      'type' => MEEPLE_TYPE_SHIP,
      'location' => MEEPLE_LOCATION_RIVER,
      'player_id' => $player->getId(),
      'state' => $position,
    ];
    $elt = self::singleCreate($meeple);
    if($sendNotif) Notifications::newBoat($player,$elt);
    return $elt;
  }
  
  public static function addRoyalShipOnRiverSpace($player,$position,$sendNotif = true) : Meeple
  {
    $meeple = Meeples::addBoatOnRiverSpace($player,$position,false);
    $meeple->setType(MEEPLE_TYPE_SHIP_ROYAL);
    if($sendNotif) Notifications::newBoat($player,$meeple);
    return $meeple;
  }

  /**
   * @param int $pId
   * @return Collection of Meeple
   */
  public static function getBoats($pId)
  {
    return self::getFilteredQuery($pId, MEEPLE_LOCATION_RIVER,null)->get();
  }
  /**
   * @param int $pId
   * @param int $region
   * @return Meeple
   */
  public static function getInfluenceMarker($pId, $region)
  {
    return self::getFilteredQuery($pId, MEEPLE_LOCATION_INFLUENCE.$region,null)->get()->first();
  }
  
  /**
   * @param int $region
   * @return Collection of Meeple
   */
  public static function getAllInfluenceMarkers($region)
  {
    return self::getFilteredQuery(null, MEEPLE_LOCATION_INFLUENCE.$region,null)->get();
  }
  
  /**
   * @param int $pId
   * @return Meeple
   */
  public static function getMarkerOnArtisanSpace($pId, $region)
  {
    return self::getFilteredQuery($pId, MEEPLE_LOCATION_ARTISAN.$region,)->get()->first();
  }
  /**
   * @param int $pId
   * @return Meeple
   */
  public static function getMarkerOnElderSpace($pId, $region)
  {
    return self::getFilteredQuery($pId, MEEPLE_LOCATION_ELDER.$region,)->get()->first();
  }
  /**
   * @param int $pId
   * @return Meeple
   */
  public static function getMarkerOnMerchantSpace($pId)
  {
    return self::getFilteredQuery($pId, MEEPLE_LOCATION_MERCHANT)->get()->first();
  }
  
  public static function countPlayerShipsInLocation(?int $pId, int $position) : int
  {
    Game::get()->trace("countPlayerShipsInLocation($pId, $position)...");
    return self::DB()->wherePlayer($pId)
      ->where(self::$prefix.'location', MEEPLE_LOCATION_RIVER)
      ->where(self::$prefix.'state', $position)
      ->count();
  }
  
  public static function countOpponentShipsInLocation(int $pId, int $position) : int
  {
    Game::get()->trace("countOpponentShipsInLocation($pId, $position)...");
    $nbShipsInSpace = Meeples::countPlayerShipsInLocation(null,$position);
    $nbPlayerShips = Meeples::countPlayerShipsInLocation($pId,$position);
    return $nbShipsInSpace - $nbPlayerShips;
  }

  /**
   * @param int $pId
   * @param int $type of building to search for
   * @return int number of DISTINCT buildings of that type (even if we have 2 meeples on the same)
   */
  public static function countPlayerBuildings(int $pId, ?int $type = null) : int
  {
    Game::get()->trace("countPlayerBuildings($pId, $type)...");
    if(isset($type)) $tilesTypes = Tiles::getTilesTypesByBuilding($type);
    else $tilesTypes = array_keys(Tiles::getBuildingTilesTypes());
    $tileIds = Tiles::getIdsByType(TILE_TYPE_BUILDING,$tilesTypes);
    $buildingTiles = [];
    foreach($tileIds as $tileId){
      $buildingTiles[] = MEEPLE_LOCATION_TILE.$tileId;
    }
    return self::DB()->wherePlayer($pId)
      ->whereIn(self::$prefix.'location', $buildingTiles)
      //->count();
      ->countDistinct(self::$prefix.'location');
  }
  
  /**
   * @param int $pId
   * @param int $region of building to search for
   * @return int number of DISTINCT buildings (even if we have 2 meeples on the same)
   */
  public static function countPlayerBuildingsInRegion(int $pId, int $region) : int
  {
    Game::get()->trace("countPlayerBuildingsInRegion($pId, $region)...");
    
    $tileIds = Tiles::getBuiltTilesIdsInRegion($region);
    $buildingTiles = [];
    foreach($tileIds as $tileId){
      $buildingTiles[] = MEEPLE_LOCATION_TILE.$tileId;
    }
    return self::DB()->wherePlayer($pId)
      ->whereIn(self::$prefix.'location', $buildingTiles)
      ->countDistinct(self::$prefix.'location');
  }

  /**
   * @param int $pId
   * @return Collection
   */
  public static function getPlayerBuildingsMarkers($pId)
  {
    //WARNING tile subTYPE is important
    //return self::getFilteredQuery($pId, MEEPLE_LOCATION_TILE.'%')->get();
    $tilesTypes = [];
    foreach(BUILDING_TYPES as $bType){
      $tileType = $bType;
      $tilesTypes = array_merge($tilesTypes, Tiles::getTilesTypesByBuilding($tileType));
    }
    $tileIds = Tiles::getIdsByType(TILE_TYPE_BUILDING,$tilesTypes);
    $buildingTiles = [];
    foreach($tileIds as $tileId){
      $buildingTiles[] = MEEPLE_LOCATION_TILE.$tileId;
    }
    return self::DB()->wherePlayer($pId)
      ->whereIn(self::$prefix.'location', $buildingTiles)
      ->get();
  }

    /**
   * @param int $pid player to exclude
   * @param int $region
   * @param int $fromInfluence (EXCLUDED)
   * @param int $plusInfluence quantity
   * @return int number Distinct used spaces on track
   */
  public static function countUsedSpacedOnInfluenceTrack($pid,$region,$fromInfluence,$plusInfluence)
  {
    Game::get()->trace("countUsedSpacedOnInfluenceTrack($pid,$region,$fromInfluence,$plusInfluence)");
    $watchedPositions = range($fromInfluence +1,$fromInfluence + $plusInfluence);
    return self::DB()
      ->whereNotIn('player_id',[$pid])
      ->where(self::$prefix.'location', MEEPLE_LOCATION_INFLUENCE.$region)
      ->whereIn(self::$prefix.'state', $watchedPositions)
      ->countDistinct(self::$prefix.'state');
  }

  public static function placeLionOnShoreSpace(Player $player, int $shore_space)
  {
    $meeple = [
      'type' => MEEPLE_TYPE_LION_MARKER,
      'location' => MEEPLE_LOCATION_SHORE.$shore_space,
      'player_id' => $player->getId(),
      'state' => 1,
    ];
    $elt = self::singleCreate($meeple);
    Notifications::newLionMarker($player,$elt);
    return $elt;
  }
  
  public static function getPlayerShipsInRiverSpace(int $position, ) : Collection
  {
    Game::get()->trace("getPlayerShipsInRiverSpace( $position)...");
    return self::DB()
      ->where(self::$prefix.'location', MEEPLE_LOCATION_RIVER)
      ->where(self::$prefix.'state', $position)
      ->get();
  }
  /**
   * @return array of Player ids
   */
  public static function getOpponentIdsInRiverSpace(int $position,int $pId, ) : array
  {
    $ships = Meeples::getPlayerShipsInRiverSpace($position);
    return $ships->filter(function($meeple) use ($pId){
        return $meeple->getPId() != $pId;
      })->map(function($meeple){
        return $meeple->getPId();
      })->toArray(); 
  }

  /**
   * @return Collection of Meeple placed on player cards filtered by Subtype and types
   */
  public static function getPlayerCardsMarkers(int $pId, int $cardSubType,array $cardTypes): Collection
  {
    $cardsIds = Cards::getIdsByTypes($cardSubType,$cardTypes);
    $cardLocations = [];
    foreach($cardsIds as $cardId){
      $cardLocations[] = MEEPLE_LOCATION_CARD.$cardId;
    }
    return self::DB()->wherePlayer($pId)
      ->whereIn(self::$prefix.'location', $cardLocations)
      ->get();
  }
  
  public static function removeClanMarkerById(Player $player,int $id) : void
  {
    $meeple = Meeples::get($id);
    if(!isset($meeple)) return;
    Notifications::removeClanMarker($player,$meeple);
    self::DB()->delete($meeple->getId());
  }
}
