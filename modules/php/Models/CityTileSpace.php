<?php

namespace ROG\Models;

use ROG\Managers\Tiles;

class CityTileSpace extends CitySpace
{
  
  public function __construct(int $id, int $row, )
  {
    parent::__construct( $id, $row, CITY_COLUMN_TILES, 0,[]);
  }

  public function getTile() : ?ScoringCityTile
  {
    return Tiles::getCityBoardTile($this->row,$this->column);
  }

}
