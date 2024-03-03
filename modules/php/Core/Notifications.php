<?php

namespace ROG\Core;

use ROG\Models\BuildingTile;

class Notifications
{ 
  
  /**
   * @param Player $player
   * @param int $money
   */
  public static function giveMoney($player, $money)
  {
    self::notifyAll('giveMoney', clienttranslate('${player_name} receives ${n} Koku'), [
      'player' => $player,
      'n' => $money,
    ]);
  }
  /**
   * @param Player $player
   * @param int $money
   */
  public static function spendMoney($player, $money)
  {
    self::notifyAll('spendMoney', clienttranslate('${player_name} spends ${n} Koku'), [
      'player' => $player,
      'n' => $money,
    ]);
  }
  
  /**
   * @param Player $player
   * @param Card $card
   */
  public static function giveCardTo($player, $card)
  {
    //Beware this is a private info !
    self::notify($player,'giveCardTo', '', [
      'player' => $player,
      'card' => $card->getUiData(),
    ]);
    self::notifyAll('giveCardToPublic', clienttranslate('${player_name} receives a new customer card'), [
      'player' => $player,
    ]);
  }
  
  /**
   * @param Player $player
   * @param int $n
   * @param int $resourceType
   */
  public static function giveResource($player,$n, $resourceType)
  {
    self::notifyAll('giveResource', clienttranslate('${player_name} receives ${n} ${res_type}'), [
      'player' => $player,
      'n' => $n,
      'res_type' => $resourceType,
    ]);
  }
  /**
   * @param Player $player
   * @param int $die_face
   */
  public static function rollDie($player,$die_face)
  {
    self::notifyAll('rollDie', clienttranslate('${player_name} rolls a die and gets ${die_face}'), [
      'player' => $player,
      'die_face' => $die_face,
    ]);
  }
  /**
   * @param Player $player
   * @param BuildingTile $tile
   * @param int $previousPosition
   */
  public static function build($player,$tile,$previousPosition)
  {
    //TODO JSA notify region for master engineer?
    self::notifyAll('build', clienttranslate('${player_name} builds ${building_tile}'), [
      'player' => $player,
      'tile' => $tile->getUiData(),
      'building_tile' => $tile->getType(),
      'from' => $previousPosition,
    ]);
  }

  /*************************
   **** GENERIC METHODS ****
   *************************/
  protected static function notifyAll($name, $msg, $data)
  {
    self::updateArgs($data);
    Game::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($player, $name, $msg, $data)
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::updateArgs($data);
    Game::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  public static function message($txt, $args = [])
  {
    self::notifyAll('message', $txt, $args);
  }

  public static function messageTo($player, $txt, $args = [])
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::notify($pId, 'message', $txt, $args);
  }

  /**
   *  Empty notif to send after an action, to let framework works & refresh ui
   * (Usually not needed if we send another notif or if we change state of a player)
   * */
  public static function emptyNotif(){
    self::notifyAll('e','',[],);
  }
  /*********************
   **** UPDATE ARGS ****
   *********************/

  /*
   * Automatically adds some standard field about player and/or card
   */
  protected static function updateArgs(&$data)
  {
    if (isset($data['player'])) {
      $data['player_name'] = $data['player']->getName();
      $data['player_id'] = $data['player']->getId();
      unset($data['player']);
    }

    if (isset($data['player2'])) {
      $data['player_name2'] = $data['player2']->getName();
      $data['player_id2'] = $data['player2']->getId();
      unset($data['player2']);
    }
    
    if (isset($data['player3'])) {
      $data['player_name3'] = $data['player3']->getName();
      $data['player_id3'] = $data['player3']->getId();
      unset($data['player3']);
    }
  }

}
