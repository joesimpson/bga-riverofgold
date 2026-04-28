<?php
namespace ROG;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Helpers\Log;
use ROG\Helpers\QueryBuilder;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\CustomerCard;
use ROG\States\EndTurnTrait;

/**
 * Debugging functions to be called in chat window in BGA Studio
 */
trait DebugTrait
{

  /**
   * Function to call to regenerate JSON from PHP 
   */
  function debug_JSON(){
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
    $this->debug_UI();
  }
  ////////////////////////////////////////////////////
  //Reset game TABLE almost like setupNewGame
  ////////////////////////////////////////////////////
  function debug_RESET(
    bool $expansionClans = true,
    bool $expansionClansAlt = true,
  ){
    Log::disable();
    $this->debug_ClearLogs();
    $options = ["DEBUG"=> true, 
      OPTION_EXPANSION_CLANS => ($expansionClans ? (
            $expansionClansAlt ? 
            OPTION_EXPANSION_CLANS_ALTERNATIVE : 
            OPTION_EXPANSION_CLANS_DRAFT
          ) : 
          OPTION_EXPANSION_CLANS_OFF
        ) ,
      //OPTION_EXPANSION_CLANS => OPTION_EXPANSION_CLANS_DRAFT,
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
    $this->debug_UI();
    $this->gamestate->jumpToState(ST_CLAN_SELECTION);
    Log::enable();
  }
  
  function debug_UI(){
    //players colors are not reloaded after using LOAD/SAVE buttons
    self::reloadPlayersBasicInfos();
    Notifications::refreshUI($this->getAllDatas());
  }
  function debug_refreshState(){
      $this->refresh_state();
  }

  /**
   * Another example of debug function, to easily test the zombie code.
   */
  public function debug_playOneMove(int $nbMoves = 1) {
      $this->debug->playUntil(fn(int $count) => $count == $nbMoves);
  }
  
  /** Test useful after loading a bug report made too late, we can rollback to previous moves */
  function debug_UndoToMove(int $moveId = 30){
      $player = Players::getCurrent();
      $query = new QueryBuilder('log', null, 'id');
      $query = $query->select(['id'])->where('move_id', $moveId);
      $logMove = $query
          ->orderBy('id', 'DESC')
          ->limit(1)
          ->get()
          ->first();
      Log::revertTo($logMove['id']);
      Notifications::restartTurn($player);
  }
  
  public function debug_goToState(int $state = ST_PLAYER_TURN) {
    $this->gamestate->jumpToState($state);
  }
  
  //Fake deliveries for UI
  function debug_Liv(){
    $this->addStep();
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
    $this->debug_UI();
    $this->refresh_state();
  }

  //To be called before clicking 'Deliver'
  function debug_DeliverReshuffle(){
    $this->addStep();
    Cards::moveAllInLocation(CARD_LOCATION_DECK,CARD_LOCATION_DISCARD);
    $this->refresh_state();
  }
  
  //To be called before clicking 'Refill hand' or 'Draw', to test that interface doesn't force you to discard your last card OR worse if you don't have cards
  function debug_DrawWithEmptyDeck(){
    $this->addStep();
    Cards::moveAllInLocation(CARD_LOCATION_DECK,CARD_LOCATION_DISCARD);
    Cards::moveAllInLocation(CARD_LOCATION_DISCARD,"FAKE_FOR_TEST");
    $player = Players::getCurrent();
    $player->setBonuses([]);
    Globals::addBonus($player,BONUS_TYPE_REFILL_HAND);
    Globals::addBonus($player,BONUS_TYPE_DRAW);
    Globals::addBonus($player,BONUS_TYPE_CHOICE);
    $this->gamestate->jumpToState(ST_BONUS_CHOICE);
  }

  function debug_Money(){
    $this->addStep();
    $player = Players::getCurrent();
    Notifications::giveMoney($player,55);
    Notifications::spendMoney($player,23);
    $this->refresh_state();
  }

  function debug_Resources( 
    int $money    = 10, 
    int $silk     = 3, 
    int $rice     = 2, 
    int $pottery  = 3, 
    int $sun      = 2, 
    int $moon     = 6, 
    int $dieFace  = 6, 
  ){
    $this->addStep();
    $player = Players::getCurrent();
    $player->setResources([
      RESOURCE_TYPE_MONEY   => $money  ,
      RESOURCE_TYPE_SILK    => $silk   ,
      RESOURCE_TYPE_RICE    => $rice   ,
      RESOURCE_TYPE_POTTERY => $pottery,
      RESOURCE_TYPE_SUN     => $sun    ,
      RESOURCE_TYPE_MOON    => $moon   ,
    ]);
    $player->setDie($dieFace);
    $this->debug_UI();
    $this->refresh_state();
  }
  
  function debug_Trade(){
    $this->addStep();
    $player = Players::getCurrent();
    $player->setResources([
      RESOURCE_TYPE_MONEY => 10,
      RESOURCE_TYPE_SILK => 1,
      RESOURCE_TYPE_RICE => 2,
      RESOURCE_TYPE_POTTERY => 3,
      RESOURCE_TYPE_SUN => 2,
      RESOURCE_TYPE_MOON => 6,
    ]);
    $this->debug_UI();
    $this->gamestate->jumpToState(ST_PLAYER_TURN_TRADE);
  }
  //Simulate a meeple in each influence space to test UI
  function debug_InfluenceMeeples(){
    $this->addStep();
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
    $this->debug_UI();
    $this->refresh_state();
  }

  function debug_BonusChoice(){
    $this->addStep();
    $player = Players::getCurrent();
    $player2 = Players::get($player->getId());
    $royalShip = $player->getRoyalShip();
    if(isset($royalShip)) $royalShip->setType(MEEPLE_TYPE_SHIP);
    $this->debug_UI();

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
  function debug_BoatMeeples(){
    $this->addStep();
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
    $this->debug_UI();
    $this->gamestate->jumpToState(ST_PLAYER_TURN_SAIL);
  }

  function debug_UpgradeShip(){
    $this->addStep();
    $player = Players::getCurrent();
    $ship = Meeples::getBoats($player->getId())->first();
    $this->debug_UI();
    $ship->setType(MEEPLE_TYPE_SHIP_ROYAL);
    Notifications::upgradeShip($player,$ship);
    $this->refresh_state();
  }
  
  //test mastery cards
  function debug_MC(){
    $this->addStep();
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
  function debug_MC2(){
    $this->addStep();
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
    $this->debug_UI();
    Players::claimMasteries($player);
    $this->gamestate->jumpToState(ST_PLAYER_TURN);
  }
  
  function debug_Merchants(){
    $this->addStep();
    $player = Players::getCurrent();
    $this->addStep();
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
  
  function debug_EmperorVisit(){
    $this->addStep();
    $player = Players::getCurrent(); 
    $this->runEmperorVisit();
    $this->refresh_state();
  }
  
  //To be called before Confirming turn
  function debug_TriggerLastTurn(){
    $this->addStep();
    Globals::setEndPlayer(null);
    $player = Players::getCurrent(); 
    Tiles::moveAllInLocation(TILE_LOCATION_BUILDING_DECK_ERA_1,TILE_LOCATION_DISCARD);
    //Keep 1 card in ERA 2 :
    Tiles::moveAllInLocation(TILE_LOCATION_BUILDING_DECK_ERA_2,TILE_LOCATION_DISCARD);
    Tiles::getTopOf(TILE_LOCATION_DISCARD)->setLocation(TILE_LOCATION_BUILDING_DECK_ERA_2);
    $this->debug_UI();
    //$this->gamestate->jumpToState(ST_CONFIRM_CHOICES);

    //... or mock by testing notif
    Notifications::triggerLastTurn($player);
    $this->refresh_state();
  }
  
  function debug_GoToScoring(){
    $this->addStep();
    $this->gamestate->jumpToState(ST_END_SCORING);
  }
  function debug_Scoring(){
    $this->addStep();
    $players = Players::getAll();
    $player = Players::getCurrent(); 
    //$testElderOnRegion = REGION_5;
    //$elder = Meeples::getMarkerOnElderSpace($player->getId(),$testElderOnRegion);
    //if(!isset($elder)){
    //  Meeples::addClanMarkerOnElderSpace($player,$testElderOnRegion);
    //}
    $this->computeFinalScore($players);
    $this->refresh_state();
  }

  function debug_ManualScoring(){
    $this->addStep();
    $player = Players::getCurrent(); 
    //With only 4 Koku we should not gain 1 point !
    $money = 4;
    $nbMerchants = 1;
    $scoreForRemainingMoney = $nbMerchants * floor( $money / NB_RESOURCES_FOR_1POINT_WITH_MERCHANT);
    if($scoreForRemainingMoney>0) {
      Notifications::scoreMerchants($player,$nbMerchants,$money,$scoreForRemainingMoney);
    }
    $this->refresh_state();
  }
  
  function debug_RefillRow(){
    $this->addStep();
    $player = Players::getCurrent();
    Tiles::refillBuildingRow();
    //for testing notif sync, send other notifs:
    $player->addPoints(12);
    $player->addPoints(4);
    $player->addPoints(9);
  }

  //Slide 1,2,3 to 2,3,4
  function debug_SlideRow(){
    $this->addStep();
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
    //$this->debug_UI();
  }

  function debug_PHP(){
    $keys = array_keys(RESOURCES_LIMIT);
    Notifications::message(json_encode($keys));
    $type = RESOURCE_TYPE_MONEY;
    if(array_key_exists($type,RESOURCES_LIMIT)) {
    //if(in_array($type,array_keys(RESOURCES_LIMIT)) ) {
      $max = RESOURCES_LIMIT[$type];
      Notifications::message("RESOURCES_LIMIT contains $type:".json_encode(RESOURCES_LIMIT));
    }
  }
  
  //Test positive_modulo ?
  function debug_Modulo(){
    $player = Players::getCurrent();
    $nbMoves = $player->getDie();
    $nbDieFaces = count(DIE_FACES);
    $indexDieFace = array_search($nbMoves,DIE_FACES);

    $modifier = 0;
    $mod = Utils::positive_modulo($indexDieFace +$modifier, $nbDieFaces);
    $playableDieFace = DIE_FACES[$mod ];
    Notifications::message(" die=$indexDieFace +$modifier  --- mod= $mod --- =$playableDieFace");

    $modifier = +1;
    $mod = Utils::positive_modulo($indexDieFace +$modifier, $nbDieFaces);
    $playableDieFace = DIE_FACES[$mod ];
    Notifications::message(" die=$indexDieFace +$modifier--- mod= $mod --- =$playableDieFace");

    $modifier = -1;
    $mod = Utils::positive_modulo($indexDieFace +$modifier, $nbDieFaces);
    $playableDieFace = DIE_FACES[$mod ];
    Notifications::message(" die=$indexDieFace +$modifier--- mod= $mod --- =$playableDieFace");
  }
  function debugCountDistinct(){
    $player = Players::getCurrent();
    $nb = Meeples::countUsedSpacedOnInfluenceTrack($player->getId(),REGION_3,1,1);
    Notifications::message("`DEBUG: $nb`");
  }

  //----------------------------------------------------------------
  //Clear logs
  function debug_CLS(){
    $query = new QueryBuilder('gamelog', null, 'gamelog_packet_id');
    $query->delete()->run();
  }
  
  //Clear all logs
  public static function debug_ClearLogs()
  {
      $query = new QueryBuilder('log', null, 'id');
      $query->delete()->run();
      $query = new QueryBuilder('gamelog', null, 'gamelog_packet_id');
      $query->delete()->run();
  }
  //*/
  function refresh_state(){
      $this->debug_goToState($this->gamestate->getCurrentMainStateId());
  }
}
