<?php

namespace ROG\Models;

use ROG\Helpers\Collection;
use ROG\Managers\Meeples;

/*
 * Tile: all utility functions concerning a tile
 */

class Tile extends \ROG\Helpers\DB_Model
{
  protected $table = 'tiles';
  protected $primary = 'tile_id';
  protected $attributes = [
    'id' => ['tile_id', 'int'],
    'state' => ['tile_state', 'int'],
    'location' => 'tile_location',
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
    return $data;
  }
  
  /**
   * @return Collection of Meeple
   */
  public function getMeeples()
  {
    return Meeples::getInLocation(MEEPLE_LOCATION_TILE.$this->getId());
  }

}
