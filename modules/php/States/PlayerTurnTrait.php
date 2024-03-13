<?php

namespace ROG\States;

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
    //$actions[] = 'actDeliver';
    $args = [
      'a' => $actions,
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

    Notifications::message("Delivering...");

    $this->gamestate->nextState('next');
  }

  function stConfirmChoices()
  {
    $this->gamestate->nextState('');
  }
}
