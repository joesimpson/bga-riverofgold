<?php

namespace ROG\States;

use ROG\Core\Notifications;

trait PlayerTurnTrait
{
   
  public function argPlayerTurn()
  { 
    $args = [
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

    Notifications::message("Sailing...");

    $this->gamestate->nextState('next');
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
