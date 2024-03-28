<?php

namespace ROG\Models;

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
        //TODO JSA how to display bonus ?
        Meeples::addClanMarkerOnArtisanSpace($player,$this->getRegion());
        break;
      case CUSTOMER_TYPE_ELDER:
        Meeples::addClanMarkerOnElderSpace($player,$this->getRegion());
        break;
      case CUSTOMER_TYPE_MERCHANT:
        $bonusChoice = Players::gainInfluence($player,$this->getRegion(),NB_INLUENCE_MERCHANT);
        Meeples::addClanMarkerOnMerchantSpace($player);
        break;
      case CUSTOMER_TYPE_MONK:
        $player->giveResource(1,RESOURCE_TYPE_MOON);
        $player->giveResource(2,RESOURCE_TYPE_SUN);
        //TODO JSA ASK player choice to add a marker on a building
        break;
      case CUSTOMER_TYPE_NOBLE:
        $bonusChoice = Players::gainInfluence($player,$this->getRegion(),NB_INLUENCE_NOBLE);
        //TODO JSA Ask player to choose a ship to become ROYAL (only the first time)
        break;
    }
  } 
}
