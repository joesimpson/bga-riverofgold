<?php
namespace ROG;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Helpers\Log;
use ROG\Helpers\QueryBuilder;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\CustomerCard;

/**
 * Debugging functions to be called in chat window in BGA Studio
 */
trait DebugTrait
{
   /**
   * STUDIO : Get the database matching a bug report (when not empty)
   */
  public function loadBugReportSQL(int $reportId, array $studioPlayersIds): void {
    $this->trace("loadBugReportSQL($reportId, ".json_encode($studioPlayersIds));
    $players = $this->getObjectListFromDb('SELECT player_id FROM player', true);
  
    $sql = [];
    //This table is modified with boilerplate
    $sql[] = "ALTER TABLE `gamelog` ADD `cancel` TINYINT(1) NOT NULL DEFAULT 0;";

    // Change for your game
    // We are setting the current state to match the start of a player's turn if it's already game over
    $state = ST_PLAYER_TURN;
    $sql[] = "UPDATE global SET global_value=$state WHERE global_id=1 AND global_value=99";
    foreach ($players as $index => $pId) {
      $studioPlayer = $studioPlayersIds[$index];
  
      // All games can keep this SQL
      $sql[] = "UPDATE player SET player_id=$studioPlayer WHERE player_id=$pId";
      $sql[] = "UPDATE global SET global_value=$studioPlayer WHERE global_value=$pId";
      $sql[] = "UPDATE stats SET stats_player_id=$studioPlayer WHERE stats_player_id=$pId";
  
      // Add game-specific SQL update the tables for your game
      $sql[] = "UPDATE meeples SET player_id=$studioPlayer WHERE player_id = $pId";
      $sql[] = "UPDATE cards SET player_id=$studioPlayer WHERE player_id = $pId";
      $sql[] = "UPDATE global_variables SET `value` = REPLACE(`value`,'$pId','$studioPlayer')";
      
      $sql[] = "UPDATE user_preferences SET player_id=$studioPlayer WHERE player_id = $pId";
    }
  
    foreach ($sql as $q) {
      $this->DbQuery($q);
    }
  
    $this->reloadPlayersBasicInfos();
  }

  /**
   * Function to call to regenerate JSON from PHP 
   */
  function debugJSON(){
    include dirname(__FILE__) . '/gameoptions.inc.php';

    $customOptions = $game_options;//READ from module file
    $json = json_encode($customOptions, JSON_PRETTY_PRINT);
    //Formatting options as json -> copy the DOM of this log : \n
    Notifications::message("$json",['json' => $json]);
    
    $customOptions = $game_preferences;
    $json = json_encode($customOptions, JSON_PRETTY_PRINT);
    //Formatting prefs as json -> copy the DOM of this log : \n
    Notifications::message("$json",['json' => $json]);
  }
  ////////////////////////////////////////////////////
  /*
  function debugSetup(){
    $players = self::loadPlayersBasicInfos();
    Cards::DB()->delete()->run();
    Cards::setupNewGame($players,[]);
    Tiles::DB()->delete()->run();
    Tiles::setupNewGame($players,[]);
  }

  function debugSetupPlayer(){
    $this->debugSetup();
    Meeples::DB()->delete()->run();

    $this->debugCLS();
    $this->stPlayerSetup();
    $this->debugUI();
  }
  ////////////////////////////////////////////////////
  //Reset game TABLE almost like setupNewGame
  ////////////////////////////////////////////////////
  function debugRESET(){
    Log::disable();
    $this->debugClearLogs();
    $options = ["DEBUG"=> true, 
      //OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_OFF,
      OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_DRAFT,
      //OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_ALTERNATIVE
    ];
    $players = self::loadPlayersBasicInfos();
    Globals::DB()->delete()->run();
    Players::DB()->delete()->run();
    Stats::DB()->delete()->run();
    Tiles::DB()->delete()->run();
    Cards::DB()->delete()->run();
    Meeples::DB()->delete()->run();
    $this->setGameStateValue('logging', 1);
    $this->player_preferences =[];
    $this->setupNewGame($players,$options);
    $this->debugUI();
    $this->gamestate->jumpToState(ST_CLAN_SELECTION);
    Log::enable();
  }

  function debugGoToPlayerSetup()
  {
    $this->gamestate->jumpToState(ST_PLAYER_SETUP);
  }
  
  function debugGoToDraft()
  {
    $this->gamestate->jumpToState(ST_CLAN_SELECTION);
  }
  
  //Fake deliveries for UI
  function debugLiv(){
    $players = Players::getAll();
    Cards::moveAllInLocation(CARD_LOCATION_DELIVERED,CARD_LOCATION_DECK);
    Cards::shuffle(CARD_LOCATION_DECK);
    $k =6;
    foreach($players as $pid => $player){

      $cards = Cards::pickForLocation($k, CARD_LOCATION_DECK, CARD_LOCATION_DELIVERED );
      foreach($cards as $card){
        $card->setPId($pid);
      }
      $k--;
    }
    $player = Players::getCurrent();
    Players::claimMasteries($player);
    $this->debugUI();
  }

  //To be called before clicking 'Deliver'
  function debugDeliverReshuffle(){
    Cards::moveAllInLocation(CARD_LOCATION_DECK,CARD_LOCATION_DISCARD);
  }
  
  //To be called before clicking 'Refill hand' or 'Draw', to test that interface doesn't force you to discard your last card OR worse if you don't have cards
  function debugDrawWithEmptyDeck(){
    Cards::moveAllInLocation(CARD_LOCATION_DECK,CARD_LOCATION_DISCARD);
    Cards::moveAllInLocation(CARD_LOCATION_DISCARD,"FAKE_FOR_TEST");
    $player = Players::getCurrent();
    $player->setBonuses([]);
    Globals::addBonus($player,BONUS_TYPE_REFILL_HAND);
    Globals::addBonus($player,BONUS_TYPE_DRAW);
    Globals::addBonus($player,BONUS_TYPE_CHOICE);
    $this->gamestate->jumpToState(ST_BONUS_CHOICE);
  }

  function debugMoney(){
    $player = Players::getCurrent();
    Notifications::giveMoney($player,55);
    Notifications::spendMoney($player,23);
  }

  function debugRess(){
    $player = Players::getCurrent();
    $player->giveResource(2,RESOURCE_TYPE_SUN);
    $player->giveResource(3,RESOURCE_TYPE_MOON);
    $this->gamestate->jumpToState(ST_PLAYER_TURN);
  }
  
  function debugTrade(){
    $player = Players::getCurrent();
    $player->setResources([
      RESOURCE_TYPE_MONEY => 10,
      RESOURCE_TYPE_SILK => 1,
      RESOURCE_TYPE_RICE => 2,
      RESOURCE_TYPE_POTTERY => 3,
      RESOURCE_TYPE_SUN => 2,
      RESOURCE_TYPE_MOON => 6,
    ]);
    $this->debugUI();
    $this->gamestate->jumpToState(ST_PLAYER_TURN_TRADE);
  }
  //Simulate a meeple in each influence space to test UI
  function debugInfluenceMeeples(){
    Meeples::DB()->delete()->run();
    $current = Players::getCurrent();
    $players = Players::getAll();
    foreach($players as $pid => $player){
      foreach (REGIONS as $region){
        for($k=0;$k<=NB_MAX_INLFUENCE;$k++){
          $meeple = Meeples::addClanMarkerOnInfluence($player, $region,false);
          $meeple->setPosition($k);
        }
        if($current->getId() == $pid){
          //Only 1 per space is expected 
          $meeple = Meeples::addClanMarkerOnArtisanSpace($player, $region);
          $meeple = Meeples::addClanMarkerOnElderSpace($player, $region);
        }
        $meeple = Meeples::addClanMarkerOnMerchantSpace($player);
      }
    }
    $this->debugUI();
  }

  function debugBonusChoice(){
    $player = Players::getCurrent();
    $player2 = Players::get($player->getId());
    $royalShip = $player->getRoyalShip();
    if(isset($royalShip)) $royalShip->setType(MEEPLE_TYPE_SHIP);
    $this->debugUI();

    $player->setBonuses([]);
    $player2->setBonuses([]);
    Globals::addBonus($player,BONUS_TYPE_CHOICE);
    Globals::addBonus($player,BONUS_TYPE_UPGRADE_SHIP);
    //$player2 = Players::get($player->getId());
    $player2 = $player;
    Globals::addBonus($player2,BONUS_TYPE_CHOICE);
    
    
    Globals::addBonus($player2,BONUS_TYPE_SELL_GOODS,clienttranslate("Sell goods"));
    
    Globals::addBonus($player,BONUS_TYPE_DRAW);
    
    Globals::addBonus($player,BONUS_TYPE_SECOND_MARKER_ON_BUILDING);
    Globals::addBonus($player,BONUS_TYPE_SECOND_MARKER_ON_OPPONENT);
    Globals::addBonus($player,BONUS_TYPE_MONEY_OR_GOOD);
    Globals::addBonus($player,BONUS_TYPE_REFILL_HAND);
    Globals::addBonus($player,BONUS_TYPE_SET_DIE,'',false);
    
    $this->gamestate->jumpToState(ST_BONUS_CHOICE);
  }

  //Add Boats on each river space
  function debugBoatMeeples(){
    Meeples::DB()->delete()->whereIn('type', [MEEPLE_TYPE_SHIP,MEEPLE_TYPE_SHIP_ROYAL])->run();
    $players = Players::getAll();
    $typeToTest = MEEPLE_TYPE_SHIP_ROYAL;
    for($k=1;$k<=NB_RIVER_SPACES;$k++){
      foreach($players as $pid => $player){
        $meeple = Meeples::addBoatOnRiverSpace($player,$k,false);
        $meeple->setType($typeToTest);
        $meeple = Meeples::addBoatOnRiverSpace($player,$k,false);
        $meeple->setType($typeToTest);
      }
    }
    $this->debugUI();
    $this->gamestate->jumpToState(ST_PLAYER_TURN_SAIL);
  }

  function debugUpgradeShip(){
    $player = Players::getCurrent();
    $ship = Meeples::getBoats($player->getId())->first();
    $this->debugUI();
    $ship->setType(MEEPLE_TYPE_SHIP_ROYAL);
    Notifications::upgradeShip($player,$ship);
  }
  
  //test mastery cards
  function debugMC(){
    $player = Players::getCurrent();
    $typesToTest = [7,8,9];

    //remove previous claims
    $masteryCards = Tiles::getMasteryCards();
    $k = 0;
    foreach ($masteryCards as $tile) {
      $clanMarkers = $tile->getMeeples();
      foreach ($clanMarkers as $clanMarker) {
        Meeples::DB()->delete($clanMarker->id);
      }
      $tile->setType($typesToTest[$k]);
      $k++;
    }
    //Remove owned buildings
    $tiles = Tiles::getBuildingTiles();
    foreach ($tiles as $tile) {
      $clanMarkers = $tile->getMeeples();
      foreach ($clanMarkers as $clanMarker) {
        Meeples::DB()->delete($clanMarker->id);
      }
      $meeple = Meeples::addClanMarkerOnShoreSpace($tile,$player);
    }
    
    //foreach (BUILDING_TYPES as $bType) {
    //  for($k=1;$k<=NB_BUILDINGS_WATER;$k++){
    //    $meeple = Meeples::addClanMarkerOnShoreSpace($tile,$player);
    //  }
    //}

    Players::gainInfluence($player,1,NB_INLUENCE_FLOWER);
    Players::claimMasteries($player);
    $this->gamestate->jumpToState(ST_PLAYER_TURN);
  }
  
  //test mastery cards 2
  function debugMC2(){
    $player = Players::getCurrent();
    $typesToTest = [10,11,12];

    //remove previous claims
    $masteryCards = Tiles::getMasteryCards();
    $k = 0;
    foreach ($masteryCards as $tile) {
      $clanMarkers = $tile->getMeeples();
      foreach ($clanMarkers as $clanMarker) {
        Meeples::DB()->delete($clanMarker->id);
      }
      $tile->setType($typesToTest[$k]);
      $k++;
    }
    
    foreach (REGIONS as $region){
      $player->setInfluence($region, NB_INLUENCE_VOID);
    }
    $this->debugUI();
    Players::claimMasteries($player);
    $this->gamestate->jumpToState(ST_PLAYER_TURN);
  }
  
  function debugMerchants(){
    $player = Players::getCurrent();
    //Globals::setBonuses([]);
    $player->setBonuses([]);
    CustomerCard::playOngoingMerchantAbility($player,CARD_MERCHANT_1);
    CustomerCard::playOngoingMerchantAbility($player,CARD_MERCHANT_2);
    CustomerCard::playOngoingMerchantAbility($player,CARD_MERCHANT_3);
    CustomerCard::playOngoingMerchantAbility($player,CARD_MERCHANT_4);
    CustomerCard::playOngoingMerchantAbility($player,CARD_MERCHANT_5);
    CustomerCard::playOngoingMerchantAbility($player,CARD_MERCHANT_6);
    $this->gamestate->jumpToState(ST_BONUS_CHOICE);
  }
  
  function debugEmperorVisit(){
    $player = Players::getCurrent(); 
    $this->runEmperorVisit();
  }
  
  //To be called before Confirming turn
  function debugTriggerLastTurn(){
    $player = Players::getCurrent(); 
    Tiles::moveAllInLocation(TILE_LOCATION_BUILDING_DECK_ERA_1,TILE_LOCATION_DISCARD);
    //Keep 1 card in ERA 2 :
    Tiles::moveAllInLocation(TILE_LOCATION_BUILDING_DECK_ERA_2,TILE_LOCATION_DISCARD);
    Tiles::getTopOf(TILE_LOCATION_DISCARD)->setLocation(TILE_LOCATION_BUILDING_DECK_ERA_2);
    $this->debugUI();
    //$this->gamestate->jumpToState(ST_CONFIRM_CHOICES);
  }
  
  function debugGoToScoring(){
    $this->gamestate->jumpToState(ST_END_SCORING);
  }
  function debugScoring(){
    $players = Players::getAll();
    $player = Players::getCurrent(); 
    //$testElderOnRegion = REGION_5;
    //$elder = Meeples::getMarkerOnElderSpace($player->getId(),$testElderOnRegion);
    //if(!isset($elder)){
    //  Meeples::addClanMarkerOnElderSpace($player,$testElderOnRegion);
    //}
    $this->computeFinalScore($players);
  }

  function debugManualScoring(){
    $player = Players::getCurrent(); 
    //With only 4 Koku we should not gain 1 point !
    $money = 4;
    $nbMerchants = 1;
    $scoreForRemainingMoney = $nbMerchants * floor( $money / NB_RESOURCES_FOR_1POINT_WITH_MERCHANT);
    if($scoreForRemainingMoney>0) {
      Notifications::scoreMerchants($player,$nbMerchants,$money,$scoreForRemainingMoney);
    }
  }
  
  function debugRefillRow(){
    $player = Players::getCurrent();
    Tiles::refillBuildingRow();
    //for testing notif sync, send other notifs:
    $player->addPoints(12);
    $player->addPoints(4);
    $player->addPoints(9);
  }

  //Slide 1,2,3 to 2,3,4
  function debugSlideRow(){
    $player = Players::getCurrent();
    $slidedTiles = [];
    for($k = BUILDING_ROW_END -1; $k>0;$k--){
      $buildingTile = Tiles::getInLocation(TILE_LOCATION_BUILDING_ROW,$k)->first();
      if(isset($buildingTile)){
        $buildingTile->setPosition($buildingTile->getPosition()+1);
        $slidedTiles[$buildingTile->getPosition()] = $buildingTile;
      }
    }
    Notifications::slideBuildingRow($slidedTiles);
    //RESET
    foreach($slidedTiles as $tile){
      $tile->setPosition($tile->getPosition()-1);
    }
    //UI doesn't refresh building row because it is not expected to cancel this change
    //$this->debugUI();
  }

  function debugPHP(){
    $keys = array_keys(RESOURCES_LIMIT);
    Notifications::message(json_encode($keys));
    $type = RESOURCE_TYPE_MONEY;
    if(array_key_exists($type,RESOURCES_LIMIT)) {
    //if(in_array($type,array_keys(RESOURCES_LIMIT)) ) {
      $max = RESOURCES_LIMIT[$type];
      Notifications::message("RESOURCES_LIMIT contains $type:".json_encode(RESOURCES_LIMIT));
    }
  }
  function debugCountDistinct(){
    $player = Players::getCurrent();
    $nb = Meeples::countUsedSpacedOnInfluenceTrack($player->getId(),REGION_3,1,1);
    Notifications::message("`DEBUG: $nb`");
  }

  //----------------------------------------------------------------
  //Clear logs
  function debugCLS(){
    $query = new QueryBuilder('gamelog', null, 'gamelog_packet_id');
    $query->delete()->run();
  }
  
  //Clear all logs
  public static function debugClearLogs()
  {
      $query = new QueryBuilder('log', null, 'id');
      $query->delete()->run();
      $query = new QueryBuilder('gamelog', null, 'gamelog_packet_id');
      $query->delete()->run();
  }
  //*/
  function debugUI(){
    //players colors are not reloaded after using LOAD/SAVE buttons
    self::reloadPlayersBasicInfos();
    Notifications::refreshUI($this->getAllDatas());
  }
}
