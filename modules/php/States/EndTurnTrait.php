<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Managers\Players;
use ROG\Managers\Tiles;

trait EndTurnTrait
{
   
  public function stEndTurn()
  { 
    self::trace("stEndTurn()");

    $activePlayer = Players::getActive();
    $turnPlayerId = Globals::getTurnPlayer();

    //manage opponent bonuses if any
    $nextPlayer = Players::getNextPlayerWithBonusToChoose($activePlayer->getId());
    if($this->goToBonusStepIfNeeded($nextPlayer,true)){
      return;
    }
    
    $turnPlayer = Players::get($turnPlayerId);
    Notifications::endTurn($turnPlayer);
    $refillResult = Tiles::refillBuildingRow();
    $lastEra1TileMoved = $refillResult[1];
    $lastEra2TileMoved = $refillResult[2];
    if($lastEra1TileMoved){
      //run Emperor Visit at end of Era 1 + starts Era 2
      $this->runEmperorVisit();
      //Emperor can give other bonuses
      $nextPlayer = Players::getNextPlayerWithBonusToChoose($turnPlayerId);
      if($this->goToBonusStepIfNeeded($nextPlayer,isset($nextPlayer) && $turnPlayerId != $nextPlayer->getId())){
        return;
      }
    }
    if($lastEra2TileMoved){
      $this->triggerLastTurn($turnPlayer);
    }
    if(Globals::isLastTurnTriggered()){
      //Save this player played for last time
      $turnPlayer->setLastTurnPlayed(true);
    }
    //Checkpoint after Emperor, because turn player could decide to cancel their turn if they realize, there is an Emperor visit ?
    $this->addCheckpoint(ST_END_TURN);

    //RULE : roll your die at the end of your turn, before others play
    if(Globals::isLastTurnTriggered()){
      //NO DIE ROLL because no future turn
    }
    else {
      $turnPlayer->rollDie();
    }
    
    $this->addCheckpoint(ST_NEXT_TURN);
    $this->gamestate->nextState('next');
  }
  
  public function runEmperorVisit()
  { 
    Globals::setEra(2);
    Notifications::emperorVisit(2);
    $this->computeBuildingsOwnerRewards(true);
    Notifications::emperorVisitEnd();
  }
  
  /**
   * All owner rewards
   * @param bool $isEmperorVisit (default false)
   */
  public function computeBuildingsOwnerRewards($isEmperorVisit = false)
  { 
    $players = Players::getAll();
    $buidingTiles = Tiles::getInLocation(TILE_LOCATION_BUILDING_SHORE);
    foreach($buidingTiles as $tile){
      $clanMarkers = $tile->getMeeples();
      if($clanMarkers->count() == 0 ) continue;
      $region = $tile->getRegion();

      Notifications::emperorReward($tile);
      foreach($tile->ownerReward->entries as $reward){
        $reward->isEmperorVisit = $isEmperorVisit;
        foreach($clanMarkers as $clanMarker){
          $owner = $players[$clanMarker->getPId()];
          $reward->rewardPlayer($owner,$region,$tile);
          //SAVE UPDATED PLAYER datas
          $players[$clanMarker->getPId()] = $owner;
        }
      }
    }
  }
  
  /**
   * @param Player $player
   */
  public function triggerLastTurn(&$player)
  { 
    Notifications::triggerLastTurn($player);
    $player->addPoints(NB_POINTS_FOR_GAME_END);
    Globals::setEndPlayer($player->getId());
    Stats::set( "endPlayer", $player->getId(), 1);
  }
}
