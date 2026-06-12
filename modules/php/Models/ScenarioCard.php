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
    $data['resources'] = $this->getResources();
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
  
  /**
   * @return bool true if boats are setup by this scenario card
   */
  public function setupCustomBoats(Player &$player,) : bool
  {
    switch($this->getType()){//ScenarioType value
      case ScenarioType::CRANE_1->value:
        //start with 1 ship on the top river starting space
        Meeples::addBoatOnRiverSpace($player,1);
        //Place your second ship and a debt pile of 30 on this scenario card. 
        Players::spendMoney($player,$player->getMoney());
        $ship = Meeples::addBoatOnCard($player,$this,null);
        Notifications::newBoatOnScenarioCard($player,$ship, $this);
        $this->addResource($player, CRANE_DEBT_SIZE, RESOURCE_TYPE_MONEY);
        return true;
    }
    return false;
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
      case ScenarioType::MANTIS_1->value:
        $ship = Meeples::getRogueShip();
        $checked = (!isset($ship));
        break;
    }
    return $checked;
  }

}
