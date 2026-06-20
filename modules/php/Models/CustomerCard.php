<?php

namespace ROG\Models;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Helpers\Utils;
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
    $data['monkType'] = $this->getMonkType();
    return $data;
  }
  
  public function formatNameForNotif() : array{
    return [
        'log'=> '${customer_type} ${region}',
        'args'=> [
          'i18n' => ['customer_type'],
          'customer_type' => $this->getTitle(),
          'region' => $this->getRegion(),
        ]
      ];
  }
  
  /**
   * Play  the instantaneous effect of this card
   * @param Player $player
   */
  public function playDeliveryAbility(Player &$player)
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
        if(MONK_TYPE_OWN_BUILDING == $monkType) Globals::addBonus($player,BONUS_TYPE_SECOND_MARKER_ON_BUILDING);
        else if(MONK_TYPE_OPPONENT_BUILDING == $monkType) Globals::addBonus($player,BONUS_TYPE_SECOND_MARKER_ON_OPPONENT);
        break;
      case CUSTOMER_TYPE_NOBLE:
        Players::gainInfluence($player,$this->getRegion(),NB_INLUENCE_NOBLE);
        //Ask player to choose a ship to become ROYAL (only the first time)
        $royalShip = $player->getRoyalShip();
        if(!isset($royalShip)) Globals::addBonus($player,BONUS_TYPE_UPGRADE_SHIP);
        break;

      case CUSTOMER_TYPE_MAGISTRATE:
        Players::gainInfluence($player,$this->getRegion(),NB_INFLUENCE_MAGISTRATE);
        break;
      case CUSTOMER_TYPE_SMUGGLER:
        Players::gainInfluence($player,$this->getRegion(),NB_INFLUENCE_SMUGGLER);
        //Ask player to choose a building owner reward
        $nbBuildings = Meeples::countPlayerBuildings($player->getId());
        if($nbBuildings>0) Globals::addBonus($player,BONUS_TYPE_ANY_OWNER_REWARD,clienttranslate('Building reward'));
        break;
      case CUSTOMER_TYPE_SHINDOSHI:
        Players::gainInfluence($player,$this->getRegion(),NB_INFLUENCE_SHIN);
        Meeples::addClanMarkerOnCard($player,$this);
        break;
      case CUSTOMER_TYPE_SPY:
        Globals::addBonus($player,BONUS_TYPE_ADVANCE_OR_POINTS);
        break;
      case CUSTOMER_TYPE_TRADER:
        Players::gainInfluence($player,$this->getRegion(),NB_INFLUENCE_TRADER);
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
   * @param int $type : customer type
   */
  public static function playOngoingAbility(Player &$player,int $type)
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
        
      case CARD_TRADER_1:
      case CARD_TRADER_2:
      case CARD_TRADER_3:
      case CARD_TRADER_4:
      case CARD_TRADER_5:
      case CARD_TRADER_6:
        $player->addPoints(NB_POINTS_TRADER);
        Globals::addBonus($player,BONUS_TYPE_DRAW);
        break;
    }
  }

  
  /**
   * Compute END score for a specific card
   * @param Player $player
   * @return int score for this player
   */
  public function computeScore(Player $player, Collection $allDelivered)
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

      case CARD_SMUGGLER_1: //+1 / 2 rice on delivered cards
      case CARD_SMUGGLER_3:
        $nbResources = Utils::countCardsCost($allDelivered,RESOURCE_TYPE_RICE);
        $score = round (1/2 * $nbResources,0,PHP_ROUND_HALF_DOWN);
        break;
      case CARD_SMUGGLER_2: //+1 / 2 silk on delivered cards 
      case CARD_SMUGGLER_6:
        $nbResources = Utils::countCardsCost($allDelivered,RESOURCE_TYPE_SILK);
        $score = round (1/2 * $nbResources,0,PHP_ROUND_HALF_DOWN);
        break;
      case CARD_SMUGGLER_4: //+1 / 2 pottery on delivered cards 
      case CARD_SMUGGLER_5:
        $nbResources = Utils::countCardsCost($allDelivered,RESOURCE_TYPE_POTTERY);
        $score = round (1/2 * $nbResources,0,PHP_ROUND_HALF_DOWN);
        break;
        
      case CARD_TRADER_1:
      case CARD_TRADER_2:
      case CARD_TRADER_3:
      case CARD_TRADER_4:
      case CARD_TRADER_5:
      case CARD_TRADER_6:
        $region = $this->getRegion();
        $nbDeliveriesInRegion = $player->getNbDeliveredCustomerByRegion($region);
        $nbBuildingsInRegion = Meeples::countPlayerBuildingsInRegion($player->getId(),$region);
        $score = 2 * ($nbDeliveriesInRegion + $nbBuildingsInRegion);
        break;

    }
    if($score>0){
      Notifications::scoreCustomer($player,$this,$score);
    }
    return $score;
  }

  public function getCostAsTradeGoods() : int{
    $sumNeededGoods = 0;
    foreach($this->getCost() as $neededType => $neededAmount){
      $isTradeGood = in_array($neededType, [RESOURCE_TYPE_SILK,RESOURCE_TYPE_RICE,RESOURCE_TYPE_POTTERY]);
      if($isTradeGood){
        $sumNeededGoods += $neededAmount;
      }
    }
    return $sumNeededGoods;
  }
}
