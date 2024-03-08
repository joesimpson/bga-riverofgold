<?php
namespace ROG;

use ROG\Core\Notifications;
use ROG\Helpers\QueryBuilder;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;

trait DebugTrait
{
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
  
  function debugLiv(){
    $players = Players::getAll();
    Cards::moveAllInLocation(CARD_LOCATION_DELIVERED,CARD_LOCATION_DECK);
    $k =1;
    foreach($players as $pid => $player){

      $cards = Cards::pickForLocation($k, CARD_LOCATION_DECK, CARD_LOCATION_DELIVERED );
      foreach($cards as $card){
        $card->setPId($pid);
      }
      $k++;
    }
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
    $players = Players::getAll();
    foreach($players as $pid => $player){
      foreach (REGIONS as $region){
        for($k=0;$k<=NB_MAX_INLFUENCE;$k++){
          $meeple = Meeples::addClanMarkerOnInfluence($player, $region,false);
          $meeple->setPosition($k);
        }
      }
    }
    $this->debugUI();
  }

  function debugBoatMeeples(){
    Meeples::DB()->delete()->where('type', MEEPLE_TYPE_SHIP)->run();
    $players = Players::getAll();
    foreach($players as $pid => $player){
      for($k=1;$k<=2;$k++){
        $meeple = Meeples::addBoatOnRiverSpace($player,$k);
      }
    }
    $this->debugUI();
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

    Players::gainInfluence($player,1,NB_INLUENCE_FLOWER);
    Players::claimMasteries($player);
    $this->gamestate->jumpToState(ST_PLAYER_TURN);
  }

  //----------------------------------------------------------------
  function debugUI(){
    Notifications::refreshUI($this->getAllDatas());
  }

  //Clear logs
  function debugCLS(){
    $query = new QueryBuilder('gamelog', null, 'gamelog_packet_id');
    $query->delete()->run();
  }
}
