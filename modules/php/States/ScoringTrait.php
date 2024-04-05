<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Managers\Players;

trait ScoringTrait
{
 
  //FOR TESTING PURPOSE
  public function stPreEndOfGame()
  {
    self::trace("stPreEndOfGame()");
    $this->gamestate->nextState('loopback');
  }

  public function stScoring()
  {
    self::trace("stScoring()");

    $players = Players::getAll();
    $this->computeFinalScore($players);

    $this->gamestate->nextState('next');
  }
  
  public function computeFinalScore($players)
  {
    self::trace("computeFinalScore()");
    Notifications::message("Computing final scoring...");
    foreach($players as $pid => $player){
      //RULE 1 : REGIONAL INFLUENCE

      //RULE 2 : CUSTOMERS
      $nbDeliveries = $player->getNbDeliveredCustomers();
      $scoreForNbDeliveries = 0;
      switch($nbDeliveries){
        case 0: $scoreForNbDeliveries = 0; break;
        case 1: $scoreForNbDeliveries = 2; break;
        case 2: $scoreForNbDeliveries = 5; break;
        case 3: $scoreForNbDeliveries = 9; break;
        case 4: $scoreForNbDeliveries = 14; break;
        case 5: $scoreForNbDeliveries = 20; break;
        case 6: 
        //Default for 6 or more
        default: $scoreForNbDeliveries = 27; break;
      }
      $player->addPoints($scoreForNbDeliveries,false);
      Notifications::scoreDeliveries($player,$scoreForNbDeliveries,$nbDeliveries);

      //RULE 3 : CUSTOMER BONUSES


      //TODO JSA TIE BREAKER : DIVINE FAVOR 
    }
  }

}
