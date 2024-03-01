<?php

namespace ROG\States;

use ROG\Core\Notifications;

trait PlayerTurnTrait
{
   
  public function actBuild()
  { 
    self::trace("actBuild()");

    Notifications::message("Building...");

    $this->gamestate->nextState('next');
  }

  public function actSail()
  { 
    self::trace("actSail()");

    Notifications::message("Sailing...");

    $this->gamestate->nextState('next');
  }
  
  public function actDeliver()
  { 
    self::trace("actDeliver()");

    Notifications::message("Delivering...");

    $this->gamestate->nextState('next');
  }
}
