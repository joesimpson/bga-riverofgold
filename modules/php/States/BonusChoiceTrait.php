<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Players;
use ROG\Models\Player;

trait BonusChoiceTrait
{
   
  public function argBonusChoice()
  { 
    $activePlayer = Players::getActive();
    $possibles = $this->listPossibleBonusTypes($activePlayer);
    $args = [
      'p' => $possibles,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $bonusType
   */
  public function actBonus($bonusType)
  { 
    self::checkAction('actBonus'); 
    self::trace("actBonus($bonusType)");

    $player = Players::getCurrent();
    $this->addStep();

    if(!$this->canSelectBonusType($player,$bonusType)){
      throw new UnexpectedException(405,"You don't have this bonus $bonusType");
    }

    switch($bonusType){
      case BONUS_TYPE_CHOICE:
        $nextState = 'bonusResource';
        break;
      case BONUS_TYPE_UPGRADE_SHIP:
        $nextState = 'bonusUpgrade';
        break;
      default:
        throw new UnexpectedException(900,"Not supported bonus type $bonusType");
    }

    $this->gamestate->nextState($nextState);
  } 

  /**
   * @param Player $player
   * @return array of int
   */
  public function listPossibleBonusTypes($player)
  { 
    return Globals::getBonuses();
  }
  
  /**
   * @param Player $player
   * @param int $bonusType
   * @return bool true 
   * false otherwise
   * 
   */
  public function canSelectBonusType($player,$bonusType)
  { 
    $bonuses = $this->listPossibleBonusTypes($player);
    if(!in_array($bonusType,$bonuses) ) return false;

    return true;
  }

}
;