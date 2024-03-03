<?php

namespace ROG\Models;

use ROG\Core\Game;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Core\Preferences;

/*
 * Player: all utility functions concerning a player
 */

class Player extends \ROG\Helpers\DB_Model
{
  private $map = null;
  protected $table = 'player';
  protected $primary = 'player_id';
  protected $attributes = [
    'id' => ['player_id', 'int'],
    'no' => ['player_no', 'int'],
    'name' => 'player_name',
    'color' => 'player_color',
    'eliminated' => 'player_eliminated',
    'score' => ['player_score', 'int'],
    'scoreAux' => ['player_score_aux', 'int'],
    'zombie' => 'player_zombie',
    //GAME SPECIFIC :
    'money' => ['money', 'int'],
    //array of numbers for each trade good
    'resources' => ['resources', 'obj'],
    //1->6
    'die' => ['die_face', 'int'],

  ];

  public function getUiData($currentPlayerId = null)
  {
    $data = parent::getUiData();
    $current = $this->id == $currentPlayerId;

    $data['silk'] = $this->getResource(RESOURCE_TYPE_SILK);
    $data['rice'] = $this->getResource(RESOURCE_TYPE_RICE);
    $data['pottery'] = $this->getResource(RESOURCE_TYPE_POTTERY);
    return $data;
  }

  public function getPref($prefId)
  {
    return Preferences::get($this->id, $prefId);
  }

  public function getStat($name)
  {
    $name = 'get' . \ucfirst($name);
    return Stats::$name($this->id);
  }
  
  public function addPoints($points)
  {
    if($points == 0) return;
    $this->setScore( $this->getScore() + $points);
    Stats::inc( "score", $this->id, $points );
  }
  
  /**
   * Increment resource number of this type
   * @param int $nb
   * @param int $type
   */
  public function giveResource($nb, $type)
  {
    if($nb == 0) return;
    $resources = $this->getResources();
    if(!isset($resources) ) $resources = [];
    if(!isset($resources[$type]) ) $resources[$type] = 0;
    $resources[$type] += $nb;
    $this->setResources($resources);
    //TODO JSA stat
    Notifications::giveResource($this,$nb,$type);
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
  
  public function setTieBreakerPoints($points)
  {
    $this->setScoreAux($points);
  }
  public function addTieBreakerPoints($points)
  {
    if($points == 0) return;
    $this->incScoreAux($points);
  }

  /**
   * Sets player datas related to turn number $turnIndex
   * @param int $turnIndex
   */
  public function startTurn($turnIndex)
  { 
  }
  
  public function giveExtraTime(){
    Game::get()->giveExtraTime($this->getId());
  }
  
  /**
   * Rolls a D6
   * @return int
   */
  public function rollDie(){
    $dieFaces = [1,2,3,4,5,6];
    $this->setDie($dieFaces[array_rand($dieFaces)]);
    $die_face = $this->getDie();
    Notifications::rollDie($this,$die_face);
    return $die_face;
  }
}
