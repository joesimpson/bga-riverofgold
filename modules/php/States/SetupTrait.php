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
    $playersDatas = Players::setupNewGame($players, $options);
    Globals::setupNewGame($players, $options);
    Preferences::setupNewGame($players, $this->player_preferences);
    Stats::setupNewGame($playersDatas);
    Tiles::setupNewGame($players,$options);
    Cards::setupNewGame($players,$options);

    $this->setGameStateInitialValue('logging', true); 

    // Activate first player (which is in general a good idea :) )
    $this->activeNextPlayer();
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

      foreach (REGIONS as $region){
        Meeples::addClanMarkerOnInfluence($player, $region);
      }

      foreach(STARTING_BOATS_SPACES as $space){
        $boatPosition = $space + $player->rollDie();
        $meeple = Meeples::addBoatOnRiverSpace($player,$boatPosition);
      }

      //Draw first cards
      $cards = Cards::pickForLocation(NB_CARDS_PER_PLAYER, CARD_LOCATION_DECK, CARD_LOCATION_HAND );
      foreach($cards as $card){
        $card->setPId($pid);
        Notifications::giveCardTo($player,$card);
      }

      //TODO JSA intial money according to first player
      $initialMoney = $k + 7;
      //Get first resources
      $player->setResources([]);
      $player->giveResource($initialMoney,RESOURCE_TYPE_MONEY);
      $player->giveResource(1,RESOURCE_TYPE_SILK);
      $player->giveResource(1,RESOURCE_TYPE_POTTERY);
      $player->giveResource(1,RESOURCE_TYPE_RICE);
      $player->giveResource(3,RESOURCE_TYPE_MOON);
      $player->giveResource(2,RESOURCE_TYPE_SUN);
      $k++;

      //first turn die roll
      $player->rollDie();
    }

    $this->addCheckpoint(ST_NEXT_TURN);
    $this->gamestate->nextState('next');
  }
}
