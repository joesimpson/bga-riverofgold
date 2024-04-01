<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Players;

trait BeforeTurnTrait
{
   
  public function stBeforeTurn()
  { 
    self::trace("stBeforeTurn()");
    
    $player = Players::getActive();
    $playerPatron = $player->getPatron();
    if(isset($playerPatron) && PATRON_DARLING == $playerPatron->getType()){
      //IN THIS CASE ONLY We stay in current state to make a choice
      //TODO JSA turn 1 only because after the choice to roll the die is already done
      return;
    }

    $this->addCheckpoint(ST_PLAYER_TURN);
    $this->gamestate->nextState('next');
  }
  

  public function argBeforeTurn()
  { 
    $activePlayer = Players::getActive();
    $possibles = $this->listPossibleDieFacesToSet($activePlayer);
    $args = [
      'p' => $possibles,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
  /**
   * Skip selection
   */
  public function actSkip()
  { 
    self::checkAction('actSkip'); 
    self::trace("actSkip()");
    
    $player = Players::getCurrent();
    $player->rollDie();
    
    $this->addCheckpoint(ST_PLAYER_TURN);
    $this->gamestate->nextState('next');
  }
  
  /**
   * @param int $dieFace
   */
  public function actDarlingFavor($dieFace)
  { 
    self::checkAction('actDarlingFavor'); 
    self::trace("actDarlingFavor($dieFace)");

    $player = Players::getCurrent();
    $this->addStep();
    
    $cost = $this->canSetDie($player,$dieFace);
    if(!isset($cost)){
      throw new UnexpectedException(405,"You cannot set the die to $dieFace");
    } 

    $player->giveResource(-$cost,RESOURCE_TYPE_SUN);
    $player->setDie($dieFace);
    Notifications::setDieFace($player,$dieFace);

    $this->gamestate->nextState('next');
  } 
  
  /**
   * @param Player $player
   * @return array of ['face' => $dieFace, 'cost' => $cost] ;
   */
  public function listPossibleDieFacesToSet($player)
  { 
    $possibleFaces = [];
    foreach (DIE_FACES as $dieFace) {
      $cost = $this->canSetDie($player,$dieFace);
      if(isset($cost)){
        $possibleFaces[] = ['face' => $dieFace, 'cost' => $cost] ;
      }
    }
    return $possibleFaces;
  }
  
  /**
   * @param Player $player
   * @param int $dieFace
   * @return int cost if this player can set their die roll to the specified face by spending 1 divine favor,
   * null otherwise
   * 
   */
  public function canSetDie($player,$dieFace)
  { 
    if(!in_array($dieFace,DIE_FACES)) return null;
    $currentDie = $player->getDie();
    if($currentDie == $dieFace) return null;
    $cost = DARLING_FAVOR_COST;
    $favor = $player->getResource(RESOURCE_TYPE_SUN);
    if($cost > $favor) return null;

    return $cost;
  }
}
