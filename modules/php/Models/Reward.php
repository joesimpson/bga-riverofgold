<?php

namespace ROG\Models;

/*
 * Reward: all utility functions concerning a reward of some resources
 */

class Reward implements \JsonSerializable
{
  /** Array of resources and count */
  public array $entries;

   /**
   * @param array $entries
   */
  public function __construct($entries)
  { 
    $this->entries = [];
    foreach($entries as $type => $number){
      $this->entries[] = new RewardEntry($type,$number);
    }
  }
  
  /**
   * Return an array of attributes
   */
  public function jsonSerialize()
  {
    $data = [];
    return $data;
  }

  public function getUiData()
  {
    $data = $this->jsonSerialize(); 
    $data['entries'] = [];
    foreach($this->entries as $reward){
      $data['entries'][] = $reward->getUiData();
    }
    return $data;
  }
}
