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

}
