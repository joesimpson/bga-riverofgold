<?php

namespace ROG\Core;

use ROG\Managers\Cards;
use ROG\Models\BuildingTile;
use ROG\Models\Meeple;
use ROG\Models\Player;

class Notifications
{ 
  
  /**
   * @param Player $player
   * @param int $money
   */
  public static function giveMoney($player, $money)
  {
    self::notifyAll('giveMoney', clienttranslate('${player_name} receives ${n} ${koku}'), [
      'player' => $player,
      'n' => $money,
      'koku' => 'Koku',
    ]);
  }
  /**
   * @param Player $player
   * @param int $money
   */
  public static function spendMoney($player, $money)
  {
    self::notifyAll('spendMoney', clienttranslate('${player_name} spends ${n} ${koku}'), [
      'player' => $player,
      'n' => $money,
      'koku' => 'Koku',
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
    self::notifyAll('giveResource', clienttranslate('${player_name} receives ${n} ${res_icon}'), [
      'player' => $player,
      'n' => $n,
      'preserve'=>['res_type'],
      'res_icon' => RESOURCES[$resourceType],
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
      'preserve'=>['tile'],
      'tile' => $tile->getUiData(),
      'building_tile' => $tile->getType(),
      'from' => $previousPosition,
    ]);
  }
    /**
   * @param Player $player
   * @param Meeple $meeple
   */
  public static function newClanMarker($player,$meeple)
  {
    self::notifyAll('newClanMarker', clienttranslate('${player_name} places a new clan marker'), [
      'player' => $player,
      'meeple' => $meeple->getUiData(),
    ]);
  }

  /**
   * @param Player $player 
   * @param int $region 
   * @param int $amount 
   * @param int $influence 
   * @param Meeple $meeple 
   */
  public static function gainInfluence($player,$region,$amount,$influence,$meeple)
  {
    $message = clienttranslate('${player_name} gets ${n} influence in region ${region} and reaches ${influence}');
    self::notifyAll('gainInfluence', $message, [
      'player' => $player,
      'region' => $region,
      'n' => $amount,
      'n2' => $influence,
      'preserve'=>['n2'],
      'influence' => $influence,
      'meeple' => $meeple->getUiData(),
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
  
  /************************************
   **** UPDATES after confirm/undo ****
   ***********************************/
  
  public static function refreshUI($datas)
  {
    // Keep only the things from getAllDatas that matters
    $players = $datas['players'];
    $gameDatas = [
      'players' => $datas['players'],
      'cards' => $datas['cards'],
      'meeples' => $datas['meeples'],
      'tiles' => $datas['tiles'],
    ];

    foreach ($gameDatas['cards'] as $index=> &$card) {
      // Hide hand !
      if( CARD_LOCATION_HAND == $card['location']) unset($gameDatas['cards'][$index]);
    }

    self::notifyAll('refreshUI', '', [
      'datas' => $gameDatas,
    ]);
    
    Cards::refreshHands($players);
  }
  /**
   * @param int $playerId
   * @param Collection $cards
   */
  public static function refreshHand($playerId,$cards)
  {
    self::notify($playerId, 'refreshHand', '', [
      'hand' => $cards->ui(),
    ]);
  }
  public static function clearTurn($player, $notifIds)
  {
    self::notifyAll('clearTurn', '', [
      'player' => $player,
      'notifIds' => $notifIds,
    ]);
  }
  public static function undoStep($player, $stepId)
  {
    self::notifyAll('undoStep', clienttranslate('${player_name} undoes their action'), [
      'player' => $player,
    ]);
  }
  public static function restartTurn($player)
  {
    self::notifyAll('restartTurn', clienttranslate('${player_name} restarts their turn'), [
      'player' => $player,
    ]);
  }

}
