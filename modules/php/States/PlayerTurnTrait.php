<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Managers\Players;

trait PlayerTurnTrait
{
   
  public function argPlayerTurn()
  { 
    $actions = [];
    
    $activePlayer = Players::getActive();
    if(count($this->listPossibleDieFacesToBuy($activePlayer))>0 ){
      $actions[] = 'actSpendFavor';
    }
    if(count($this->listPossibleTrades($activePlayer))>0 ){
      $actions[] = 'actTrade';
    }
    if(count($this->listPossibleSpacesToBuild($activePlayer))>0 ){
      $actions[] = 'actBuild';
    }
    //Sail always possible
    $actions[] = 'actSail';
    if(count($this->listPossibleCardsToDeliver($activePlayer))>0 ){
      $actions[] = 'actDeliver';
    }
    $die_face = $activePlayer->getDie();
    $args = [
      'a' => $actions,
      'die_face' => $die_face,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 

  public function actSpendFavor()
  { 
    self::checkAction('actSpendFavor'); 
    self::trace("actSpendFavor()");

    $this->addStep();

    $this->gamestate->nextState('favor');
  }

  public function actTrade()
  { 
    self::checkAction('actTrade'); 
    self::trace("actTrade()");

    $this->addStep();
    $currentState = $this->gamestate->state_id();
    Globals::setStateBeforeTrade($currentState);

    $this->gamestate->nextState('trade');
  }

  public function actBuild()
  { 
    self::checkAction('actBuild'); 
    self::trace("actBuild()");

    $this->addStep();

    $this->gamestate->nextState('build');
  }

  public function actSail()
  { 
    self::checkAction('actSail'); 
    self::trace("actSail()");
    $this->addStep();

    $this->gamestate->nextState('sail');
  }
  
  public function actDeliver()
  { 
    self::checkAction('actDeliver'); 
    self::trace("actDeliver()");
    $this->addStep();

    $this->gamestate->nextState('deliver');
  }

  function stConfirmChoices()
  {
    $this->gamestate->nextState('');
  }

  
  /**
   * Go to bonus transition after current turn action
   * @param Player $player
   * @param bool $changeActivePlayer (Default false) 
   * @return bool true if state changed
   */
  function goToBonusStepIfNeeded($player, $changeActivePlayer = false)
  {
    if(!isset($player)) return false;
    //refresh datas
    $player = Players::get($player->getId());
    $bonuses = $player->getBonuses();
    if(isset($bonuses) && count($bonuses)>0){
      $player->giveExtraTime();
      if($changeActivePlayer){
        //Change active player when in a game state !
        Players::changeActive($player->getId());
        $this->addCheckpoint(ST_BONUS_CHOICE);
      }
      $this->gamestate->nextState('bonus');
      return true;
    }
    return false;
  }
}
