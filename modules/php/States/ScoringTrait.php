<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\AutomaPlayer;
use ROG\Models\Meeple;
use ROG\Models\CustomerCard;

trait ScoringTrait
{
 
  //FOR TESTING PURPOSE
  public function stPreEndOfGame()
  {
    self::trace("stPreEndOfGame()");
    Notifications::emptyNotif();
    $this->gamestate->nextState('next');
  }

  public function stScoring()
  {
    self::trace("stScoring()");

    $players = Players::getAllWithAutoma();
    $this->computeFinalScore($players);
    $this->checkCoopVictory($players);

    $this->gamestate->nextState('next');
  }
  
  public function checkBeforeScoring($players)
  {
    self::trace("checkBeforeScoring()");
    foreach($players as $pid => $player){
      $scenario = Cards::getScenario($player);
      if(isset($scenario)){
        $scenario->beforeScoring($player);
      }
    }
  }

  public function computeFinalScore($players)
  {
    self::trace("computeFinalScore()");
    Notifications::computeFinalScore();
    $this->checkBeforeScoring($players);
    $endScoringDatas = [];
    //Query influence meeples before looping
    $influenceMarkers = [];
    $scoringTiles = Tiles::getInLocationOrdered(TILE_LOCATION_SCORING);
    foreach(REGIONS as $region){
      $influenceMarkers[$region] = Meeples::getAllPlayersInfluenceMarkers($region,$players->getIds());
    }
    //INIT Datas to save
    foreach($players as $pid => $player){
      $endScoringDatas[$pid] = [
        SCORING_INGAME => $player->getScore(), 
        SCORING_INFLUENCE => [], 
        SCORING_DELIVERED => 0, 
        SCORING_CUSTOMERS=> 0
      ];
      
      //$this->trace("player::class = ".($player::class));
      if($player instanceof AutomaPlayer){
        Cards::deliverHiddenCards($player);
      }
    }

    //RULE 1 : REGIONAL INFLUENCE, scored region by region
    foreach(REGIONS as $region){
      foreach($players as $pid => $player){
        $this->trace("Final scoring for player $pid in region $region ...");
        $playerMarker = $influenceMarkers[$region]->filter( function($meeple) use ($pid) { 
            return $meeple->getPId() == $pid; 
          })->first();
        $playerPosition = $playerMarker ? $playerMarker->getPosition() : 0;
        $opponentPositions = $influenceMarkers[$region]->filter( function($meeple) use ($pid) { 
            return $meeple->getPId() != $pid; 
          })->map(function($meeple) { 
            return $meeple->getPosition(); 
          })->toArray();

        $scoringTile = $scoringTiles->filter(function($tile) use ($region) {return $region == $tile->getRegion();})->first();
        if(!isset($scoringTile)) throw new UnexpectedException(404,"Missing scoring tile for region $region");
        $influenceScore = $scoringTile->computeScore($playerPosition,$opponentPositions);
        $endScoringDatas[$pid][SCORING_INFLUENCE][$region] = $influenceScore;

        if($influenceScore>0){
          $player->addPoints($influenceScore,false);
          Notifications::scoreInfluence($player,$scoringTile,$region,$influenceScore,$playerPosition);

          //check Elder space to double influence score
          $elder = Meeples::getMarkerOnElderSpace($player->getId(),$region);
          if(isset($elder)){
            $player->addPoints($influenceScore,false);
            Notifications::scoreElder($player,$scoringTile,$region,$influenceScore);
            $endScoringDatas[$pid][SCORING_CUSTOMERS] += $influenceScore;
          }
        }
      }
    }

    foreach($players as $pid => $player){
      //RULE 2 : CUSTOMERS
      $nbDeliveries = $player->getNbDeliveredCustomers();
      $scoreForNbDeliveries = 0;
      switch($nbDeliveries){
        case 0: $scoreForNbDeliveries = 0; break;
        case 1: $scoreForNbDeliveries = 2; break;
        case 2: $scoreForNbDeliveries = 5; break;
        case 3: $scoreForNbDeliveries = 9; break;
        case 4: $scoreForNbDeliveries = 14; break;
        case 5: $scoreForNbDeliveries = 20; break;
        case 6: 
        //Default for 6 or more
        default: $scoreForNbDeliveries = 27; break;
      }
      $player->addPoints($scoreForNbDeliveries,false);
      Notifications::scoreDeliveries($player,$scoreForNbDeliveries,$nbDeliveries);
      $endScoringDatas[$pid][SCORING_DELIVERED] = $scoreForNbDeliveries;

      //RULE 3 : CUSTOMER BONUSES : artisans, merchants, nobles
      $fixedEndResourcesForCustomers = null;
      if($player instanceof AutomaPlayer){
        $level = Globals::getOptionSeishin();
        $fixedEndResourcesForCustomers = [ 
          BONUS_TYPE_CHOICE =>    ['n' => 3 * $level , /*'name' => clienttranslate('Choose any trade good')*/ ], 
          RESOURCE_TYPE_MONEY =>  ['n' => 5 * $level , /*'name' => clienttranslate('Koku')                 */ ], 
          RESOURCE_TYPE_SUN =>    ['n' => 1 * $level , /*'name' => clienttranslate('Divine favor')         */ ], 
        ];
        Notifications::endResourcesForCustomers($player,$level,$fixedEndResourcesForCustomers);
      }
      //3.1 ARTISANS score remaining trade goods :
      $nbResources = $player->getResource(RESOURCE_TYPE_SILK) + $player->getResource(RESOURCE_TYPE_RICE)+ $player->getResource(RESOURCE_TYPE_POTTERY);
      if(isset($fixedEndResourcesForCustomers)){
        $nbResources = $fixedEndResourcesForCustomers[BONUS_TYPE_CHOICE]['n'];
      }
      $nbArtisans = $player->getNbDeliveredCustomerByType(CUSTOMER_TYPE_ARTISAN);
      $scoreForRemainingGoods = $nbArtisans * floor( $nbResources / NB_RESOURCES_FOR_1POINT_WITH_ARTISAN);
      if($scoreForRemainingGoods>0) {
        $player->addPoints($scoreForRemainingGoods,false);
        Notifications::scoreArtisans($player,$nbArtisans,$nbResources,$scoreForRemainingGoods);
        $endScoringDatas[$pid][SCORING_CUSTOMERS] += $scoreForRemainingGoods;
      }
      //3.2 : Merchants score remaining money :
      $money = $player->getMoney();
      if(isset($fixedEndResourcesForCustomers)){
        $money = $fixedEndResourcesForCustomers[RESOURCE_TYPE_MONEY]['n'];
      }
      $nbMerchants = $player->getNbDeliveredCustomerByType(CUSTOMER_TYPE_MERCHANT);
      $scoreForRemainingMoney = $nbMerchants * floor( $money / NB_RESOURCES_FOR_1POINT_WITH_MERCHANT);
      if($scoreForRemainingMoney>0) {
        $player->addPoints($scoreForRemainingMoney,false);
        Notifications::scoreMerchants($player,$nbMerchants,$money,$scoreForRemainingMoney);
        $endScoringDatas[$pid][SCORING_CUSTOMERS] += $scoreForRemainingMoney;
      }
      //3.3 : Noble score is specific :
      $delivered = Cards::getPlayerDeliveredOrders($player->getId());
      $deliveredNobles = $delivered->filter(function($card) {return CUSTOMER_TYPE_NOBLE == $card->getCustomerType();});
      $customScore = function ($deliveredNobles) use(&$player,$pid,$delivered, &$endScoringDatas){
      foreach($deliveredNobles as $deliveredNoble){
        $scoreNoble = $deliveredNoble->computeScore($player,$delivered);
        $player->addPoints($scoreNoble,false);
        //Specific notif has been sent
        $endScoringDatas[$pid][SCORING_CUSTOMERS] += $scoreNoble;
      }
      };
      $customScore($deliveredNobles);

      //3.4 : SHin score remaining favor :
      $resourceScored = RESOURCE_TYPE_SUN;
      $favor = $player->getResource($resourceScored);
      if(isset($fixedEndResourcesForCustomers)){
        $favor = $fixedEndResourcesForCustomers[$resourceScored]['n'];
      }
      $nbShins = $player->getNbDeliveredCustomerByType(CUSTOMER_TYPE_SHINDOSHI);
      $scoreForRemainingFavor = $nbShins * floor( $favor / NB_RESOURCES_FOR_1POINT_WITH_SHIN);
      if($scoreForRemainingFavor>0) {
        $player->addPoints($scoreForRemainingFavor,false);
        Notifications::scoreMultiCustomers($player,CUSTOMER_TYPE_SHINDOSHI,$nbShins,$resourceScored,$favor,$scoreForRemainingFavor);
        $endScoringDatas[$pid][SCORING_CUSTOMERS] += $scoreForRemainingFavor;
      }

      //3.5 :  Smuggler Specific score 
      $deliveredSmugglers = $delivered->filter(function($card) {return CUSTOMER_TYPE_SMUGGLER == $card->getCustomerType();});
      $customScore($deliveredSmugglers);

      //3.7 :  Traders scores
      $deliveredTraders = $delivered->filter(function($card) {return CUSTOMER_TYPE_TRADER == $card->getCustomerType();});
      $customScore($deliveredTraders);

      //TIE BREAKER : DIVINE FAVOR 
      $player->setScoreAux($player->getResource(RESOURCE_TYPE_SUN));
      
    }
    Globals::setEndScoring($endScoringDatas);
  }
  
  public function checkCoopVictory($players)
  {
    if(!Utils::isGameWithAutoma()) return;

    self::trace("checkCoopVictory()");
    $endScoringDatas = Globals::getEndScoring();
    
    //COMPARE SCORES WITH AUTOMA : "You and your ally win or lose as a team. To win, each of you must have a higher score than Seishin. If either of your scores is lower than or equal to Seishin’s, you both lose the game."
    $automaScore = Globals::getAutomaScore();
    $eliminated = [];
    $lowestScore = null;
    foreach($players as $pId => $player){
      if( $player instanceof AutomaPlayer) continue;
      //----------------------
      $playerScore = Players::getUpdatedPlayerScore($pId);
      if(!isset($lowestScore)) $lowestScore = $playerScore;
      $lowestScore = min($lowestScore, $playerScore);
      if($playerScore <= $automaScore ){
        Notifications::eliminateByScore($player,$automaScore,$playerScore);
        $eliminated[$pId] = $player;
      }
      //----------------------
      $scenario = Cards::getScenario($player);
      if(isset($scenario)){
        if($scenario->checkEndConditions()){
          Notifications::scenarioCompleted($player,$scenario);
          $endScoringDatas[$pId][SCORING_COMPLETE_SCENARIO] = true; 
        }
        else {
          Notifications::scenarioFailed($player,$scenario);
          $eliminated[$pId] = $player;
          $endScoringDatas[$pId][SCORING_COMPLETE_SCENARIO] = false; 
        }
      }
    }
    //--------------------------------------------------
    if(count($eliminated) == 0){
      Notifications::teamWin($lowestScore);
      foreach($players as $pId => $player){
        if( $player instanceof AutomaPlayer) continue;
        //Set all team players to the same score to display all as winners
        $player->setScore($lowestScore);
        $player->setScoreAux(0);
      }
    }
    else {
      Notifications::teamLoose();
      foreach($players as $pId => $player){
        if( $player instanceof AutomaPlayer) continue;
        //RESET SCORE TO -1 if LOST for BGA framework
        $player->setScore(SCORE_FAIL);
        $player->setScoreAux(0);
      }
    }
    //--------------------------------------------------
    Globals::setEndScoring($endScoringDatas);
  }

}
