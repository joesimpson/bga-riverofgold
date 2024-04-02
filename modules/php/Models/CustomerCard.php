<?php

namespace ROG\Models;

use ROG\Core\Globals;
use ROG\Managers\Meeples;
use ROG\Managers\Players;

/**
 * CustomerCard: all utility functions concerning a Customer card
 */

class CustomerCard extends Card
{ 
  
  protected $staticAttributes = [
    ['customerType', 'int'],
    ['region', 'int'],
    ['cost', 'obj'],
    ['title', 'string'],
    ['desc', 'string'],
  ];

  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  } 
  
  public function getUiData()
  {
    $data = parent::getUiData();
    $data['subtype'] = CARD_TYPE_CUSTOMER;
    return $data;
  }
  
  /**
   * Play  the instantaneous effect of this card
   * @param Player $player
   */
  public function playDeliveryAbility($player)
  {
    switch($this->getCustomerType()){
      case CUSTOMER_TYPE_ARTISAN:
        $bonusChoice = Players::gainInfluence($player,$this->getRegion(),NB_INLUENCE_ARTISAN);
        if($bonusChoice) Globals::addBonus($player,BONUS_TYPE_CHOICE);
        Meeples::addClanMarkerOnArtisanSpace($player,$this->getRegion());
        break;
      case CUSTOMER_TYPE_ELDER:
        Meeples::addClanMarkerOnElderSpace($player,$this->getRegion());
        break;
      case CUSTOMER_TYPE_MERCHANT:
        $bonusChoice = Players::gainInfluence($player,$this->getRegion(),NB_INLUENCE_MERCHANT);
        if($bonusChoice) Globals::addBonus($player,BONUS_TYPE_CHOICE);
        Meeples::addClanMarkerOnMerchantSpace($player);
        break;
      case CUSTOMER_TYPE_MONK:
        $player->giveResource(1,RESOURCE_TYPE_MOON);
        $player->giveResource(2,RESOURCE_TYPE_SUN);
        //ASK player choice to add a marker on a building
        $monkType = $this->getMonkType();
        //TODO JSA check there is a building to select
        if(MONK_TYPE_OWN_BUILDING == $monkType) Globals::addBonus($player,BONUS_TYPE_SECOND_MARKER_ON_BUILDING);
        else if(MONK_TYPE_OPPONENT_BUILDING == $monkType) Globals::addBonus($player,BONUS_TYPE_SECOND_MARKER_ON_OPPONENT);
        break;
      case CUSTOMER_TYPE_NOBLE:
        $bonusChoice = Players::gainInfluence($player,$this->getRegion(),NB_INLUENCE_NOBLE);
        if($bonusChoice) Globals::addBonus($player,BONUS_TYPE_CHOICE);
        //Ask player to choose a ship to become ROYAL (only the first time)
        $royalShip = $player->getRoyalShip();
        if(!isset($royalShip)) Globals::addBonus($player,BONUS_TYPE_UPGRADE_SHIP);
        break;
    }
  } 
  
  /**
   * @return int
   */
  public function getMonkType()
  {
    switch($this->getType()){
      case 19://TODO JSA CONSTANTS
      case 21:
      case 23:
        return MONK_TYPE_OWN_BUILDING;
      case 20:
      case 22:
      case 24:
        return MONK_TYPE_OPPONENT_BUILDING;
    }
    return null;
  } 
}
