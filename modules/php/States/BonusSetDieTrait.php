<?php

namespace ROG\States;

use Bga\GameFramework\Actions\Types\IntParam;
use Bga\GameFramework\States\PossibleAction;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Players;
use ROG\Models\Player;

trait BonusSetDieTrait
{
   
  public function argBonusSetDie()
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
   * @param int $dieFace
   */
  #[PossibleAction]
  public function actBonusSetDie(#[IntParam(name: 'd')] int $dieFace, int $version)
  { 
    $this->checkVersion($version);
    self::checkAction('actBonusSetDie'); 
    self::trace("actBonusSetDie($dieFace)");

    $player = Players::getCurrent();
    $this->addStep();
    
    $cost = $this->canSetDie($player,$dieFace);
    if(!isset($cost)){
      throw new UnexpectedException(405,"You cannot set the die to $dieFace");
    } 
    $player->giveResource(-$cost,RESOURCE_TYPE_SUN);
    $player->setDie($dieFace);
    $player->setSkipRollDie(true);
    Notifications::setDieFace($player,$dieFace);

    $this->gamestate->nextState('next');
  } 
 
}