<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Models\Meeple;

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

    //TODO JSA SAIling : visitor rewards
    //TODO JSA SAIling : owner rewards
    //TODO JSA SAIling : royal ship rewards 


    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @return array  ['shipId' => ['positions'],'shipId' => ['positions']]
   */
  public function listPossibleSpacesToSail($player)
  { 
    $nbMoves = $player->getDie();
    //TODO JSA canSail for noble 5 : +1/-1
    $possibleSpaces = [];
    $boats = Meeples::getBoats($player->getId());
    foreach($boats as $boat){
      //ship position is between 1 and NB_RIVER_SPACES, and comes back at 1 after completing the journey
      $possibleSpaces[$boat->getId()][] = ($boat->getPosition() + $nbMoves -1) % NB_RIVER_SPACES +1;
    }
    return $possibleSpaces;
  }

}
