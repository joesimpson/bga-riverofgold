<?php

namespace ROG\Models;

use ROG\Core\Game;
use ROG\Core\Notifications;

/*
 * Card: all utility functions concerning a card
 */

class Card extends \ROG\Helpers\DB_Model
{
  protected $table = 'cards';
  protected $primary = 'card_id';
  protected $attributes = [
    'id' => ['card_id', 'int'],
    'state' => ['card_state', 'int'],
    'location' => 'card_location',
    'pId' => ['player_id', 'int'],
    'type' => ['type', 'int'],

    //array of numbers of resources (money/trade goods placed on the card)
    'resources' => ['resources', 'obj'],
    
    'played' => ['card_played', 'bool'],
  ];
   
  public function __construct($row, $datas)
  {
    parent::__construct($row);
    foreach ($datas as $attribute => $value) {
      $this->$attribute = $value;
    }
  }

  public function getUiData()
  {
    $data = parent::getUiData();
    //useless for majority of cards
    unset($data['resources']);
    unset($data['played']);
    return $data;
  }

  
  /**
   * Increment resource number of this type
   * @param Player $player
   * @param int $nb
   * @param int $type
   * @param bool $sendNotif (Optional) default true
   * @return int real increment applied after checking max
   */
  public function addResource(Player $player,int $nb,int $type,bool $sendNotif = true) : int
  {
    if($nb == 0) return 0;
    $resources = $this->getResources();
    Game::get()->trace(__CLASS__.".".__FUNCTION__."($nb,$type) - BEFORE =".json_encode($resources));
    if(!isset($resources) ) $resources = [];
    if(!isset($resources[$type]) ) $resources[$type] = 0;
    $before = $resources[$type];
    $resources[$type] += $nb;
    //No max check HERE
    $realNb = $resources[$type] - $before;
    $this->setResources($resources);
    if($sendNotif) Notifications::addResourceOnCard($player,$this,$realNb,$type);
    Game::get()->trace(__CLASS__.".".__FUNCTION__."($nb,$type) - AFTER =".json_encode($resources));
    return $realNb;
  }
  
  /**
   * @param int $type
   * @return int resource number of this type
   */
  public function getResource($type)
  {
    $resources = $this->getResources();
    if(!isset($resources) ) return 0;
    if(!isset($resources[$type]) ) return 0;
    return $resources[$type];
  }
}
