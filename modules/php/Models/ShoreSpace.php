<?php

namespace ROG\Models;

/*
 * ShoreSpace: all utility functions concerning a Shore Space along the River
 */

class ShoreSpace implements \JsonSerializable
{
  /** Id from 1 to 30 */
  public int $id;
  /**
   * BASE, 
   * IMPERIAL_MARKET, 
   * STARTING_BUILDING_FOR_2, 
   * STARTING_BUILDING_FOR_3
   */
  public int $type;
  /** Region from 1 to 6 */
  public int $region;
  //Cost to build on it
  public int $cost;
  
  /** Tile built on it */
  //public ?BuildingTile $tile;
  /** Players who built on it */
  public array $playerIds;

  /** LEft or Right Side of the River */
  public SHORE_SIDE $side;

  protected $attributes = [
    'id','type','region','cost','playerIds',
  ];

   /**
   * @param int $id
   * @param int $type
   * @param int $region
   * @param int $cost
   */
  public function __construct(int $id,  SHORE_SIDE $side, int $region,int $cost,int $type,)
  {
    $this->id = $id;
    $this->side = $side;
    $this->type = $type;
    $this->region = $region;
    $this->cost = $cost;
    //$this->tile = null;
    $this->playerIds = [];
  }
  
  /**
   * Return an array of attributes
   */
  public function jsonSerialize() : array
  {
    $data = [];
    foreach ($this->attributes as $attribute) {
      $data[$attribute] = $this->$attribute;
    }

    return $data;
  }

  public function getUiData()
  {
    $data = $this->jsonSerialize();
    //if(isset($this->tile)){
    //  $data['tile'] = $this->tile->getUiData();
    //}
    return $data;
  }
}
