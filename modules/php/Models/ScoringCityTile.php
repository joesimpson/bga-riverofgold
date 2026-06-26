<?php

namespace ROG\Models;

class ScoringCityTile extends Tile
{
  
  protected $staticAttributes = [
    ['region', 'int'],
    ['scoreByElement', 'int'],
    ['scoredElement', 'int'],
  ];

  
  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  }

  public function getUiData() : array
  {
    $data = parent::getUiData();
    $data['subtype'] = TILE_TYPE_CITY_SCORING;
    unset($data['pId']);
    $data['pos'] = $this->getState();
    unset($data['state']);
    return $data;
  }
  
  public function computeScore( )
  {
    return 0;
  }
}
