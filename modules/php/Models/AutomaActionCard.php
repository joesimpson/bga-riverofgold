<?php

namespace ROG\Models;

use ROG\Core\Globals;
use ROG\Core\Notifications;

class AutomaActionCard extends Card
{ 
  
  protected $staticAttributes = [
    ['title', 'string'],
  ];

  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  } 

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['subtype'] = CARD_TYPE_AUTOMA_ACTION;
    return $data;
  }

  public function play(AutomaPlayer $player)
  {
    $action = $this->getType();
    switch($action){
      case AutomaActionType::SAIL_HIGHER->value : 
        break;
      case AutomaActionType::SAIL_LOWER->value : 
        break;
      case AutomaActionType::DELIVER->value : 
        break;
      case AutomaActionType::BUILD->value : 
        break;
      case AutomaActionType::ADVANCE_CITY->value : 
        break;
    }
  }
}
