<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Players;
use ROG\Models\Player;

trait DivineFavorTrait
{
   
  public function argSpendFavor()
  { 
    $activePlayer = Players::getActive();
    $possibles = $this->listPossibleDieFacesToBuy($activePlayer);
    $args = [
      'p' => $possibles,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $dieFace
   */
  public function actDFSelect($dieFace)
  { 
    self::checkAction('actDFSelect'); 
    self::trace("actDFSelect($dieFace)");

    $player = Players::getCurrent();
    $pId = $player->id;
    $this->addStep();
    
    $cost = $this->canChangeDie($player,$dieFace);
    if(!isset($cost)){
      throw new UnexpectedException(405,"You cannot change the die to $dieFace");
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
  public function listPossibleDieFacesToBuy($player)
  { 
    $possibleFaces = [];
    foreach (DIE_FACES as $dieFace) {
      $cost = $this->canChangeDie($player,$dieFace);
      if(isset($cost)){
        $possibleFaces[] = ['face' => $dieFace, 'cost' => $cost] ;
      }
    }
    return $possibleFaces;
  }
  
  /**
   * @param Player $player
   * @param int $dieFace
   * @return int cost if this player can change their die roll to the specified face by spending divine favor,
   * null otherwise
   * 
   */
  public function canChangeDie($player,$dieFace)
  { 
    if(!in_array($dieFace,DIE_FACES)) return null;
    $nbFaces = count(DIE_FACES);
    $currentDie = $player->getDie();
    if($currentDie == $dieFace) return null;
    $diff = abs( $currentDie - $dieFace );
    $cost = $diff;
    if($cost > $nbFaces/2){
      //Check if it is cheaper to go through 6/1 (eg  when 1->6 or 2->6)
      $cost = $nbFaces - $cost;
    }
    $favor = $player->getResource(RESOURCE_TYPE_SUN);
    if($cost > $favor) return null;

    return $cost;
  }

}
;