<?php

namespace ROG\Models;

class CitySpace implements \JsonSerializable
{
  
  public int $id;

  public int $row;
  public int $column;
  public int $region;
  public Reward $rewards;
  
  public function __construct(int $id, int $row, int $column, int $region, array $rewardArray)
  {
    $this->id = $id;
    $this->row = $row;
    $this->column = $column;
    $this->region = $region;
    $this->rewards = new Reward($rewardArray);
  }
  
  protected $attributes = [
    'id', 'row','column','region',
  ];
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

  public function getUiData() : array
  {
    $data = $this->jsonSerialize();
    $data['rewards'] = $this->rewards->getUiData();
    return $data;
  }
}
