<?php

namespace ROG\Core;

use ROG\Helpers\Collection;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Tiles;
use ROG\Models\AutomaActionCard;
use ROG\Models\AutomaPlayer;
use ROG\Models\BuildingTile;
use ROG\Models\ClanPatronCard;
use ROG\Models\CustomerCard;
use ROG\Models\MasteryCard;
use ROG\Models\Meeple;
use ROG\Models\Player;

class Notifications
{ 
  
  public static function automaColor(int $clan, string $color, int $automa_id)
  {
    self::notifyAll('automaColor',  '', [
      'automa_name' => Utils::getAutomaName(),
      'automa_color' => $color,
      'automa_clan' => $clan,
      'automa_id' => $automa_id,
      'i18n' => ['automa_name'],
      'preserve' => ['automa_color','automa_clan'],
    ]);
  }

  public static function initCustomersDeck(array $customerTypes)
  {
    $customerNames = [];
    foreach($customerTypes as $type) {
      $customerNames[] = Cards::getCustomerTypeName($type);
    }
    self::notifyAll('initCustomersDeck', clienttranslate('Customers deck is shuffled with ${customers_icons}'), [
      'customers_types' => $customerTypes,
      'customers_icons' => $customerNames,
      'preserve'=>['customers_types',],
      'i18n' => ['customers_icons'],
      'separator' => ['customers_icons' => ', '],
    ]);
  }
  
  public static function initSeishinDeck(Collection $cards)
  {
    $cardsNames = $cards->map(function($card) {return $card->getTitle();})->toArray();

    $level = Globals::getOptionSeishin();
    self::notifyAll('initSeishinDeck',  clienttranslate('Level ${n} : ${automa_name} will play with ${x} action cards : ${cards_list}'), [
      'automa_name' => Utils::getAutomaName(),
      'automa_color' => Utils::getAutomaColor(),
      'i18n' => ['automa_name','cards_list'],
      'preserve' => ['automa_color','cards'],
      'n' => Utils::getAutomaDifficultyName($level),
      'x' => $cards->count(),
      'cards' => $cards->ui(),
      'cards_list' => $cardsNames,
      'separator' => ['cards_list' => ','],
    ]);
  }
  /**
   * @param Player $player
   * @param int $money
   */
  public static function giveMoney($player, $money)
  {
    self::notifyAll('giveMoney', clienttranslate('${player_name} receives ${n} ${koku}'), [
      'player' => $player,
      'n' => $money,
      'koku' => clienttranslate('Koku'),
      'i18n' => ['koku'],
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
      'koku' => clienttranslate('Koku'),
      'i18n' => ['koku'],
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
      'player_clan' => $player->getClan(),
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
   * @param Collection $cards
   */
  public static function draftCards($cards)
  {
    self::notifyAll('draftCards', '', [
      'cards' => $cards->ui(),
    ]);
  }
  
  /**
   * @param Player $player
   * @param Collection $cards
   */
  public static function draftPlayerCards($player,$cards)
  {
    self::notify($player,'draftPlayerCards', '', [
      'player' => $player,
      'cards' => $cards->ui(),
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
  public static function giveScenarioCard($player, $card)
  {
    self::notifyAll('giveScenarioCard', clienttranslate('${player_name} will play with ${clan_name} scenario ${scenario_name}'), [
      'i18n' => [ 'scenario_name','clan_name' ],
      'player' => $player,
      'card' => $card->getUiData(),
      'scenario_name' => $card->getName(),
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
    if($player instanceof AutomaPlayer){
      return;
    }
    self::notify($player,'giveCardTo', '', [
      'player' => $player,
      'card' => $card->getUiData(),
    ]);
  }
  
  /**
   * @param Player $player
   * @param int $missingNb
   */
  public static function missingCards($player,$missingNb)
  {
    self::notifyAll('missingCards', clienttranslate('${player_name} cannot draw more cards (${n} missing)'), [
      'player' => $player,
      'n' => $missingNb,
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
      'preserve' => ['card'],
    ]);
  }
  
  public static function deliverHidden(Player $player, CustomerCard $card)
  { 
    self::notifyAll('deliverHidden', clienttranslate('${player_name} delivers a customer card : ${customer_name}'), [
      'player' => $player,
      'customer_name' => '???',
      //'card_location' => $card->getLocation(),
      'card_pId' => $card->getPId(),
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
  
  public static function reshuffleDeck(int $deckSize, int $discardSize)
  { 
    self::notifyAll('reshuffleDeck', clienttranslate('Customers\'s deck is reshuffled'), [
      'deckSize' => $deckSize,
      'discardSize' => $discardSize,
      'preserve' => ['deckSize','discardSize'],
    ]);
  }
  
  public static function reshuffleAutomaActionDeck(AutomaPlayer $player, int $deck_size, int $discardSize)
  {
    $msg = clienttranslate('${player_name} action deck is reshuffled');

    self::notifyAll('reshuffleAutomaActionDeck', $msg, [
      'deckSize' => $deck_size,
      'discardSize' => $discardSize,
      'player' => $player,
      'preserve' => ['deckSize','discardSize'],
    ]);
  }
  
  public static function giveActionCardToAutoma(AutomaPlayer $player, AutomaActionCard $card)
  {
    $message = clienttranslate('${player_name} draws the action card ${action_card_name}${card_icon} to play with ${die_face}');
    $die_face = $player->getDie();
    $cardName = $card->getTitle();
    self::notifyAll("giveActionCardToAutoma", $message, [
      'player' => $player,
      'card' => $card->getUiData(),
      'card_icon' => '',
      'action_card_name' => $cardName,
      'die_face' => $die_face,
      'die_value' => $die_face,
      'preserve'=>['card', 'die_value' ],
      'i18n'=>['action_card_name',  ],
    ]);
  }
  /**
   * @param Player $player
   * @param int $n
   * @param int $resourceType
   * @param BuildingTile $fromTile (Optional)
   * @param int $fromShoreSpace (Optional)
   */
  public static function giveResource($player,$n, $resourceType, $fromTile = null, $fromShoreSpace = null)
  {
    $notif = 'giveResource';
    $msg = clienttranslate('${player_name} receives ${n} ${res_icon}');
    if($n < 0){
      $msg = clienttranslate('${player_name} spends ${n} ${res_icon}');
      $notif = 'spendResource';
      $n = -$n;
    }
    $tile_id = isset($fromTile) ? $fromTile->getId() : null;
    $shore_space_pos = isset($fromShoreSpace) ? $fromShoreSpace : null;
    self::notifyAll($notif, $msg, [
      'player' => $player,
      'n' => $n,
      'preserve'=>['res_type','tile_id'],
      'res_icon' => Utils::resourceName($resourceType),
      'res_type' => $resourceType,
      'tile_id' => $tile_id,
      'shore_space_pos' => $shore_space_pos,
      'i18n' => ['res_icon'],
    ]);
  }
  
  /**
   * @param Player $player
   * @param int $type
   * @param string $typeText (optional)
   */
  public static function addBonus($player,$type,$typeText = '',?int $amount = null)
  {
    $msg = clienttranslate('${player_name} receives a bonus decision ${bonus_icon}${bonus_text}');
    self::notifyAll('addBonus', $msg, [
      'i18n'=>['bonus_text'],
      'player' => $player,
      'bonus_icon' => $type,
      'bonus_text' => $typeText,
      'n' => $amount,
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
   * @param string $previousLocation
   */
  public static function build(Player $player,BuildingTile $tile,int $previousPosition,string $previousLocation)
  {
    self::notifyAll('build', clienttranslate('${player_name} builds ${building_tile}'), [
      'player' => $player,
      'preserve'=>['tile','from','fromLoc'],
      'tile' => $tile->getUiData(),
      'building_tile' => $tile->getType(),
      'from' => $previousPosition,
      'fromLoc' => $previousLocation,
    ]);
  }
  
  public static function moveBuilding(Player $player,BuildingTile $tile,int $previousPosition,string $previousLocation)
  {
    self::notifyAll('moveBuilding', clienttranslate('${player_name} moves ${building_tile}'), [
      'player' => $player,
      'preserve'=>['tile','from','fromLoc'],
      'tile' => $tile->getUiData(),
      'building_tile' => $tile->getType(),
      'from' => $previousPosition,
      'fromLoc' => $previousLocation,
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
  
  public static function swapBoats(Player $player, Meeple $ship1, Player $player2,Meeple $ship2)
  {
    $sourcePosition = $ship2->getPosition();
    $destPosition = $ship1->getPosition();
    self::notifyAll('swapBoats', clienttranslate('${player_name} swaps a ${player_name2} ship at river space #${n1} with ${player_name3} ship at river space #${n2}'), [
      'player' => $player,
      'player2' => $player,
      'player3' => $player2,
      'ship1' => $ship1->getUiData(),
      'ship2' => $ship2->getUiData(),
      'n1' => $sourcePosition,
      'n2' => $destPosition,
      'preserve' => ['ship1','ship2'],
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
  public static function skipOpponentOwnerRewards()
  {
    self::notifyAll('skipOORewards', clienttranslate('Skipping opponents\'s owner rewards...'), [
    ]);
  }
  public static function buildingOwnerRewards(Player $player,BuildingTile $tile)
  {
    self::notifyAll('buildingOwnerRewards', clienttranslate('${player_name} select owner rewards from tile ${building_tile}'), [
      'player' => $player,
      'preserve'=>['tile'],
      'tile' => $tile->getUiData(),
      'building_tile' => $tile->getType(),
    ]);
  }
  
  public static function buildingVisitorRewards(Player $player,BuildingTile $tile)
  {
    self::notifyAll('buildingVisitorRewards', clienttranslate('${player_name} select visitor rewards from tile ${building_tile}'), [
      'player' => $player,
      'preserve'=>['tile'],
      'tile' => $tile->getUiData(),
      'building_tile' => $tile->getType(),
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
   * @param array $meeples
   */
  public static function influenceClanMarkers($player,$meeples)
  {
    $msg = '';
    $meeplesCollection = new Collection($meeples);
    self::notifyAll('influenceClanMarkers', $msg, [
      'player' => $player,
      'meeples' => $meeplesCollection->ui(),
    ]);
  }
    /**
   * @param Player $player
   * @param Meeple $meeple
   * @param BuildingTile $toTile (optional)
   * @param int $increaseBuildingsCounter (Optional) default 1
   */
  public static function newClanMarker($player,$meeple,$toTile = null, $increaseBuildingsCounter=1)
  {
    //$msg = clienttranslate('${player_name} places a new clan marker');
    $msg = '';//avoid spoiling notifs

    $buildingType = (isset($toTile)) ? $toTile->getBuildingType() : null;
    self::notifyAll('newClanMarker', $msg, [
      'player' => $player,
      'meeple' => $meeple->getUiData(),
      'buildingType' => $buildingType,
      'inc' => (isset($buildingType)) ? $increaseBuildingsCounter : 0,
    ]);
  }
  
  public static function newLionMarker(
    Player $player,
    Meeple $meeple,
  )
  {
    $msg = clienttranslate('${player_name} places a ${lion_marker} marker on a shore space');

    self::notifyAll('newClanMarker', $msg, [
      'player' => $player,
      'meeple' => $meeple->getUiData(),
      'lion_marker' => clienttranslate('Lady of Lions'),
      'preserve' => ['meeple'],
      'i18' => ['lion_marker'],
    ]);
  }

  public static function removeClanMarker(Player $player, Meeple $meeple,)
  {
    $msg = '';//avoid spoiling notifs
    self::notifyAll('removeClanMarker', $msg, [
      'player' => $player,
      'meeple' => $meeple->getUiData(),
    ]);
  }
  
  public static function newBuildingMarkersWithPatron(Player $player, array $markers, ?ClanPatronCard $playerPatron,)
  {
    $msg = clienttranslate('');
    $patron_name = '';
    if(isset($playerPatron)){
      $patron_name = $playerPatron->getName();
      $msg = clienttranslate('${player_name} places ${n} clan markers on buildings with ${patron_name} ability');
    }
    self::notifyAll('newBuildingMarkersWithPatron', $msg, [
      'player' => $player,
      'n' => count($markers),
      'patron_name' => $patron_name,
      'i18n' => [ 'patron_name' ],
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
   * @param ClanPatronCard $playerPatron (optional) clan patron that leads to this gain
   */
  public static function gainInfluence($player,$region,$amount,$influence,$meeple,$playerPatron = null)
  {
    $message = clienttranslate('${player_name} gets ${n} influence in region #${region}${region_icon} and reaches ${influence}');
    $patron_name = '';
    if(isset($playerPatron)){
      $patron_name = $playerPatron->getName();
      $message = clienttranslate('${player_name} gets ${n} influence in region #${region}${region_icon} and reaches ${influence} with ${patron_name} ability');
    }
    self::notifyAll('gainInfluence', $message, [
      'i18n' => [ 'patron_name' ],
      'player' => $player,
      'region_icon' => '',
      'region' => $region,
      'n' => $amount,
      'n2' => $influence,
      'preserve'=>['n2','region'],
      'influence' => $influence,
      'meeple' => $meeple->getUiData(),
      'patron_name' => $patron_name,
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
        'points' => clienttranslate('Points'),
        'i18n' => ['points'],
      ],
    );
  }
  /**
   * @param Player $player
   * @param int $points
   * @param MasteryCard $masteryCard
   * @param Meeple $meeple
   */
  public static function claimMasteryCard(Player $player,int $points,MasteryCard $masteryCard, $meeple){
    $msg = clienttranslate('${player_name} scores ${n} ${points} for claiming ${mastery_name}');
    self::notifyAll('claimMC',$msg,[ 
        'i18n' => ['mastery_name', 'points'],
        'player' => $player,
        'n' => $points,
        'points' => clienttranslate('Points'),
        'mastery_name' => $masteryCard->getTitle(),
        //'meeple_id' => $meeple->getId(),
        'tile_id' => $masteryCard->getId(),
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param Collection $masteries
   */
  public static function giveMasteriesTo(Player $player, Collection $masteries,){
    
    $sortedMasteries = $masteries->ui();
    usort($sortedMasteries, function ($a,$b)  {
      return $a["type"] <=>  $b["type"];
    });
    $masteriesNames = $masteries->map(function($masteryCard) {return $masteryCard->getTitle();})->toArray();

    $msg = clienttranslate('${player_name} reserves : ${masteries_names}');
    self::notifyAll('giveMasteriesTo',$msg,[ 
        'player' => $player,
        'masteries_names' => $masteriesNames,
        'tiles' => $sortedMasteries, //we need ids and datas because the tiles are not yet displayed
        
        'i18n' => ['masteries_names'],
        'separator' => ['masteries_names' => ', '],
        'preserve' => ['tiles'],
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
        'points' => clienttranslate('Points'),
        'i18n' => ['points'],
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
        'points' => clienttranslate('Points'),
        'i18n' => ['points'],
        'n2' => $playerInfluence,
        'region' => $region,
        'region_icon' => '',
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
        'points' => clienttranslate('Points'),
        'i18n' => ['points'],
        'region' => $region,
        'region_icon' => '',
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
        'points' => clienttranslate('Points'),
        'i18n' => ['points'],
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
        'points' => clienttranslate('Points'),
        'i18n' => ['points'],
        'n2' => $nbMerchants,
        'n3' => $money,
      ],
    );
  }
  
  public static function scoreMultiCustomers(Player $player,int $customer_type, int $nbCustomers,int $typeResource,int $amountResources,int $points){
    $msg = clienttranslate('${player_name} scores ${n} ${points} with ${n2} ${customer_name} and ${n3} remaining ${res_icon}');
    self::notifyAll('scoreMultiCustomers',$msg,[ 
        'player' => $player,
        'n' => $points,
        'points' => clienttranslate('Points'),
        'n2' => $nbCustomers,
        'n3' => $amountResources,
        'res_icon' => Utils::resourceName($typeResource),
        'res_type' => $typeResource,
        'customer_name' => Cards::getCustomerTypeName($customer_type),
        'customer_type' => $customer_type,
        'preserve' => ['res_type','customer_type'],
        'i18n' => ['customer_name', 'points','res_icon'],
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
        'points' => clienttranslate('Points'),
        'i18n' => ['points'],
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

  
  public static function playCustomerAbility(Player $player,CustomerCard $card){
    $msg = clienttranslate('${player_name} plays ${customer_name} ability');
    self::notifyAll('playCustomerAbility',$msg,[ 
        'player' => $player,
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
  public static function scorePatron(Player $player,int $points,ClanPatronCard $card){
    $msg = clienttranslate('${player_name} scores ${n} ${points} with ${patron_name}');
    self::notifyAll('scorePatron',$msg,[ 
        'i18n' => [ 'patron_name', 'points' ],
        'player' => $player,
        'n' => $points,
        'points' => clienttranslate('Points'),
        'patron_name' => $card->getName(),
        'card_id' => $card->getId(),
      ],
    );
  }
  
  public static function activePatron(Player $player,ClanPatronCard $card){
    $msg = clienttranslate('${player_name} activates ${patron_name} ability');
    self::notifyAll('activePatron',$msg,[ 
        'i18n' => [ 'patron_name' ],
        'player' => $player,
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

  public static function revealTopEraTiles(?BuildingTile $nextEra1Card,?BuildingTile $nextEra2Card){
    $msg = '';
    self::notifyAll('revealTopEraTiles',$msg,[ 
        'era1' => isset($nextEra1Card) ? $nextEra1Card->getUiData() : null,
        'era2' => isset($nextEra2Card) ? $nextEra2Card->getUiData() : null,
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
  
  public static function discardTiles(Collection $tiles, string|null $message){
    $msg = '';
    if(isset($msg)) $msg = $message;
    self::notifyAll('discardTiles',$msg,[ 
        'preserve'=>['tiles'],
        'tiles' => $tiles->uiAssocLight(),
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
   * @param BuildingTile $tile
   */
  public static function emperorReward($tile)
  {
    self::notifyAll('emperorReward', '', [
      'tile_space' => $tile->getPosition(),
    ]);
  }
  /**
   * In order to know it client side
   */
  public static function emperorVisitEnd()
  {
    self::notifyAll('emperorVisitEnd', clienttranslate('Emperor\'s Visit ends'), [
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
  
  /**
   */
  public static function endResourcesForCustomers(AutomaPlayer $player, int $level, array $resources )
  {
    /* Cannot use recursive args in i18n array...
    * https://studio.boardgamearena.com/bug?id=346
    $resourcesNames = array_values(array_map(function($r) {
        return [
          'log'=> clienttranslate('${n} ${element}'),
          'args'=> [ 
            'n' => $r['n'],
            'element' => $r['name'],
            'i18n' => ['element'],
          ],
        ];
      }, $resources));

    self::notifyAll('endResourcesForCustomers', clienttranslate('Level ${n} : ${player_name} will score customer endgame rewards with ${resources_list}'), [
      'player' => $player,
      'n' => Utils::getAutomaDifficultyName($level),
      'resources' => $resources,
      'resources_list' => $resourcesNames,
      'separator' => ['resources_list' => ','],
      'i18n' => ['resources_list'],
      'preserve' => ['resources'],

    ]);
    */
    self::notifyAll('endResourcesForCustomers', clienttranslate('Level ${n} : ${player_name} will score customer endgame rewards with ${n1} trade goods, ${n2} Koku, ${n3} divine favor'), [
      'player' => $player,
      'n' => Utils::getAutomaDifficultyName($level),
      'n1' => $resources[BONUS_TYPE_CHOICE]['n'],
      'n2' => $resources[RESOURCE_TYPE_MONEY]['n'],
      'n3' => $resources[RESOURCE_TYPE_SUN]['n'],

    ]);
  }
  
  public static function eliminateByScore(Player $player,int $scoreToBeat, int $playerScore )
  {
    self::notifyAll('eliminateByScore', clienttranslate('${player_name} failed to exceed ${n} ${points} (Final score : ${n2} ${points})'), [
      'player' => $player,
      'n' => $scoreToBeat,
      'n2' => $playerScore,
      'points' => clienttranslate('Points'),
      'i18n' => ['points'],
    ]);
  }
  
  public static function teamLoose()
  {
    self::notifyAll('teamLoose', clienttranslate('All players lose the game as a team'), [
    ]);
  }

  public static function teamWin(int $teamScore)
  {
    self::notifyAll('teamWin', clienttranslate('All players win the game as a team with a score of ${n} ${points}'), [
      'n' => $teamScore,
      'points' => clienttranslate('Points'),
      'i18n' => ['points'],
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
      //for playername_wrapper
      $data['player_color'] = $data['player']->getColor();
      if (!isset($data['preserve'])) {
        $data['preserve'] = [];
      }
      $data['preserve'][] = 'player_color';
      if($data['player'] instanceof AutomaPlayer){
        //it is not enough for now because framework won't translate player_names :(
        $data['i18n'][] = 'player_name';
      }

      unset($data['player']);
    }
    if (isset($data['player2'])) {
      $data['player_name2'] = $data['player2']->getName();
      $data['player_id2'] = $data['player2']->getId();
      //for playername_wrapper
      $data['player_color2'] = $data['player2']->getColor();
      if($data['player2'] instanceof AutomaPlayer){
        $data['i18n'][] = 'player_name2';
      }
      unset($data['player2']);
      $data['preserve'][] = 'player_color2';
      $data['preserve'][] = 'player_id2';
    }
    
    if (isset($data['player3'])) {
      $data['player_name3'] = $data['player3']->getName();
      $data['player_id3'] = $data['player3']->getId();
      //for playername_wrapper
      $data['player_color3'] = $data['player3']->getColor();
      if($data['player3'] instanceof AutomaPlayer){
        $data['i18n'][] = 'player_name3';
      }
      unset($data['player3']);
      $data['preserve'][] = 'player_color3';
      $data['preserve'][] = 'player_id3';
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
