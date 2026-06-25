<?php

namespace ROG\Models;

class CitySpace implements \JsonSerializable
{
  
  public int $row;
  public int $column;
  public int $region;
  public Reward $rewards;
  
  public function __construct(int $row, int $column, int $region, array $rewardArray)
  {
    $this->row = $row;
    $this->column = $column;
    $this->region = $region;
    $this->rewards = new Reward($rewardArray);
  }
  
  protected $attributes = [
    'row','column','region',
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
