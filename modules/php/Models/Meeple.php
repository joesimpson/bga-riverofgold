<?php

namespace ROG\Models;

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
  
  /**
   * @return int region where this meeple is (when on building tile),
   * null otherwise
   */
  public function getBuildingRegion(){
    $location = $this->getLocation();
    if (preg_match("/^" . MEEPLE_LOCATION_TILE . "(?P<tile>\d+)$/", $location, $matches) == 1) {
      $tileId = $matches['tile'];
      $tile = Tiles::get($tileId);
      if($tile instanceof BuildingTile){
        return $tile->getRegion();
      }
    }
    return null;
  }
}
