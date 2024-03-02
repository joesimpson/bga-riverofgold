<?php

namespace ROG\Models;

/*
 * MasteryCard: all utility functions concerning a Mastery Card
 */

class MasteryCard extends Tile
{
  
  protected $staticAttributes = [
    ['scoringType', 'int'],
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
    unset($data['pId']);
    unset($data['state']);
    unset($data['nbPlayers']);
    unset($data['scores']);
    return $data;
  }
}
