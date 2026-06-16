<?php

namespace ROG\Models;

use ROG\Core\Game;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;

/*
 * MasteryCard: all utility functions concerning a Mastery Card
 */

class MasteryCard extends Tile
{
  
  protected $staticAttributes = [
    ['scoringType', 'int'],
    ['nbPlayers', 'obj'],
    ['scores', 'obj'],
  ];

  
  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  }
  

  public function getUiData() : array
  {
    $data = parent::getUiData();
    $data['title'] = $this->getTitle();
    $data['subtype'] = TILE_TYPE_MASTERY_CARD;
    unset($data['state']);
    unset($data['scores']);
    return $data;
  }
  
  /**
   * @return string to be displayed as the title of this card
   */
  public function getTitle() : string
  {
    switch($this->getScoringType()){
      case MASTERY_TYPE_AIR: return clienttranslate('Mastery of Air');
      case MASTERY_TYPE_COURTS: return clienttranslate('Mastery of the Courts');
      case MASTERY_TYPE_EARTH: return clienttranslate('Mastery of Earth');
      case MASTERY_TYPE_FIRE: return clienttranslate('Mastery of Fire');
      case MASTERY_TYPE_VOID: return clienttranslate('Mastery of Void');
      case MASTERY_TYPE_WATER: return clienttranslate('Mastery of Water');
      case MASTERY_TYPE_WAVES    : return clienttranslate('Mastery of Waves');
      case MASTERY_TYPE_SUN_MOON : return clienttranslate('Mastery of Sun & Moon');
      case MASTERY_TYPE_LIGHTNING: return clienttranslate('Mastery of Lightning');
      default: return '';
    }
  }
  
  /**
   * @return bool true if player can claim this specific Mastery tile, false otherwise
   */
  public function checkRequirements(Player $player) : bool
  {
    $claim = false;

    $pId = $player->getId();
    if($player instanceof AutomaPlayer){
      //Instead of checking the masteries’ requirements, she automatically claims the leftmost or rightmost available mastery
      return true;
    }

    switch($this->scoringType){
      //----------------------------------------------------------------------
      case MASTERY_TYPE_AIR: //1 of 3 customers
        $countCustomers = 0;
        foreach (ALL_CUSTOMER_TYPES as $customer){
          if($player->getNbDeliveredCustomerByType($customer) >= 1) $countCustomers++;
          if($countCustomers >= NB_CUSTOMERS_FOR_AIR){
            $claim = true;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_FIRE: //2 of same customer
        foreach (ALL_CUSTOMER_TYPES as $customer){
          if($player->getNbDeliveredCustomerByType($customer) >= NB_CUSTOMERS_FOR_FIRE){
            $claim = true;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_COURTS://flower in 1 region
        foreach (REGIONS as $region){
          if($player->getInfluence($region) >= NB_INLUENCE_FLOWER){
            $claim = true;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_WATER: //1 of each building
        $claim = true;
        foreach (BUILDING_TYPES as $bType){
          if(Meeples::countPlayerBuildings($player->getId(),$bType) < 1){
            $claim = false;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_VOID://influence in each region
        $claim = true;
        foreach (REGIONS as $region){
          if($player->getInfluence($region) < NB_INLUENCE_VOID){
            $claim = false;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_EARTH: //3 of 1 building
        foreach (BUILDING_TYPES as $bType){
          if(Meeples::countPlayerBuildings($player->getId(),$bType) >= NB_BUILDINGS_WATER){
            $claim = true;
            break;
          }
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_WAVES: //7 unique river spaces near player buildings
        $shoreSpaces = Tiles::getPlayerBuildingTilesShoreSpace($pId);
        $riverSpaces = ShoreSpaces::getUniqueAdjacentRiverSpaces($shoreSpaces);
        if(count($riverSpaces) >= NB_SPACES_FOR_MASTERY_WAVES){
          $claim = true;
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_SUN_MOON: //>=5 favor
        if($player->getResource(RESOURCE_TYPE_SUN) >= NB_FAVOR_FOR_MASTERY_SUN_MOON){
          $claim = true;
        }
        break;
      //----------------------------------------------------------------------
      case MASTERY_TYPE_LIGHTNING: // score >= 30 points
        $playerScore = Players::getUpdatedPlayerScore($pId);
        if($playerScore >= NB_POINTS_FOR_MASTERY_LIGHTNING){
          $claim = true;
        }
        else {
          Game::get()->trace("checkRequirements(MASTERY_TYPE_LIGHTNING) score $playerScore is not enough "); 
        }
        break;
      //----------------------------------------------------------------------
    }
    return $claim;
  }
}
