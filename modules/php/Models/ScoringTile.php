<?php

namespace ROG\Models;

/*
 * ScoringTile: all utility functions concerning a scoring tile
 */

class ScoringTile extends Tile
{
  
  protected $staticAttributes = [
    ['nbPlayers', 'obj'],
    ['scores', 'obj'],
  ];

  
  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  }
  

  public function getUiData()
  {
    $data = parent::getUiData();
    //state will be in [0,1,2,3,4,5] after shuffle :
    $data['pos'] = $this->getState() +1 ;
    $data['subtype'] = TILE_TYPE_SCORING;
    unset($data['pId']);
    unset($data['state']);
    unset($data['nbPlayers']);
    unset($data['scores']);
    return $data;
  }
}
