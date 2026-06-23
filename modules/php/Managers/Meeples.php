<?php

namespace ROG\Managers;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Models\BuildingTile;
use ROG\Models\Card;
use ROG\Models\ClanPatronCard;
use ROG\Models\MasteryCard;
use ROG\Models\Meeple;
use ROG\Models\Player;
use ROG\Models\ScenarioCard;
use ROG\Models\ScenarioType;
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
  
  public static function removePlayerMarkersOnShoreSpace(int $shoreSpace,Player $player, BuildingTile $tile,ClanPatronCard $playerPatron) : void
  {
    Game::get()->trace("removePlayerMarkersOnShoreSpace( $shoreSpace)...");
    $meeples = Meeples::getInLocation(MEEPLE_LOCATION_SHORE.$shoreSpace);
    foreach($meeples as $meeple){
      $meepleOwner = $meeple->getPId();
      if(isset($meepleOwner) && ($meepleOwner == $player->getId())){
        Notifications::removeClanMarker($player,$meeple);
        self::DB()->delete($meeple->getId());
        if(PATRON_LIONS_LADY == $playerPatron->getType()){
          Globals::addBonusWithDatas($player,BONUS_TYPE_BUILDING_REWARD,['tile'=>$tile->getId(),'position'=>$shoreSpace,'bonusQuantity'=>1,],clienttranslate('Building reward'));
          Globals::addBonus($player,BONUS_TYPE_PLACE_LION);
        }
      }
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
   * @param int $pId : player id
   * @return int count of unique mastery tiles where this player has a clan marker
   */
  public static function countPlayerMasteries(int $pId) : int
  {
    Game::get()->trace("countPlayerMasteries($pId)...");
    $tilesTypes = array_keys(Tiles::getMasteryCardsTypes());
    $tileIds = Tiles::getIdsByType(TILE_TYPE_MASTERY_CARD,$tilesTypes);
    $tiles = [];
    foreach($tileIds as $tileId){
      $tiles[] = MEEPLE_LOCATION_TILE.$tileId;
    }
    return self::DB()->wherePlayer($pId)
      ->where('type', MEEPLE_TYPE_CLAN_MARKER)
      ->whereIn(self::$prefix.'location', $tiles)
      ->countDistinct(self::$prefix.'location');
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
  public static function addBoatOnRiverSpace(Player $player,int $position,bool $sendNotif = true) : Meeple
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
  
  public static function addRoyalShipOnRiverSpace(Player $player,int $position,bool $sendNotif = true) : Meeple
  {
    $meeple = Meeples::addBoatOnRiverSpace($player,$position,false);
    $meeple->setType(MEEPLE_TYPE_SHIP_ROYAL);
    if($sendNotif) Notifications::newBoat($player,$meeple);
    return $meeple;
  }
  
  public static function addBoatOnCard(Player $player,Card $card, ?string $message = null) : Meeple
  {
    $meeple = [
      'type' => MEEPLE_TYPE_SHIP,
      'location' => MEEPLE_LOCATION_CARD.$card->getId(),
      'player_id' => $player->getId(),
      'state' => 1,
    ];
    $elt = self::singleCreate($meeple);
    if(isset($message)) Notifications::newBoat($player,$elt, $message);
    return $elt;
  }

  /**
   * @param int $pId
   * @return Collection of Meeple
   */
  public static function getBoats($pId)
  {
    return self::getFilteredQuery($pId, MEEPLE_LOCATION_RIVER,null)->get();
  }
  
  public static function getRogueShip() : ?Meeple
  {
    return self::getFilteredQuery(ROGUE_PLAYER_ID, MEEPLE_LOCATION_RIVER,null)->get()->first();
  }

  public static function getBoatOnCard(Card $card) : ?Meeple
  {
    $cardId = $card->getId();
    return self::DB()
      ->whereIn('type', [MEEPLE_TYPE_SHIP, MEEPLE_TYPE_SHIP_ROYAL])
      ->where(self::$prefix.'location', MEEPLE_LOCATION_CARD.$cardId)
      ->get()
      ->first();
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
   * @param array $playersIds : list of player ids to select
   * @return Collection of Meeple
   */
  public static function getAllPlayersInfluenceMarkers(int $region, array $playersIds)
  {
    return self::DB()
      ->whereIn('player_id', $playersIds)
      ->whereIn('type', [MEEPLE_TYPE_CLAN_MARKER])
      ->where(self::$prefix.'location', MEEPLE_LOCATION_INFLUENCE.$region)
      ->get();
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
  
  public static function getAssassinsOnShoreSpace(int $pId, int $shoreSpace) : Collection
  {
    return self::getFilteredQuery($pId, MEEPLE_LOCATION_NEAR_SHORE.$shoreSpace,)->get();
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

  public static function countEnemies(int $pId) : int
  {
    return self::DB()->wherePlayer($pId)
      ->count();
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
      ->where('type', MEEPLE_TYPE_CLAN_MARKER)
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
      ->where('type', MEEPLE_TYPE_CLAN_MARKER)
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
      ->whereNotIn('player_id',[$pid, SCORPION_ENEMY_ID])
      ->where(self::$prefix.'location', MEEPLE_LOCATION_INFLUENCE.$region)
      ->whereIn(self::$prefix.'state', $watchedPositions)
      ->countDistinct(self::$prefix.'state');
  }

  public static function placeLionOnShoreSpace(Player $player, int $shore_space) : Meeple
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
  
  public static function placeAssassinNearShoreSpace(Player $player, int $shore_space, ScenarioCard $card,) : Meeple
  {
    $meeple = [
      'type' => MEEPLE_TYPE_CLAN_MARKER,
      'location' => MEEPLE_LOCATION_NEAR_SHORE.$shore_space,
      'player_id' => $player->getId(),
      'state' => 1,
    ];
    $elt = self::singleCreate($meeple);
    Notifications::newAssassin($player,$elt,$card,);
    return $elt;
  }
  
  public static function getTypeFromResource(int $resourceType) : int
  {
    $meepleType = 0;
    switch($resourceType){
      case RESOURCE_TYPE_SILK:    $meepleType = MEEPLE_TYPE_RESOURCE_SILK;    break;
      case RESOURCE_TYPE_POTTERY: $meepleType = MEEPLE_TYPE_RESOURCE_POTTERY; break;
      case RESOURCE_TYPE_RICE:    $meepleType = MEEPLE_TYPE_RESOURCE_RICE;    break;
    }
    return $meepleType;
  }
  public static function getResourceFromType(int $meepleType) : int
  {
    $resourceType = 0;
    switch($meepleType){
      case MEEPLE_TYPE_RESOURCE_SILK   : $resourceType = RESOURCE_TYPE_SILK   ; break;
      case MEEPLE_TYPE_RESOURCE_POTTERY: $resourceType = RESOURCE_TYPE_POTTERY; break;
      case MEEPLE_TYPE_RESOURCE_RICE   : $resourceType = RESOURCE_TYPE_RICE   ; break;
    }
    return $resourceType;
  }
  public static function placeResourceOnShoreSpace(Player $player, int $shore_space, int $resourceType) : Meeple
  {
    $meeple = [
      'type' => Meeples::getTypeFromResource($resourceType),
      'location' => MEEPLE_LOCATION_SHORE.$shore_space,
      'state' => 1,
    ];
    $elt = self::singleCreate($meeple);
    //Don't notif now, but send 1 notif for all
    return $elt;
  }

  public static function getResourcesOnShoreSpaces() : Collection
  {
    $resTypes = [ MEEPLE_TYPE_RESOURCE_SILK,MEEPLE_TYPE_RESOURCE_POTTERY,MEEPLE_TYPE_RESOURCE_RICE];
    return self::DB()
      ->whereIn('type', $resTypes)
      ->where(self::$prefix.'location','LIKE', MEEPLE_LOCATION_SHORE."%")
      ->get();
  }
  
  public static function getResourcesOnShoreSpace(ShoreSpace $space) : Collection
  {
    $resTypes = [ MEEPLE_TYPE_RESOURCE_SILK,MEEPLE_TYPE_RESOURCE_POTTERY,MEEPLE_TYPE_RESOURCE_RICE];
    return self::DB()
      ->whereIn('type', $resTypes)
      ->where(self::$prefix.'location', MEEPLE_LOCATION_SHORE.$space->id)
      ->get();
  }
  
  /**
   * @param bool $pay : do we need to pay a good for this
   * @return bool true if resources are removed
   */
  public static function removeResourcesOnShoreSpace(ShoreSpace $shoreSpace,Player &$player, bool $pay = true ) : bool
  {
    Game::get()->trace("removeResourcesOnShoreSpace( $shoreSpace->id)...");
    $meeples = Meeples::getResourcesOnShoreSpace($shoreSpace);
    if(count($meeples) == 0) return false;
    //$scenarioResources = Cards::getAssignedScenario(ScenarioType::UNICORN_1);
    //$scenarioOwner = $scenarioResources->getPId();
    foreach($meeples as $meeple){
      Meeples::removeResourceOnShoreSpace($player,$meeple);
      if(!$pay) continue;
      $typeResource = Meeples::getResourceFromType($meeple->getType());
      if($player->canSpendResource($typeResource,1)){
        $player->giveResource(-1,$typeResource);
      }
    }
    return true;
  }
  
  public static function removeResourceOnShoreSpace(Player &$player, Meeple $meeple, )
  {
    Notifications::removeResourceMarker($player,$meeple,);
    Meeples::DB()->delete($meeple->getId());
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
  
  public static function removeShip(Player $player,Meeple $meeple, string $message = '') : void
  {
    Notifications::removeShip($player,$meeple, $message);
    self::DB()->delete($meeple->getId());
  }
  
  public static function removeTarget(Player $player,Meeple $meeple, ScenarioCard $card) : void
  {
    Notifications::removeTarget($player,$meeple,$card);
    self::DB()->delete($meeple->getId());
  }
  
  public static function removeAssassin(Player $player,Meeple $meeple, ScenarioCard $card) : void
  {
    Notifications::removeAssassin($player,$meeple,$card);
    self::DB()->delete($meeple->getId());
  }

  public static function removeInfluenceClanMarker(Player $player,int $region) : void
  {
    $meeple = Meeples::getInfluenceMarker($player->getId(),$region);
    Notifications::removeInfluenceClanMarker($player,$meeple,$region);
    self::DB()->delete($meeple->getId());
  }
}
