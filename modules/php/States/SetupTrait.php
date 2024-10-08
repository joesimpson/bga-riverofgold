<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Preferences;
use ROG\Core\Stats;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\Player;

trait SetupTrait
{
  
  /*
      setupNewGame:
      
      This method is called only once, when a new game is launched.
      In this method, you must setup the game according to the game rules, so that
      the game is ready to be played.
  */
  protected function setupNewGame($players, $options = [])
  {
    Globals::setupNewGame($players, $options);
    $playersDatas = Players::setupNewGame($players, $options);
    Preferences::setupNewGame($players, $this->player_preferences);
    Stats::setupNewGame($playersDatas);
    Tiles::setupNewGame($players,$options);
    Cards::setupNewGame($players,$options);

    $this->setGameStateInitialValue('logging', true); 

    // Activate first player (which is in general a good idea :) )
    if(!array_key_exists("DEBUG",$options)){
      //$this->activeNextPlayer();
      Players::changeActive(Globals::getFirstPlayer());
    }
    /************ End of the game initialization *****/
  }

  /**
   * Set up of players datas/resources depend on their clan
   */
  public function stPlayerSetup()
  {
    self::trace("stPlayerSetup()");

    $players = Players::getAll();
    $k =0;
    foreach($players as $pid => $player){
      $playerPatron = $player->getPatron();

      Notifications::newClanMarkers($player);
      $influenceMeeples = [];
      foreach (REGIONS as $region){
        $influenceMeeples[] = Meeples::addClanMarkerOnInfluence($player, $region,false);
      }
      Notifications::influenceClanMarkers($player,$influenceMeeples);

      foreach(STARTING_BOATS_SPACES as $space){
        $boatPosition = $space + $player->rollDie();
        $meeple = Meeples::addBoatOnRiverSpace($player,$boatPosition);
      }

      //Draw first cards
      $startingCards = NB_CARDS_PER_PLAYER;
      if(isset($playerPatron) && PATRON_SON_OF_STORM == $playerPatron->getType()){
        $startingCards = NB_CARDS_FOR_YORITOMO;
      }
      $cards = Cards::pickForLocation($startingCards, CARD_LOCATION_DECK, CARD_LOCATION_HAND );
      foreach($cards as $card){
        $card->setPId($pid);
        Notifications::giveCardTo($player,$card);
      }

      //initial money according to Clan patron
      $initialMoney = 0;
      if(isset($playerPatron) && PATRON_MASTER_ENGINEER == $playerPatron->getType()){
        $initialMoney += 10;
      }
      $player->giveResource($initialMoney,RESOURCE_TYPE_MONEY);
      $k++;

      //first turn die roll
      if(isset($playerPatron) && PATRON_DARLING == $playerPatron->getType()){
        $player->setSkipRollDie(true);
        //no setup roll for darling
      }
      $player->rollDie();
    }

    $this->addCheckpoint(ST_NEXT_TURN);
    $this->gamestate->nextState('next');
  }
}
