<?php

namespace ROG\Models;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Utils;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;

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
    $data['monkType'] = $this->getMonkType();
    return $data;
  }
  
  /**
   * Play  the instantaneous effect of this card
   * @param Player $player
   */
  public function playDeliveryAbility(&$player)
  {
    switch($this->getCustomerType()){
      case CUSTOMER_TYPE_ARTISAN:
        Players::gainInfluence($player,$this->getRegion(),NB_INLUENCE_ARTISAN);
        Meeples::addClanMarkerOnArtisanSpace($player,$this->getRegion());
        break;
      case CUSTOMER_TYPE_ELDER:
        Meeples::addClanMarkerOnElderSpace($player,$this->getRegion());
        break;
      case CUSTOMER_TYPE_MERCHANT:
        Players::gainInfluence($player,$this->getRegion(),NB_INLUENCE_MERCHANT);
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
        Players::gainInfluence($player,$this->getRegion(),NB_INLUENCE_NOBLE);
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
      case CARD_MONK_1:
      case CARD_MONK_3:
      case CARD_MONK_5:
        return MONK_TYPE_OWN_BUILDING;
      case CARD_MONK_2:
      case CARD_MONK_4:
      case CARD_MONK_6:
        return MONK_TYPE_OPPONENT_BUILDING;
    }
    return null;
  }
  
  /**
   * @param Player $player
   * @param int $type
   */
  public static function playOngoingMerchantAbility(&$player,$type)
  {
    switch($type){
      case CARD_MERCHANT_1://+ 1 Influence in built regions
        $regions = $player->getBuiltRegions();
        foreach($regions as $region){
          Players::gainInfluence($player,$region,NB_INLUENCE_MERCHANT_1);
        }
        break;
      case CARD_MERCHANT_2://+1 point per card
        $player->addPoints($player->getNbDeliveredCustomers());
        break;
      case CARD_MERCHANT_3:
        Globals::addBonus($player,BONUS_TYPE_CHOICE);
        break;
      case CARD_MERCHANT_4://bonus to sell goods
        Globals::addBonus($player,BONUS_TYPE_SELL_GOODS,clienttranslate("Sell goods"));
        break;
      case CARD_MERCHANT_5://+1 Sun
        $player->giveResource(1,RESOURCE_TYPE_SUN);
        break;
      case CARD_MERCHANT_6://+3 Koku
        $player->giveResource(3,RESOURCE_TYPE_MONEY);
        break;
    }
  }

  
  /**
   * Compute END score for a specific card
   * @param Player $player
   * @return int score for this player
   */
  public function computeScore($player)
  {
    $score = 0;
    switch($this->getType()){
      case CARD_NOBLE_1://1 point per market
        $score = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_MARKET);
        break;
      case CARD_NOBLE_2://1 point per port
        $score = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_PORT);
        break;
      case CARD_NOBLE_3://1 point per manor
        $score = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_MANOR);
        break;
      case CARD_NOBLE_4://1 point per shrine
        $score = Meeples::countPlayerBuildings($player->getId(),BUILDING_TYPE_SHRINE);
        break;
      case CARD_NOBLE_5://1 point per different type of buildings
        $nbType = 0;
        foreach(BUILDING_TYPES as $type){
          if(Meeples::countPlayerBuildings($player->getId(),$type) >0) $nbType++;
        }
        $score = $nbType;
        break;
      case CARD_NOBLE_6://1 point per different region of buildings
        $score = count($player->getBuiltRegions());
        break;
    }
    if($score>0){
      Notifications::scoreCustomer($player,$this,$score);
    }
    return $score;
  }
}
