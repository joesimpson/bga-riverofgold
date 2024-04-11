<?php

namespace ROG\Models;

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

}
