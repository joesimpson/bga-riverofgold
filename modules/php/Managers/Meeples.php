<?php

namespace ROG\Managers;

use ROG\Core\Notifications;
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
   */
  public static function addClanMarkerOnShoreSpace($tile,$player)
  {
    $meeple = [
      'type' => MEEPLE_TYPE_CLAN_MARKER,
      'location' => MEEPLE_LOCATION_TILE.$tile->id,
      'player_id' => $player->id,
    ];
    $elt = self::singleCreate($meeple);
    Notifications::newClanMarker($player,$elt);
    return $elt;
  }
  
  /**
   * @param Player $player
   * @param int $region
   * @param bool $sendNotif (optional) default true, means send a notif to UI
   */
  public static function addClanMarkerOnInfluence($player,$region,$sendNotif = true)
  {
    $meeple = [
      'type' => MEEPLE_TYPE_CLAN_MARKER,
      'location' => MEEPLE_LOCATION_INFLUENCE.$region,
      'player_id' => $player->id,
      'state' => 0,
    ];
    $elt = self::singleCreate($meeple);
    if($sendNotif) Notifications::newClanMarker($player,$elt);
    return $elt;
  }
  
  /**
   * @param int $pId
   * @param int $type of building to search for
   * @return int number 
   */
  public static function countPlayerBuildings($pId, $type)
  {
    return self::getFilteredQuery($pId, MEEPLE_LOCATION_TILE.'%', $type)->count();
  }

  
}
