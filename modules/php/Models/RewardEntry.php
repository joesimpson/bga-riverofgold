<?php

namespace ROG\Models;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Managers\Meeples;
use ROG\Managers\Players;

class RewardEntry implements \JsonSerializable
{
  /** Type of resources */
  public int $type;
  /** Number of resources */
  public int $number;

   /**
   * @param int $type
   * @param int $number
   */
  public function __construct($type,$number)
  { 
    $this->type = $type;
    $this->number = $number;
  }
  
  /**
   * Return an array of attributes
   */
  public function jsonSerialize()
  {
    $data = [];
    $data['type'] = $this->type;
    $data['n'] = $this->number;
    return $data;
  }

  public function getUiData()
  {
    $data = $this->jsonSerialize(); 
    return $data;
  }

  /**
   * reward a player in a given region
   * @param Player $player
   * @param int $region
   * @param BuildingTile $tile
   */
  public function rewardPlayer($player, $region,$tile){

    switch($this->type){
      case BONUS_TYPE_POINTS:
        $player->addPoints($this->number);
        return;
      case BONUS_TYPE_INFLUENCE:
        Players::gainInfluence($player,$region,$this->number);
        return;
      case RESOURCE_TYPE_SILK:
      case RESOURCE_TYPE_POTTERY:
      case RESOURCE_TYPE_RICE:
      case RESOURCE_TYPE_SUN:
      case RESOURCE_TYPE_MONEY:
        $player->giveResourceFromTile($this->number,$this->type,$tile);
        return;
      case BONUS_TYPE_CHOICE:
        Globals::addBonus($player,BONUS_TYPE_CHOICE);
        return;
      case BONUS_TYPE_MONEY_PER_CUSTOMER:
        $player->giveResourceFromTile($this->number * $player->getNbDeliveredCustomers(),RESOURCE_TYPE_MONEY,$tile);
        return;
      case BONUS_TYPE_MONEY_PER_PORT:
        $nbBuildings = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_PORT);
        $player->giveResourceFromTile($this->number * $nbBuildings,RESOURCE_TYPE_MONEY,$tile);
        return;
      case BONUS_TYPE_MONEY_PER_MANOR:
        $nbBuildings = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_MANOR);
        $player->giveResourceFromTile($this->number * $nbBuildings,RESOURCE_TYPE_MONEY,$tile);
        return;
      case BONUS_TYPE_MONEY_PER_MARKET:
        $nbBuildings = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_MARKET);
        $player->giveResourceFromTile($this->number * $nbBuildings,RESOURCE_TYPE_MONEY,$tile);
        return;
      case BONUS_TYPE_MONEY_PER_SHRINE:
        $nbBuildings = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_SHRINE);
        $player->giveResourceFromTile($this->number * $nbBuildings,RESOURCE_TYPE_MONEY,$tile);
        return;
      case BONUS_TYPE_DRAW:
        Globals::addBonus($player,BONUS_TYPE_DRAW);
        return;
      default :
        Game::get()->error("Not supported reward ".$this->type);
        Notifications::message("Not supported reward ".$this->type);
    }
  }
}
