<?php

namespace ROG\Models;

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
  

  public function getUiData()
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
  public function getTitle()
  {
    switch($this->getScoringType()){
      case MASTERY_TYPE_AIR: return clienttranslate('Mastery of Air');
      case MASTERY_TYPE_COURTS: return clienttranslate('Mastery of the Courts');
      case MASTERY_TYPE_EARTH: return clienttranslate('Mastery of Earth');
      case MASTERY_TYPE_FIRE: return clienttranslate('Mastery of Fire');
      case MASTERY_TYPE_VOID: return clienttranslate('Mastery of Void');
      case MASTERY_TYPE_WATER: return clienttranslate('Mastery of Water');
      default: return '';
    }
  }
}
