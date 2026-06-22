<?php

namespace ROG\Models;

use ROG\Managers\Meeples;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;

/*
 * Meeple: all utility functions concerning a Meeple 
 */

class Meeple extends \ROG\Helpers\DB_Model
{
  protected $table = 'meeples';
  protected $primary = 'meeple_id';
  protected $attributes = [
    'id' => ['meeple_id', 'int'],
    'state' => ['meeple_state', 'int'],
    'location' => 'meeple_location',
    'pId' => ['player_id', 'int'],
    'type' => ['type', 'int'],
  ];
  
  protected $staticAttributes = [
  ];

  public function __construct($row, $datas)
  {
    parent::__construct($row);
    foreach ($datas as $attribute => $value) {
      $this->$attribute = $value;
    }
  }

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['pos'] = $this->getPosition();
    unset($data['state']);
    return $data;
  }
 
  public function setPosition($value){
    $this->setState($value);
  }
  public function getPosition(){
    return $this->getState();
  }
  
  public function getBuildingTileId(): int|null{
    $location = $this->getLocation();
    if (preg_match("/^" . MEEPLE_LOCATION_TILE . "(?P<tile>\d+)$/", $location, $matches) == 1) {
      return $matches['tile'];
    }
    return null;
  }
  /**
   * @return int region where this meeple is (when on building tile),
   * null otherwise
   */
  public function getBuildingRegion(): int|null{
    $tileId = $this->getBuildingTileId();
    if (isset($tileId)) {
      $tile = Tiles::get($tileId);
      if($tile instanceof BuildingTile){
        return $tile->getRegion();
      }
    }
    return null;
  }
  
  /**
   * @return int $cardId if this meeple is on a card, null otherwise
   */
  public function getCardId(): int|null{
    $location = $this->getLocation();
    if (preg_match("/^" . MEEPLE_LOCATION_CARD . "(?P<card>\d+)$/", $location, $matches) == 1) {
      return $matches['card'];
    }
    return null;
  }

  public function isResource(): bool
  {
    $resType = Meeples::getResourceFromType($this->getType());
    return $resType > 0;
  }
  
  public function getResourceRegion(): int
  {
    $region = 0;
    $location = $this->getLocation();
    if (preg_match("/^" . MEEPLE_LOCATION_SHORE . "(?P<space>\d+)$/", $location, $matches) == 1) {
      $space = $matches['space'];
      $region = ShoreSpaces::getShoreSpace($space)->region;
    }
    return $region;
  }
}
