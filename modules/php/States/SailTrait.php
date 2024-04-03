<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;
use ROG\Models\Meeple;
use ROG\Models\BuildingTile;

trait SailTrait
{
   
  public function argSail()
  { 
    $activePlayer = Players::getActive();
    $possibleSpaces = $this->listPossibleSpacesToSail($activePlayer);
    $args = [
      'spaces' => $possibleSpaces,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $shipId
   * @param int $riverSpace
   */
  public function actSailSelect($shipId,$riverSpace)
  { 
    self::checkAction('actSailSelect'); 
    self::trace("actSailSelect($shipId,$riverSpace)");

    $player = Players::getCurrent();
    $this->addStep();

    $possibleSpaces = $this->listPossibleSpacesToSail($player);
    $possibleShips = array_keys($possibleSpaces);
    if(!in_array($shipId, $possibleShips)){
      throw new UnexpectedException(20,"You cannot Sail ship $shipId, see : ".json_encode($possibleShips));
    } 
    $possibleShipsDest = $possibleSpaces[$shipId];
    if(!in_array($riverSpace, $possibleShipsDest)){
      throw new UnexpectedException(21,"You cannot Sail to $riverSpace, see : ".json_encode($possibleShipsDest));
    } 
    $ship = Meeples::get($shipId);
    //TODO JSA SAIL : completing the journey - pause game to select bonus
    $ship->setPosition($riverSpace);
    Notifications::sail($player,$ship,$riverSpace);

    $adjacentSpaces = ShoreSpaces::getAdjacentSpaces($riverSpace);
    self::trace("actSailSelect($shipId,$riverSpace) adjacent spaces :".json_encode($adjacentSpaces));

    $players = Players::getAll();

    Notifications::message("Checking visitor rewards...");
    foreach($adjacentSpaces as $adjacentSpace){
      $tile = Tiles::getTileOnShoreSpace($adjacentSpace);
      if(!isset($tile)){
        Players::giveMoney($player,EMPTY_SPACE_REWARD);
      }
      else {
        $shoreSpace = ShoreSpaces::getShoreSpace($tile->getPosition());
        $region = $shoreSpace->region;

        //Visitor rewards
        $rewards = $tile->visitorReward;
        foreach($rewards->entries as $reward){
          //noble 1 Ongoing Ability :if market AND royal ship: replace reward resource type by a choice
          if(MEEPLE_TYPE_SHIP_ROYAL == $ship->getType() 
            && BUILDING_TYPE_MARKET == $tile->getBuildingType() 
            && in_array($reward->type,[RESOURCE_TYPE_SILK,RESOURCE_TYPE_POTTERY,RESOURCE_TYPE_RICE])
            && Cards::hasPlayerDeliveredOrder($player->getId(),CARD_NOBLE_1)
          ){
            Globals::addBonus($player,BONUS_TYPE_CHOICE);
          }
          else {
            $reward->rewardPlayer($player,$region);
          }
        }
      }
    }
    
    Notifications::message("Checking owner rewards...");
    $ownBuilding = false;
    $opponentBuilding = false;
    foreach($adjacentSpaces as $adjacentSpace){
      $tile = Tiles::getTileOnShoreSpace($adjacentSpace);
      if(isset($tile)){
        //Owner rewards : sometimes 2 owners (or 2 times the same)
        foreach($tile->ownerReward->entries as $reward){
          $clanMarkers = $tile->getMeeples();
          foreach($clanMarkers as $clanMarker){
            $owner = $players[$clanMarker->getPId()];
            $reward->rewardPlayer($owner,$region);
            $ownBuilding = $ownBuilding || $clanMarker->getPId() == $player->getId();
            $opponentBuilding = $opponentBuilding || $clanMarker->getPId() != $player->getId();
          }
        }
      }
    }

    //TODO JSA SAIling : royal ship rewards 
    if(MEEPLE_TYPE_SHIP_ROYAL == $ship->getType()){
      Notifications::message("Checking royal ship abilities...");
      //noble 4 Ongoing Ability : +1 point IF 1 or more opponent buildings
      if($opponentBuilding && Cards::hasPlayerDeliveredOrder($player->getId(),CARD_NOBLE_4)){
        $player->addPoints(NB_POINTS_NOBLE_4);
      }
      //noble 6 Ongoing Ability : +1 point IF 1 or more owned buildings
      if($ownBuilding && Cards::hasPlayerDeliveredOrder($player->getId(),CARD_NOBLE_6)){
        $player->addPoints(NB_POINTS_NOBLE_6);
      }
    }

    Players::claimMasteries($player);

    if(Globals::getBonuses()){
      //Manage bonus for active player
      $this->gamestate->nextState('bonus');
    }
    else {
      $this->gamestate->nextState('next');
    }
  } 

  /**
   * @param Player $player
   * @return array  ['shipId' => ['positions'],'shipId' => ['positions']]
   */
  public function listPossibleSpacesToSail($player)
  { 
    $nbMoves = $player->getDie();
    $possibleSpaces = [];
    $boats = Meeples::getBoats($player->getId());
    foreach($boats as $boat){
      //ship position is between 1 and NB_RIVER_SPACES, and comes back at 1 after completing the journey
      $diePosition = $boat->getPosition() + $nbMoves -1;
      $possibleSpaces[$boat->getId()][] = $diePosition % NB_RIVER_SPACES +1;
      if(MEEPLE_TYPE_SHIP_ROYAL == $boat->getType()){
        //Royal Ship: +1/-1 space
        if(Cards::hasPlayerDeliveredOrder($player->getId(), CARD_NOBLE_5)){
          $possibleSpaces[$boat->getId()][] = ($diePosition +1) % NB_RIVER_SPACES +1;
          $possibleSpaces[$boat->getId()][] = ($diePosition -1) % NB_RIVER_SPACES +1;
        }

      }
    }
    return $possibleSpaces;
  }

}
