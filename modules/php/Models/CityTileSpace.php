<?php

namespace ROG\Models;

class CityTileSpace extends CitySpace
{
  
  public function __construct(int $id, int $row, )
  {
    parent::__construct( $id, $row, CITY_COLUMN_TILES, 0,[]);
  }
}
