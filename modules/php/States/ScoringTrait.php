<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\Meeple;

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
    //Query influence meeples before looping
    $influenceMarkers = [];
    $scoringTiles = Tiles::getInLocationOrdered(TILE_LOCATION_SCORING);
    foreach(REGIONS as $region){
      $influenceMarkers[$region] = Meeples::getAllInfluenceMarkers($region);
    }

    foreach($players as $pid => $player){
      //RULE 1 : REGIONAL INFLUENCE
      foreach(REGIONS as $region){
        $all = $influenceMarkers[$region];
        $opponentPositions = [];
        $playerPosition = 0;
        foreach($all as $meeple){
          if($meeple->getPId() == $pid) $playerPosition = $meeple->getPosition();
          else $opponentPositions[] = $meeple->getPosition();
        }
        $scoringTile = $scoringTiles->filter(function($tile) use ($region) {return $region == $tile->getRegion();})->first();
        if(!isset($scoringTile)) throw new UnexpectedException(404,"Missing scoring tile for region $region");
        $influenceScore = $scoringTile->computeScore($playerPosition,$opponentPositions);
        if($influenceScore>0){
          $player->addPoints($influenceScore,false);
          Notifications::scoreInfluence($player,$scoringTile,$region,$influenceScore,$playerPosition);
          
          //check Elder space to double influence score
          $elder = Meeples::getMarkerOnElderSpace($player->getId(),$region);
          if(isset($elder)){
            $player->addPoints($influenceScore,false);
            Notifications::scoreElder($player,$scoringTile,$region,$influenceScore);
          }
        }
      }

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

      //RULE 3 : CUSTOMER BONUSES : artisans, merchants, nobles
      //3.1 ARTISANS score remaining trade goods :
      $nbResources = $player->getResource(RESOURCE_TYPE_SILK) + $player->getResource(RESOURCE_TYPE_RICE)+ $player->getResource(RESOURCE_TYPE_POTTERY);
      $nbArtisans = $player->getNbDeliveredCustomerByType(CUSTOMER_TYPE_ARTISAN);
      $scoreForRemainingGoods = $nbArtisans * round( $nbResources / NB_RESOURCES_FOR_1POINT_WITH_ARTISAN,0,PHP_ROUND_HALF_DOWN);
      if($scoreForRemainingGoods>0) {
        $player->addPoints($scoreForRemainingGoods,false);
        Notifications::scoreArtisans($player,$nbArtisans,$nbResources,$scoreForRemainingGoods);
      }


      //TODO JSA TIE BREAKER : DIVINE FAVOR 
    }
  }

}
