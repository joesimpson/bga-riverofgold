<?php

namespace ROG\Models;

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
   */
  public function rewardPlayer($player, $region){

    switch($this->type){
      case BONUS_TYPE_POINTS:
        $player->addPoints($this->number);
        Notifications::addPoints($player,$this->number);
        return;
      case BONUS_TYPE_INFLUENCE:
        $bonusChoice = Players::gainInfluence($player,$region,$this->number);
        if($bonusChoice){
          //TODO JSA how to add another reward ?
        }
        return;
      case RESOURCE_TYPE_SILK:
      case RESOURCE_TYPE_POTTERY:
      case RESOURCE_TYPE_RICE:
      case RESOURCE_TYPE_SUN:
      case RESOURCE_TYPE_MONEY:
        $player->giveResource($this->number,$this->type);
        return;
      case BONUS_TYPE_MONEY_PER_CUSTOMER:
        $player->giveResource($this->number * $player->getNbDeliveredCustomer(),RESOURCE_TYPE_MONEY);
        return;
      case BONUS_TYPE_MONEY_PER_PORT:
        $nbBuildings = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_PORT);
        $player->giveResource($this->number * $nbBuildings,RESOURCE_TYPE_MONEY);
        return;
      case BONUS_TYPE_MONEY_PER_MANOR:
        $nbBuildings = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_MANOR);
        $player->giveResource($this->number * $nbBuildings,RESOURCE_TYPE_MONEY);
        return;
      case BONUS_TYPE_MONEY_PER_MARKET:
        $nbBuildings = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_MARKET);
        $player->giveResource($this->number * $nbBuildings,RESOURCE_TYPE_MONEY);
        return;
      case BONUS_TYPE_MONEY_PER_SHRINE:
        $nbBuildings = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_SHRINE);
        $player->giveResource($this->number * $nbBuildings,RESOURCE_TYPE_MONEY);
        return;
    }
    //TODO JSA SAIling : specific visitor rewards types : BONUS_TYPE_CHOICE / BONUS_TYPE_DRAW
    Notifications::message("TODO reward ".json_encode($this));
  }
}
