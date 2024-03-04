<?php

namespace ROG\States;

use ROG\Core\Notifications;

trait PlayerTurnTrait
{
   
  public function actBuild()
  { 
    self::checkAction('actBuild'); 
    self::trace("actBuild()");

    $this->addStep();

    Notifications::message("Building...");

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
