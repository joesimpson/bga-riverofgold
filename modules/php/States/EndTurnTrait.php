<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
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
    $playerPatron = $turnPlayer->getPatron();
    if(Globals::isLastTurnTriggered()){
      //NO DIE ROLL because no future turn
    }
    else if(isset($playerPatron) && PATRON_DARLING == $playerPatron->getType()){
      //TODO JSA darling may decide to not roll the die
      $turnPlayer->rollDie();
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
    $this->computeBuildingsOwnerRewards();
  }
  
  /**
   * All owner rewards
   */
  public function computeBuildingsOwnerRewards()
  { 
    $players = Players::getAll();
    $buidingTiles = Tiles::getInLocation(TILE_LOCATION_BUILDING_SHORE);
    foreach($buidingTiles as $tile){
      $clanMarkers = $tile->getMeeples();
      $region = $tile->getRegion();
      foreach($tile->ownerReward->entries as $reward){
        foreach($clanMarkers as $clanMarker){
          $owner = $players[$clanMarker->getPId()];
          $reward->rewardPlayer($owner,$region);
        }
      }
    }
  }
  
  /**
   * @param Player $player
   */
  public function triggerLastTurn($player)
  { 
    $player->addPoints(NB_POINTS_FOR_GAME_END);
    Globals::setEndPlayer($player->getId());
    Notifications::triggerLastTurn($player);
  }
}
