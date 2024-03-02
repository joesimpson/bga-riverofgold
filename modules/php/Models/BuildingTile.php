<?php

namespace ROG\Models;

/*
 * BuildingTile: all utility functions concerning a Building tile
 */

class BuildingTile extends Tile
{
  
  protected $staticAttributes = [
    ['bonus', 'int'],
    ['buildingType', 'int'],
    ['era', 'int'],
    ['ownerReward', 'obj'],
    ['visitorReward', 'obj'],
  ];

  
  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  }
  

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['pos'] = $data['state'];
    unset($data['ownerReward']);
    unset($data['visitorReward']);
    unset($data['state']);
    return $data;
  }
}
