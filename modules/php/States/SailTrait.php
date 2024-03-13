<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
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
    foreach($adjacentSpaces as $adjacentSpace){
      $tile = Tiles::getTileOnShoreSpace($adjacentSpace);
      if(!isset($tile)){
        Players::giveMoney($player,EMPTY_SPACE_REWARD);
      }
      else {
        $shoreSpace = ShoreSpaces::getShoreSpace($tile->getPosition());
        $region = $shoreSpace->region;

        $rewards = $tile->visitorReward;
        foreach($rewards->entries as $reward){
          //TODO JSA if market AND noble 1 AND royal ship: replace reward resource type by BONUS_TYPE_CHOICE
          $reward->rewardPlayer($player,$region);
        }
        //TODO JSA SAIling : owner rewards
        //TODO JSA SAIling : royal ship rewards 
      }
    }

    Players::claimMasteries($player);

    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @return array  ['shipId' => ['positions'],'shipId' => ['positions']]
   */
  public function listPossibleSpacesToSail($player)
  { 
    $nbMoves = $player->getDie();
    //TODO JSA canSail for noble 5 AND royal ship : +1/-1
    $possibleSpaces = [];
    $boats = Meeples::getBoats($player->getId());
    foreach($boats as $boat){
      //ship position is between 1 and NB_RIVER_SPACES, and comes back at 1 after completing the journey
      $possibleSpaces[$boat->getId()][] = ($boat->getPosition() + $nbMoves -1) % NB_RIVER_SPACES +1;
    }
    return $possibleSpaces;
  }

}
