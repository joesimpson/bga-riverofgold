<?php

namespace ROG\Models;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Helpers\Utils;
use ROG\Managers\Players;

/**
 * ScenarioCard: all utility functions concerning a Scenario Card
 */

class ScenarioCard extends Card
{ 
  
  protected $staticAttributes = [
    ['clan', 'int'],
    ['name', 'string'],
  ];

  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  } 

  public function getUiData()
  {
    $data = parent::getUiData();
    unset($data['state']);
    $data['subtype'] = CARD_TYPE_SCENARIO;
    return $data;
  }

  /**
   * @return string
   */
  public function getClanName() : string
  {
    return Utils::getClanName($this->getClan());
  } 

}
