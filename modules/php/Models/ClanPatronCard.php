<?php

namespace ROG\Models;

use ROG\Core\Notifications;
use ROG\Managers\ShoreSpaces;

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
    }
    return '';
  } 

  /**
   * @param Player $player
   * @param ShoreSpace $shoreSpace
   */
  public function  scoreWhenBuild($player,$shoreSpace){
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
  public function  scoreWhenDeliver($player, $card){
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
}
