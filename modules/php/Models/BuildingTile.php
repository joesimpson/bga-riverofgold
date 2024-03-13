<?php

namespace ROG\Models;

/*
 * BuildingTile: all utility functions concerning a Building tile
 */

class BuildingTile extends Tile
{
  
  public Reward $ownerReward;
  public Reward $visitorReward;

  protected $staticAttributes = [
    ['bonus', 'int'],
    ['buildingType', 'int'],
    ['era', 'int'],
    'ownerRewardArray',
    'visitorRewardArray',
  ];

  
  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
    $this->ownerReward = new Reward($datas['ownerRewardArray']);
    $this->visitorReward = new Reward($datas['visitorRewardArray']);
  }
  

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['pos'] = $this->getPosition();
    $data['subtype'] = TILE_TYPE_BUILDING;
    unset($data['ownerReward']);
    unset($data['visitorReward']);
    unset($data['state']);
    return $data;
  }

  public function setPosition($value){
    $this->setState($value);
  }
  public function getPosition(){
    return $this->getState();
  }
}
