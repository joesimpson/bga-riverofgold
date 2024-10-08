<?php

namespace ROG\Models;

use ROG\Core\Game;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Core\Preferences;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;

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
    //array of numbers for each trade good
    'resources' => ['resources', 'obj'],
    //1->6
    'die' => ['die_face', 'int'],
    //1->4
    'clan' => ['player_clan', 'int'],
    //array of bonus types (int)
    'bonuses' => ['bonuses', 'obj'],
    //is last turn played ?
    'lastTurnPlayed' => ['last_turn_played', 'bool'],
    //is expected to skip next die roll ? FALSE except for some clan patrons and if player DECIDES to
    'skipRollDie' => ['skip_roll_die', 'bool'],

  ];

  public function getUiData($currentPlayerId = null)
  {
    $data = parent::getUiData();
    $current = $this->id == $currentPlayerId;

    $data['money'] = $this->getMoney();
    $data['silk'] = $this->getResource(RESOURCE_TYPE_SILK);
    $data['rice'] = $this->getResource(RESOURCE_TYPE_RICE);
    $data['pottery'] = $this->getResource(RESOURCE_TYPE_POTTERY);
    $data['moon'] = $this->getResource(RESOURCE_TYPE_MOON);
    $data['sun'] = $this->getResource(RESOURCE_TYPE_SUN);
    unset($data['resources']);
    unset($data['bonuses']);
    
    $data['buildings'] = [];
    foreach (BUILDING_TYPES as $bType){
      $data['buildings'][$bType] = Meeples::countPlayerBuildings($this->getId(),$bType);
    }
    $data['influence'] = [];
    foreach (REGIONS as $region){
      $data['influence'][$region] = $this->getInfluence($region);
    }
    $data['customers'] = [];
    foreach (CUSTOMER_TYPES as $customer){
      $data['customers'][$customer] = $this->getNbDeliveredCustomerByType($customer);
    }
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
  
  /**
   * @param int $points
   * @param bool $sendNotif (Default true)
   */
  public function addPoints($points, $sendNotif = true)
  {
    if($points == 0) return;
    //$this->setScore( $this->getScore() + $points);
    //$this->incScore($points); // SAME as previous
    //REAL INC in DB in case of not up to date score in object
    Players::incPlayerScore($this->getId(), $points);
    Stats::inc( "score", $this->id, $points );
    if($sendNotif) Notifications::addPoints($this,$points);
  }
  
  /**
   * Increment resource number of this type
   * @param int $nb
   * @param int $type
   * @param bool $sendNotif (Optional) default true
   * @return int real increment applied after checking max
   */
  public function giveResource($nb, $type, $sendNotif = true)
  {
    Game::get()->trace("giveResource($nb, $type, $sendNotif)");
    if($nb == 0) return 0;
    $resources = $this->getResources();
    if(!isset($resources) ) $resources = [];
    if(!isset($resources[$type]) ) $resources[$type] = 0;
    $before = $resources[$type];
    $resources[$type] += $nb;
    $max = NB_MAX_RESOURCE;
    if(array_key_exists($type,RESOURCES_LIMIT) ) {
      $max = RESOURCES_LIMIT[$type];
    } else if(RESOURCE_TYPE_SUN == $type){
      $max = $resources[RESOURCE_TYPE_MOON]; 
    }
    $resources[$type] = min($resources[$type], $max);
    $nb = $resources[$type] - $before;
    $this->setResources($resources);
    //Stats :
    if(RESOURCE_TYPE_MONEY == $type){
      Stats::set("moneyLeft",$this,$resources[$type]);
      if($nb>0){
        Stats::inc("moneyReceived",$this,$nb);
      }
      else if($nb<0){
        Stats::inc("moneySpent",$this,-$nb);
      }
    }
    if($sendNotif) Notifications::giveResource($this,$nb,$type);
    return $nb;
  }

  /**
   * Increment resource number of this type
   * @param int $nb
   * @param int $type
   * @param BuildingTile $tile
   */
  public function giveResourceFromTile($nb, $type, $tile)
  {
    $realInc = $this->giveResource($nb, $type,false);
    Notifications::giveResource($this,$realInc,$type,$tile);
  }
  
  /**
   * Increment resource number of this type
   * @param int $nb
   * @param int $type
   * @param int $shoreSpace
   */
  public function giveResourceFromShoreSpace($nb, $type, $shoreSpace)
  {
    $realInc = $this->giveResource($nb, $type,false);
    Notifications::giveResource($this,$realInc,$type,null, $shoreSpace);
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

  /**
   * @return int 
   */
  public function getMoney()
  {
    return $this->getResource(RESOURCE_TYPE_MONEY);
  }
  /**
   * @return bool 
   */
  public function canReceiveMoney()
  {
    $money = $this->getMoney();
    $max = 1000;
    if(array_key_exists(RESOURCE_TYPE_MONEY,RESOURCES_LIMIT) ) {
      $max = RESOURCES_LIMIT[RESOURCE_TYPE_MONEY];
    }
    if($money < $max) return true;
    return false;
  }
  
  /**
   * @param int $region
   * @return int 
   */
  public function getInfluence($region)
  {
    $meeple = Meeples::getInfluenceMarker($this->getId(),$region);
    if(!isset($meeple)) return 0;
    return $meeple->getPosition();
  }
  /**
   * @param int $region
   * @param int $value
   */
  public function setInfluence($region,$value)
  {
    $meeple = Meeples::getInfluenceMarker($this->getId(),$region);
    if(!isset($meeple)) return ;
    $meeple->setPosition($value);
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
    if(!$this->isSkipRollDie()){
      $dieFaces = DIE_FACES;
      $this->setDie($dieFaces[array_rand($dieFaces)]);
      $die_face = $this->getDie();
      Notifications::rollDie($this,$die_face);
    }
    //Reset next decision
    $this->setSkipRollDie(false);
    return $this->getDie();
  }

  
  /**
   * @return int 
   */
  public function getNbDeliveredCustomers()
  {
    return Cards::countPlayerCards($this->getId(),CARD_LOCATION_DELIVERED);
  }
  
  /**
   * @param int $customerType
   * @return int 
   */
  public function getNbDeliveredCustomerByType($customerType)
  {
    return Cards::countDeliveredCardsByCustomerType($this->getId(),$customerType);
  }
  /**
   * @param int $region
   * @return int 
   */
  public function getNbDeliveredCustomerByRegion($region)
  {
    return Cards::countDeliveredCardsByCustomerRegion($this->getId(),$region);
  }

  /**
   * @return ?ClanPatronCard 
   */
  public function getPatron()
  {
    return Cards::getPatron($this);
  }

  /**
   * @return ?Meeple the royal ship currently on the river, or null
   */
  public function getRoyalShip()
  {
    return Meeples::getBoats($this->getId())->filter( function($ship) { 
      return MEEPLE_TYPE_SHIP_ROYAL ==$ship->getType(); 
    })->first();
  }

  /**
   * @return array of int, id of regions with own buildings
   */
  public function getBuiltRegions()
  {
    $markersOnBuildings = Meeples::getPlayerBuildingsMarkers($this->getId());
    $regions = array_unique( $markersOnBuildings->map( function($meeple) { 
      return $meeple->getBuildingRegion();
    })->toArray());
    Game::get()->trace("getBuiltRegions()".$this->getId().":". json_encode($regions));
    return $regions;
  }
}
