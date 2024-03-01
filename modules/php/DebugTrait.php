<?php
namespace ROG;

use ROG\Core\Notifications;
use ROG\Managers\Cards;
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

  function debugSetupPlayerCards(){
    $this->debugSetup();
    /*
    $players = Players::getAll();
    foreach($players as $pid => $player){

      $cards = Cards::pickForLocation(NB_CARDS_PER_PLAYER, CARD_LOCATION_DECK, CARD_LOCATION_HAND );
      //Cards::DB()->update(['player_id'=>$pid],$cards->getIds());
      foreach($cards as $card){
        $card->setPId($pid);
      }
    }
    */
    $this->stPlayerSetup();
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
}
