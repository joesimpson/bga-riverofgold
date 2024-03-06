<?php

namespace ROG\Managers;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Exceptions\UnexpectedException;
use ROG\Models\Player;

/*
 * Players manager : allows to easily access players ...
 *  a player is an instance of Player class
 */

class Players extends \ROG\Helpers\DB_Manager
{
  protected static $table = 'player';
  protected static $primary = 'player_id';
  protected static function cast($row)
  {
    return new \ROG\Models\Player($row);
  }

  /**
   * @param array $players
   * @return Collection $players
   */
  public static function setupNewGame($players, $options)
  {
    // Create players
    $gameInfos = Game::get()->getGameinfos();
    $colors = $gameInfos['player_colors'];
    $query = self::DB()->multipleInsert(['player_id', 'player_color', 'player_canal', 'player_name', 'player_avatar']);

    $values = [];
    $k =0;
    foreach ($players as $pId => $player) {
      $color = array_shift($colors);
      $values[] = [$pId, $color, $player['player_canal'], $player['player_name'], $player['player_avatar']];
      $k++;
    }
    $query->values($values);

    Game::get()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);
    Game::get()->reloadPlayersBasicInfos();
    return self::getAll();
  }

  /**
   * @param Collection $players Players
   * @param int $turn
   */
  public static function setupNewTurn($players,$turn)
  {
    Game::get()->trace("setupNewTurn($turn)");
    if(!Globals::isModeCompetitive()) return;

    $players = $players->filter(function ($player) { 
      return $player->getZombie() ==0 && $player->getEliminated() == 0;
    });
    
  }

  public static function getActiveId()
  {
    return Game::get()->getActivePlayerId();
  }

  public function getCurrentId($bReturnNullIfNotLogged = false)
  {
    return (int) Game::get()->getCurrentPId($bReturnNullIfNotLogged);
  }

  public static function getAll()
  {
    return self::DB()->get(false);
  }

  /*
   * get : returns the Player object for the given player ID
   */
  public static function get($pId = null)
  {
    $pId = $pId ?: self::getActiveId();
    return self::DB()
      ->where($pId)
      ->getSingle();
  }

  public static function getActive()
  {
    return self::get();
  }

  public static function getCurrent()
  {
    return self::get(self::getCurrentId());
  }

  public static function getNextId($player = null)
  {
    $player = $player ?? Players::getCurrent();
    $pId = is_int($player) ? $player : $player->getId();
    $table = Game::get()->getNextPlayerTable();
    return $table[$pId];
  }
  
  /**
   * @param int $player_id
   * @return Player
   */
  public static function getNextPlayerNotEliminated($player_id)
  {
    $nextPlayer_id = Players::getNextId($player_id);
    $nextPlayer = Players::get($nextPlayer_id);
    if(isset($nextPlayer) 
      && $nextPlayer->getZombie() != 1 && $nextPlayer->getEliminated() == 0
    ){
      return $nextPlayer;
    }
    return self::getNextPlayerNotEliminated($nextPlayer_id);
  }


  /*
   * Return the number of players
   */
  public static function count()
  {
    return self::DB()->count();
  }

  /*
   * getUiData : get all ui data of all players
   */
  public static function getUiData($pId)
  {
    return self::getAll()
      ->map(function ($player) use ($pId) {
        return $player->getUiData($pId);
      })
      ->toAssoc();
  }

  /**
   * Get current turn order according to first player variable
   */
  public static function getTurnOrder($firstPlayer = null)
  {
    $firstPlayer = $firstPlayer ?? Globals::getFirstPlayer();
    $order = [];
    $p = $firstPlayer;
    do {
      $order[] = $p;
      $p = self::getNextId($p);
    } while ($p != $firstPlayer);
    return $order;
  }

  /**
   * This allow to change active player
   */
  public static function changeActive($pId)
  {
    Game::get()->gamestate->changeActivePlayer($pId);
  }

  /**
   * Sets player datas related to turn number $turn
   * @param array $player_ids
   * @param int $turn
   */
  public static function startTurn($player_ids,$turn)
  {
    foreach($player_ids as $player_id){
      $player = self::get($player_id);
      $player->startTurn($turn);
    }
  }
  
  /**
   * @param Player $player 
   * @param int $money 
   */
  public static function giveMoney($player,$money){
    $pId = $player->getId();
    //self::DB()->inc(['money' => $money], $pId);
    $player->giveResource($money,RESOURCE_TYPE_MONEY);
    Notifications::giveMoney($player,$money);
    Stats::inc("moneyReceived",$player,$money);
    Stats::inc("moneyLeft",$player,$money);
  }
  
  /**
   * @param Player $player 
   * @param int $money 
   */
  public static function spendMoney($player,$money){
    $pId = $player->getId();
    if($player->getMoney() < $money){
      //Should not happen
      throw new UnexpectedException(404,"Not enough money to spend");
    }
    //self::DB()->inc(['money' => 0-$money], $pId);
    $player->giveResource($money,RESOURCE_TYPE_MONEY,false);
    Notifications::spendMoney($player,0-$money);
    Stats::inc("moneySpent",$player,$money);
    Stats::inc("moneyLeft",$player,-$money);
  }
  
  /**
   * @param Player $player 
   * @param int $region 
   * @param int $amount 
   */
  public static function gainInfluence($player,$region,$amount){
    if($amount == 0) return;
    //TODO JSA influence depends on Scorpion clan patron
    $meeple = Meeples::getInfluenceMarker($player->getId(),$region);
    $currentInfluence = $meeple->getPosition(); 
    $newInfluence = min(NB_MAX_INLFUENCE,$currentInfluence + $amount);
    $meeple->setPosition($newInfluence);
    //TODO JSA earn bonus on track

    Notifications::gainInfluence($player,$region,$amount,$newInfluence,$meeple);
  }
  
  /**
   * @param Player $player 
   * @param int $amount 
   */
  public static function gainDivineFavor($player,$amount){
    if($amount == 0) return;
    $current = $player->getResource(RESOURCE_TYPE_SUN); 
    $max = $player->getResource(RESOURCE_TYPE_MOON); 
    $new = min($max,$current + $amount);
    $player->giveResource($new - $current,RESOURCE_TYPE_SUN);

  }
}

