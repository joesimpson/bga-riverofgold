<?php

namespace ROG\Models;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Helpers\Utils;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;

/**
 * ScenarioCard: all utility functions concerning a Scenario Card
 */

class ScenarioCard extends Card
{ 
  
  protected $staticAttributes = [
    ['clan', 'int'],
    ['name', 'string'],
    ['difficulty', 'int'],
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

  public function setupChangesBeforePlayerSetup()
  {
    switch($this->getType()){//ScenarioType value
      case ScenarioType::CRAB_1->value:
        Tiles::discardStartingBuildings();
        break;
    }
  }
  
  public function setupChangesAfterPlayerSetup(Player $player, Collection $players)
  {
    switch($this->getType()){
      case ScenarioType::MANTIS_1->value:
        //Place a royal ship of an unused player color on the bottom river space.
        $clanToPick = Utils::pickNextAvailableClan($players);
        Globals::setRogueClan($clanToPick['clan']);
        $roguePlayer = Players::roguePlayer();
        Notifications::roguePlayer($roguePlayer);
        $boatPosition = ShoreSpaces::getLastRiverSpace();
        $boat = Meeples::addRoyalShipOnRiverSpace($roguePlayer, $boatPosition,false);
        Notifications::newBoat($player,$boat);
        break;
    }
  }
  
  /**
   * @return bool true if scenario is completed
   */
  public function checkEndConditions() : bool
  {
    $checked = false;

    switch($this->getType()){
      case ScenarioType::CRAB_1->value:
        $nbPlayerBuildings = Meeples::countPlayerBuildings($this->getPId());
        $nbSeishinBuildings = Meeples::countPlayerBuildings(AUTOMA_PLAYER_ID);
        $checked = $nbSeishinBuildings < $nbPlayerBuildings;
        break;
    }
    return $checked;
  }

}
