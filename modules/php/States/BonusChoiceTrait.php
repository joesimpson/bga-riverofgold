<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Cards;
use ROG\Managers\Players;
use ROG\Models\Player;

trait BonusChoiceTrait
{
   
  public function stBonusChoice()
  {  
    self::trace("stBonusChoice()");
    Globals::setCurrentBonus(null);
    $nbPossibleActions = count($this->argBonusChoice()['p']);
    if($nbPossibleActions == 0){
      $this->gamestate->nextState('next');
      return;
    }
  } 
  public function argBonusChoice()
  { 
    $activePlayer = Players::getActive();
    $possibles = $this->listPossibleBonusTypes($activePlayer);
    $trade = false;
    if(count($this->listPossibleTrades($activePlayer))>0 ){
      $trade = true;
    }
    $args = [
      'p' => $possibles,
      'trade' => $trade,
      'canSkip' => $this->canSkipBonuses($activePlayer),
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   */
  public function actSkipBonuses()
  { 
    self::checkAction('actSkipBonuses'); 
    self::trace("actSkipBonuses()");
    $this->addStep();
    $player = Players::getCurrent();
    if(!$this->canSkipBonuses($player)){ //$currentTurnPlayer == $player->getId()
      throw new UnexpectedException(405,"You should not skip these bonuses !");
    }
    $player->setBonuses([]);
    $this->gamestate->nextState('next');
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

    Globals::setCurrentBonus($bonusType);
    Globals::removeBonus($player,$bonusType);

    switch($bonusType){
      case BONUS_TYPE_CHOICE:
        $nextState = 'bonusResource';
        break;
      case BONUS_TYPE_UPGRADE_SHIP:
        $nextState = 'bonusUpgrade';
        break;
      case BONUS_TYPE_SECOND_MARKER_ON_BUILDING:
      case BONUS_TYPE_SECOND_MARKER_ON_OPPONENT:
        $nextState = 'bonusBuilding';
        break;
      case BONUS_TYPE_MONEY_OR_GOOD:
        $nextState = 'bonusMoneyOrGood';
        break;
      case BONUS_TYPE_SELL_GOODS:
        $nextState = 'bonusSellGoods';
        break;
      case BONUS_TYPE_DRAW:
        $nextState = 'bonusDraw';
        Cards::drawCardsToHand($player,1);
        //ACTION IS NOT UNDOABLE
        $this->addCheckpoint(ST_DISCARD_CARD);
        break;
      case BONUS_TYPE_REFILL_HAND://Draw 2 and discard 1
        $nextState = 'bonusDraw';
        Notifications::refillHand($player);
        Cards::drawCardsToHand($player,2);
        //ACTION IS NOT UNDOABLE
        $this->addCheckpoint(ST_DISCARD_CARD);
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
    return $player->getBonuses();
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

  
  /**
   * @param Player $player
   * @return bool true when player can skip,
   * false otherwise
   * 
   */
  public function canSkipBonuses($player)
  {  
    $bonuses = $player->getBonuses();
    //$currentTurnPlayer = Globals::getTurnPlayer();
    //$currentTurnPlayer == $player->getId()
    if(in_array(BONUS_TYPE_REFILL_HAND,$bonuses)){
      return false;
    }
    if(in_array(BONUS_TYPE_UPGRADE_SHIP,$bonuses)){
      return false;
    }
    return true;
  }
}
;