<?php

namespace ROG\Core;

use ROG\Managers\Cards;
use ROG\Managers\Tiles;
use ROG\Models\BuildingTile;
use ROG\Models\ClanPatronCard;
use ROG\Models\CustomerCard;
use ROG\Models\MasteryCard;
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
   */
  public static function newPlayerColor($player)
  {
    self::notifyAll('newPlayerColor', '', [
      'player' => $player,
      'player_color' => $player->getColor(),
    ]);
  }
  
  /**
   * @param Player $player
   */
  public static function refillHand($player)
  {
    self::notifyAll('refillHand', clienttranslate('${player_name} decides to refill their hand now'), [
      'player' => $player,
    ]);
  }
  /**
   * @param Player $player
   * @param ClanPatronCard $card
   */
  public static function giveClanCardTo($player, $card)
  {
    self::notifyAll('giveClanCardTo', clienttranslate('${player_name} receives a new clan patron : ${patron_name} alias ${patron_ability} ( ${clan_name} )'), [
      'i18n' => [ 'patron_name','patron_ability','clan_name' ],
      'player' => $player,
      'card' => $card->getUiData(),
      'patron_name' => $card->getName(),
      'patron_ability' => $card->getAbilityName(),
      'clan_name' => $card->getClanName(),
    ]);
  }
  /**
   * @param Player $player
   * @param Card $card
   */
  public static function giveCardTo($player, $card)
  {
    self::notifyAll('giveCardToPublic', clienttranslate('${player_name} receives a new customer card'), [
      'player' => $player,
    ]);
    //Beware this is a private info !
    self::notify($player,'giveCardTo', '', [
      'player' => $player,
      'card' => $card->getUiData(),
    ]);
  }
  /**
   * @param Player $player
   * @param Card $card
   */
  public static function deliver($player, $card)
  { 
    self::notifyAll('deliver', clienttranslate('${player_name} delivers a customer card : ${customer_name}'), [
      'player' => $player,
      'card' => $card->getUiData(),
      'customer_name' => [
        'log'=> '${customer_type} ${region}',
        'args'=> [
          'i18n' => ['customer_type'],
          'customer_type' => $card->getTitle(),
          'region' => $card->getRegion(),
        ]
      ],
    ]);
  }
  
  /**
   * @param Player $player
   * @param Card $card
   */
  public static function discard($player, $card)
  { 
    self::notifyAll('discardPublic', clienttranslate('${player_name} discards a customer card'), [
      'player' => $player,
    ]);
    //Beware this is a private info !
    self::notify($player,'discard', clienttranslate('You discard ${customer_name}'), [
      'player' => $player,
      'card' => $card->getUiData(),
      'customer_name' => [
        'log'=> '${customer_type} ${region}',
        'args'=> [
          'i18n' => ['customer_type'],
          'customer_type' => $card->getTitle(),
          'region' => $card->getRegion(),
        ]
      ],
    ]);
  }
  
  /**
   * @param Player $player
   * @param int $n
   * @param int $resourceType
   */
  public static function giveResource($player,$n, $resourceType)
  {
    $notif = 'giveResource';
    $msg = clienttranslate('${player_name} receives ${n} ${res_icon}');
    if($n < 0){
      $msg = clienttranslate('${player_name} spends ${n} ${res_icon}');
      $notif = 'spendResource';
      $n = -$n;
    }
    self::notifyAll($notif, $msg, [
      'player' => $player,
      'n' => $n,
      'preserve'=>['res_type'],
      'res_icon' => RESOURCES[$resourceType],
      'res_type' => $resourceType,
    ]);
  }
  
  /**
   * @param Player $player
   * @param int $type
   * @param string $typeText (optional)
   */
  public static function addBonus($player,$type,$typeText = '')
  {
    $msg = clienttranslate('${player_name} receives a bonus decision ${bonus_icon}${bonus_text}');
    self::notifyAll('addBonus', $msg, [
      'i18n'=>['bonus_text'],
      'player' => $player,
      'bonus_icon' => $type,
      'bonus_text' => $typeText,
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
      'preserve'=>['die_value'],
      'die_value' => $die_face,
    ]);
  }
  /**
   * @param Player $player
   * @param int $die_face
   */
  public static function setDieFace($player,$die_face)
  {
    self::notifyAll('setDie', clienttranslate('${player_name} sets their die to ${die_face}'), [
      'player' => $player,
      'die_face' => $die_face,
      'preserve'=>['die_value'],
      'die_value' => $die_face,
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
   * @param Meeple $ship
   * @param int $riverSpace
   */
  public static function sail($player,$ship,$riverSpace)
  {
    self::notifyAll('sail', clienttranslate('${player_name} sails and moves a ${ship_type} ship to river space #${n}'), [
      'player' => $player,
      'ship' => $ship->getUiData(),
      'ship_type' => $ship->getType(),
      'n' => $riverSpace,
    ]);
  }
  
  public static function checkVisitorRewards()
  {
    self::notifyAll('checkVRewards', clienttranslate('Checking visitor rewards...'), [
    ]);
  }
  public static function checkOwnerRewards()
  {
    self::notifyAll('checkORewards', clienttranslate('Checking owner rewards...'), [
    ]);
  }
  public static function checkRoyalShipAbilities()
  {
    self::notifyAll('checkRoyal', clienttranslate('Checking royal ship abilities...'), [
    ]);
  }
  /**
   * @param Player $player
   * @param Meeple $ship
   */
  public static function reachRiverEnd($player,$ship)
  {
    self::notifyAll('reachRiverEnd', clienttranslate('${player_name} ship completed its journey by reaching the end of the river'), [
      'player' => $player,
    ]);
  }
    /**
   * @param Player $player
   */
  public static function newClanMarkers($player)
  {
    $msg = clienttranslate('${player_name} places their clan markers');
    self::notifyAll('newClanMarkers', $msg, [
      'player' => $player,
    ]);
  }
    /**
   * @param Player $player
   * @param Meeple $meeple
   */
  public static function newClanMarker($player,$meeple)
  {
    //$msg = clienttranslate('${player_name} places a new clan marker');
    $msg = '';//avoid spoiling notifs
    self::notifyAll('newClanMarker', $msg, [
      'player' => $player,
      'meeple' => $meeple->getUiData(),
    ]);
  }
  /**
   * @param Player $player
   * @param Meeple $meeple
   */
  public static function newBoat($player,$meeple)
  {
    self::notifyAll('newBoat', clienttranslate('${player_name} places a ship on the river (at space #${space})'), [
      'player' => $player,
      'meeple' => $meeple->getUiData(),
      'space' => $meeple->getPosition(),
    ]);
  }
  /**
   * @param Player $player
   * @param Meeple $meeple
   */
  public static function upgradeShip($player,$meeple)
  {
    self::notifyAll('upgradeShip', clienttranslate('${player_name} upgrade a standard ship (at space #${space})'), [
      'player' => $player,
      'meeple' => $meeple->getUiData(),
      'space' => $meeple->getPosition(),
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
    $message = clienttranslate('${player_name} gets ${n} influence in region #${region}${region_icon} and reaches ${influence}');
    self::notifyAll('gainInfluence', $message, [
      'player' => $player,
      'region_icon' => $region,
      'region' => $region,
      'n' => $amount,
      'n2' => $influence,
      'preserve'=>['n2','region'],
      'influence' => $influence,
      'meeple' => $meeple->getUiData(),
    ]);
  }
  
  /**
   * @param Player $player
   * @param int $points
   * @param string $msg (optional)
   */
  public static function addPoints($player,$points, $msg = null){
    if(!isset($msg)) $msg = clienttranslate('${player_name} scores ${n} ${points}');
    self::notifyAll('addPoints',$msg,[ 
        'player' => $player,
        'n' => $points,
        'points' => 'points',
      ],
    );
  }
  /**
   * @param Player $player
   * @param int $points
   * @param MasteryCard $masteryCard
   * @param Meeple $meeple
   */
  public static function claimMasteryCard($player,$points,$masteryCard, $meeple){
    $msg = clienttranslate('${player_name} scores ${n} ${points} for claiming ${mastery_name}');
    self::notifyAll('claimMC',$msg,[ 
        'i18n' => ['mastery_name'],
        'player' => $player,
        'n' => $points,
        'points' => 'points',
        'mastery_name' => $masteryCard->getTitle(),
        //'meeple_id' => $meeple->getId(),
        'tile_id' => $masteryCard->getId(),
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param int $points
   * @param int $nbDeliveries
   */
  public static function scoreDeliveries($player,$points,$nbDeliveries){
    $msg = clienttranslate('${player_name} scores ${n} ${points} with ${n2} deliveries');
    self::notifyAll('scoreDeliveries',$msg,[ 
        'player' => $player,
        'n' => $points,
        'points' => 'points',
        'n2' => $nbDeliveries,
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param ScoringTile $scoringTile
   * @param int $region
   * @param int $points
   * @param int $playerInfluence
   */
  public static function scoreInfluence($player,$scoringTile,$region,$points,$playerInfluence){
    $msg = clienttranslate('${player_name} scores ${n} ${points} with ${n2} influence in region #${region}${region_icon}');
    self::notifyAll('scoreInfluence',$msg,[ 
        'player' => $player,
        'n' => $points,
        'points' => 'points',
        'n2' => $playerInfluence,
        'region' => $region,
        'region_icon' => $region,
        'tile_id' => $scoringTile->getId(),
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param ScoringTile $scoringTile
   * @param int $region
   * @param int $points
   */
  public static function scoreElder($player,$scoringTile,$region,$points){
    $msg = clienttranslate('${player_name} scores ${n} ${points} with an elder in region #${region}${region_icon}');
    self::notifyAll('scoreElder',$msg,[ 
        'player' => $player,
        'n' => $points,
        'points' => 'points',
        'region' => $region,
        'region_icon' => $region,
        'tile_id' => $scoringTile->getId(),
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param int $nbArtisans
   * @param int $nbResources
   * @param int $points
   */
  public static function scoreArtisans($player,$nbArtisans,$nbResources,$points){
    $msg = clienttranslate('${player_name} scores ${n} ${points} with ${n2} artisans and ${n3} remaining trade goods');
    self::notifyAll('scoreArtisans',$msg,[ 
        'player' => $player,
        'n' => $points,
        'points' => 'points',
        'n2' => $nbArtisans,
        'n3' => $nbResources,
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param int $nbMerchants
   * @param int $money
   * @param int $points
   */
  public static function scoreMerchants($player,$nbMerchants,$money,$points){
    $msg = clienttranslate('${player_name} scores ${n} ${points} with ${n2} merchants and ${n3} remaining Koku');
    self::notifyAll('scoreMerchants',$msg,[ 
        'player' => $player,
        'n' => $points,
        'points' => 'points',
        'n2' => $nbMerchants,
        'n3' => $money,
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param CustomerCard $card
   * @param int $points
   */
  public static function scoreCustomer($player,$card,$points){
    $msg = clienttranslate('${player_name} scores ${n} ${points} with ${customer_name}');
    self::notifyAll('scoreCustomer',$msg,[ 
        'player' => $player,
        'n' => $points,
        'points' => 'points',
        'card_id' => $card->getId(),
        'customer_name' => [
          'log'=> '${customer_type} ${region}',
          'args'=> [
            'i18n' => ['customer_type'],
            'customer_type' => $card->getTitle(),
            'region' => $card->getRegion(),
          ]
        ],
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param int $points
   * @param ClanPatronCard $card
   */
  public static function scorePatron($player,$points,$card){
    $msg = clienttranslate('${player_name} scores ${n} ${points} with ${patron_name}');
    self::notifyAll('scorePatron',$msg,[ 
        'player' => $player,
        'n' => $points,
        'points' => 'points',
        'patron_name' => $card->getName(),
        'card_id' => $card->getId(),
      ],
    );
  }
  /**
   * @param BuildingTile $buildingTile
   * @param BuildingTile $nextEra1Card
   * @param BuildingTile $nextEra2Card
   */
  public static function refillBuildingRow($buildingTile,$nextEra1Card,$nextEra2Card){
    $msg = clienttranslate('The building row is refilled from the building board');
    self::notifyAll('refillBuildingRow',$msg,[ 
        'tile' => $buildingTile->getUiData(),
        'era1' => isset($nextEra1Card) ? $nextEra1Card->getUiData() : null,
        'era2' => isset($nextEra2Card) ? $nextEra2Card->getUiData() : null,
        'deckSize' => [
          'era1' => Tiles::countInLocation(TILE_LOCATION_BUILDING_DECK_ERA_1),
          'era2' => Tiles::countInLocation(TILE_LOCATION_BUILDING_DECK_ERA_2),
        ],
      ],
    );
  }
  
  /**
   * @param BuildingTile $buildingTile
   */
  public static function discardBuildingRow($buildingTile){
    $msg = clienttranslate('The last tile of the building row is discarded : ${building_tile}');
    self::notifyAll('discardBuildingRow',$msg,[ 
        'preserve'=>['tile'],
        'building_tile' => $buildingTile->getType(),
        'tile' => $buildingTile->getUiData(),
      ],
    );
  }
  
  /**
   * @param array $slidedTiles
   */
  public static function slideBuildingRow($slidedTiles){
    $msg = '';
    $buildingTilesUi = [];
    foreach($slidedTiles as $fromPosition => $buildingTile){
      $buildingTilesUi[] = [
        'from' => $fromPosition,
        'tile' => $buildingTile->getUiData(),
      ];
    }
    self::notifyAll('slideBuildingRow',$msg,[ 
        'tiles' => $buildingTilesUi,
      ],
    );
  }
  
  /**
   * @param Player $player
   */
  public static function endTurn($player)
  {
    self::notifyAll('endTurn', clienttranslate('End of ${player_name} turn'), [
      'player' => $player,
    ]);
  }
  /**
   * @param int $era
   */
  public static function emperorVisit($era)
  {
    self::notifyAll('emperorVisit', clienttranslate('Emperor\'s Visit has been triggered !'), [
      'era' => $era,
    ]);
  }
  /**
   * @param Player $player
   */
  public static function triggerLastTurn($player)
  {
    self::notifyAll('triggerLastTurn', clienttranslate('Start of the end of the game is triggered by ${player_name} !'), [
      'player' => $player,
    ]);
  }
  /**
   */
  public static function triggerEnd()
  {
    self::notifyAll('triggerEnd', clienttranslate('End of the game !'), [
    ]);
  }
  
  /**
   */
  public static function computeFinalScore()
  {
    self::notifyAll('computeFinalScore', clienttranslate('Computing final scoring...'), [
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
      //If we rollback Emperor visit :
      'era' => $datas['era'],
      'deckSize' => $datas['deckSize'],
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
  
  /**
   * @param Player $player
   * @param array $notifIds
   */
  public static function clearTurn($player, $notifIds)
  {
    self::notifyAll('clearTurn', '', [
      'player' => $player,
      'notifIds' => $notifIds,
    ]);
  }
  
  /**
   * @param Player $player
   * @param int $stepId
   */
  public static function undoStep($player, $stepId)
  {
    self::notifyAll('undoStep', clienttranslate('${player_name} undoes their action'), [
      'player' => $player,
    ]);
  }
  /**
   * @param Player $player
   */
  public static function restartTurn($player)
  {
    self::notifyAll('restartTurn', clienttranslate('${player_name} restarts their turn'), [
      'player' => $player,
    ]);
  }

}
