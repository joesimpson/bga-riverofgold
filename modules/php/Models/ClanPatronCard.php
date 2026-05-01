<?php

namespace ROG\Models;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Collection;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;

/**
 * ClanPatronCard: all utility functions concerning a Clan Patron card
 */

class ClanPatronCard extends Card
{ 
  
  protected $staticAttributes = [
    ['clan', 'int'],
    ['abilityName', 'string'],
    ['name', 'string'],
    ['desc', 'string'],
  ];

  public function __construct($row, $datas)
  {
    parent::__construct($row, $datas);
  } 

  public function getUiData()
  {
    $data = parent::getUiData();
    unset($data['state']);
    $data['subtype'] = CARD_TYPE_CLAN_PATRON;
    return $data;
  }

  /**
   * @return string
   */
  public function getClanName()
  {
    $clanId = $this->getClan();
    switch($clanId){
      case CLAN_CRAB:     return clienttranslate('Crab Clan');
      case CLAN_MANTIS:   return clienttranslate('Mantis Clan');
      case CLAN_CRANE:    return clienttranslate('Crane Clan');
      case CLAN_SCORPION: return clienttranslate('Scorpion Clan');
      case CLAN_PHOENIX:  return clienttranslate('Phoenix Clan');
      case CLAN_LION:     return clienttranslate('Lion Clan');
      case CLAN_DRAGON:   return clienttranslate('Dragon Clan');
      case CLAN_UNICORN:  return clienttranslate('Unicorn Clan');
    }
    return '';
  } 

  /**
   * @param Player $player
   * @param ShoreSpace $shoreSpace
   */
  public function scoreWhenBuild(&$player,$shoreSpace){
    switch($this->getType()){
      case PATRON_TRADER://+1 point per adjacent river space
        $nb = 0;
        for($k=1;$k<=NB_RIVER_SPACES;$k++){
          $shoresSpaces = ShoreSpaces::getAdjacentSpaces($k);
          if(in_array($shoreSpace->id,$shoresSpaces)) $nb++;
        }
        $player->addPoints($nb,false);
        Notifications::scorePatron($player,$nb,$this);
        break;
    }
  }

  /**
   * @param Player $player
   * @param CustomerCard $card delivered card
   */
  public function scoreWhenDeliver(&$player, $card){
    switch($this->getType()){
      case PATRON_PRIESTESS://+1 point IF first delivery of the region
        $region = $card->getRegion();
        if(1 == $player->getNbDeliveredCustomerByRegion($region)){
          $player->addPoints(NB_POINTS_PRIESTESS,false);
          Notifications::scorePatron($player,NB_POINTS_PRIESTESS,$this);
        }
        break;
    }
  }
  
  /**
   * @param Player $player
   * @param bool $ownBuilding sailed to own buildings
   * @param bool $opponentBuilding sailed to opponent buildings
   */
  public function scoreWhenSail(Player &$player,bool $ownBuilding,bool $opponentBuilding, Meeple $ship){
    switch($this->getType()){
      case PATRON_IRON_CRANE://+2 points IF opponent buildings
        if($opponentBuilding){
          $player->addPoints(NB_POINTS_IRON_CRANE,false);
          Notifications::scorePatron($player,NB_POINTS_IRON_CRANE,$this);
        }
        break;
      case PATRON_MAGNATE_SAND_ROAD://+2 points per own ships on space when sailing with royal ship
        if($ship->getType() == MEEPLE_TYPE_SHIP_ROYAL){
          $nbOtherShips = Meeples::countPlayerShipsInLocation($player->getId(),$ship->getPosition()) - 1;
          $points = $nbOtherShips * NB_POINTS_MAGNATE_SAND_ROAD;
          $player->addPoints($points,false);
          Notifications::scorePatron($player,$points,$this);
        }
        break;
    }
  }
  
  /**
   * @param Player $player
   */
  public function addBonuses(&$player){
    switch($this->getType()){
      case PATRON_DARLING://Darling needs a step to decide whether or not to roll the die !
        Globals::addBonus($player,BONUS_TYPE_SET_DIE,'',false);
        break;
    }
  }
  
  /**
   * Apply a card ability right after assignment
   * @param Player $player
   */
  public function abilityOnAssign(Player &$player){
    switch($this->getType()){
      case PATRON_SCION_OF_VOID: //Reserve 3 mastery tiles
        $masteryCards = Tiles::pickForLocation(3,TILE_LOCATION_MASTERY_DECK,TILE_LOCATION_MASTERY_RESERVED);
        foreach($masteryCards as $tile){
          $tile->setPId($player->getId());
          //Change tile for 2 player side :
          $oldType = $tile->getType();
          $newType = Tiles::get2PlayerSideMasteryCardType($oldType);
          $tile->setType($newType);
        }
        Notifications::giveMasteriesTo($player, $masteryCards);
        break;
    }
  }
  
  /**
   * Apply a card ability right before emperor visit
   * @param Player $player
   * @param Collection $buildingTiles
   */
  public function abilityOnEmperorVisit(Player &$player, Collection $buildingTiles){
    switch($this->getType()){
      case PATRON_REVEREND_SENSEI: //+1 clan marker on own buildings
        $newMarkers = [];
        foreach($buildingTiles as $tile){
          $clanMarkers = $tile->getMeeples();
          //check if only 1 marker on tile:
          if($clanMarkers->count() != 1) continue;
          $clanMarker = $clanMarkers->first();
          if($clanMarker->getPId() == $player->getId()){
            $newMarkers[] = Meeples::addClanMarkerOnShoreSpace($tile,$player,2,false);
          }
        }
        Notifications::newBuildingMarkersWithPatron($player,$newMarkers,$this);
        break;
    }
  }
  
  public function abilityOnVisitImperialMarket(Player &$player, int $region){
    switch($this->getType()){
      case PATRON_IMPERIAL_ENVOY:
        Players::gainInfluence($player,$region,2, $this);
        break;
    }
  }
  
  public function abilityOnCustomerDiscard(Player &$player,){
    switch($this->getType()){
      case PATRON_TATTOOED_MONK:
        Notifications::activePatron($player,$this);
        $player->giveResource(1,RESOURCE_TYPE_SILK);
        break;
    }
  }

}
