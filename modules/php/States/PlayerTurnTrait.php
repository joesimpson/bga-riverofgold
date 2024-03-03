<?php

namespace ROG\States;

use ROG\Core\Notifications;

trait PlayerTurnTrait
{
   
  public function actBuild()
  { 
    self::checkAction('actBuild'); 
    self::trace("actBuild()");

    Notifications::message("Building...");

    $this->gamestate->nextState('build');
  }

  public function actSail()
  { 
    self::checkAction('actSail'); 
    self::trace("actSail()");

    Notifications::message("Sailing...");

    $this->gamestate->nextState('next');
  }
  
  public function actDeliver()
  { 
    self::checkAction('actDeliver'); 
    self::trace("actDeliver()");

    Notifications::message("Delivering...");

    $this->gamestate->nextState('next');
  }
}
