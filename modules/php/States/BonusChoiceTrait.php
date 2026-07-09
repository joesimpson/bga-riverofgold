<?php

namespace ROG\States;

use Bga\GameFramework\Actions\Types\IntParam;
use Bga\GameFramework\States\PossibleAction;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Collection;
use ROG\Helpers\Log;
use ROG\Managers\Cards;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Models\Player;

trait BonusChoiceTrait
{
   
  public function stBonusChoice()
  {  
    self::trace("stBonusChoice()");
    $activePlayer = Players::getActive();
    Globals::setCurrentBonus(null);
    $args = $this->argBonusChoice();
    $nbPossibleActions = count($args['p']);
    $nbPossiblePrivateActions = count($args['_private'][$activePlayer->getId()]['p']);
    if($nbPossibleActions == 0 && $nbPossiblePrivateActions == 0){
      $this->gamestate->nextState('next');
      return;
    }
  } 
  public function argBonusChoice()
  { 
    $activePlayer = Players::getActive();
    $possibles = $activePlayer->filterPossibleBonuses(true);
    $privateBonuses = $activePlayer->filterPossibleBonuses(false);
    $trade = false;
    if(count($this->listPossibleTrades($activePlayer))>0 ){
      $trade = true;
    }
    $cannotSetDie = count($this->listPossibleDieFacesToSet($activePlayer)) ==0;
    $args = [
      'p' => $possibles,
      '_private' => [ 
        $activePlayer->getId() => [
          'p' => $privateBonuses,
        ],
      ],
      //'_merge_private' => true,
      'trade' => $trade,
      'canSkip' => $this->canSkipBonuses($activePlayer),
      'cannotSetDie' => $cannotSetDie,
    ];
    $this->addArgsForPlayCards($args,$activePlayer);
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   */
  #[PossibleAction]
  public function actSkipBonuses(
    int $version,
  )
  { 
    $this->checkVersion($version);
    self::checkAction('actSkipBonuses'); 
    self::trace(__CLASS__.".".__FUNCTION__."()");
    Log::addStep();
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
  #[PossibleAction]
  public function actBonus(
    int $version, 
    #[IntParam(name: 't')] int $bonusType,
    ?int $bonusKey = null,
  )
  { 
    $this->checkVersion($version);
    self::checkAction('actBonus'); 
    self::trace("actBonus($bonusType,$bonusKey)");

    $player = Players::getCurrent();
    $this->addStep();

    if(!$this->canSelectBonusType($player,$bonusType,$bonusKey)){
      throw new UnexpectedException(405,"You don't have this bonus $bonusType (#$bonusKey)");
    }

    Globals::setCurrentBonus($bonusType);
    $removeDatas = Globals::removeBonus($player,$bonusType,$bonusKey);
    Globals::setCurrentBonusDatas($removeDatas);

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
        $missingInDeck = Cards::drawCardsToHand($player,1);
        if($missingInDeck==0){
          //ACTION IS NOT UNDOABLE
          $this->addCheckpoint(ST_DISCARD_CARD);
        }
        else {
          //Don't force a discard in this case
          $nextState = 'continue';
        }
        break;
      case BONUS_TYPE_INC_HAND_LIMIT:
        $nextState = 'continue';
        $missingInDeck = Cards::drawCardsToHand($player,1);
        if($missingInDeck==0){
          //ACTION IS NOT UNDOABLE
          $this->addCheckpoint(ST_BONUS_CHOICE);
        }
        break;
      case BONUS_TYPE_REFILL_HAND://Draw 2 and discard 1
        $nextState = 'bonusDraw';
        Notifications::refillHand($player);
        $missingInDeck = Cards::drawCardsToHand($player,2);
        if($missingInDeck < 2 ){
          //ACTION IS NOT UNDOABLE
          $this->addCheckpoint(ST_DISCARD_CARD);
        }
        if($missingInDeck > 0){
          //Don't force a discard in this case
          $nextState = 'continue';
        }
        break;
      case BONUS_TYPE_SET_DIE:
        $nextState = 'bonusSetDie';
        break;
      case BONUS_TYPE_PLACE_LION:
        $nextState = 'bonusPlaceLion';
        break;
      case BONUS_TYPE_ANY_OWNER_REWARD:
      case BONUS_TYPE_BUILDING_REWARD:
        $nextState = 'bonusBuildingReward';
        break;
      case BONUS_TYPE_PAY_SHIPS:
        $nextState = 'bonusPayShips';
        break;
      case BONUS_TYPE_ADVANCE_OR_POINTS:
        $nextState = 'bonusAdvanceCity';
        break;
      case BONUS_TYPE_INF_SELECT_REGION:
      case BONUS_TYPE_REWARDS_SELECT_REGION:
        $nextState = 'bonusSelectRegion';
        break;
      case BONUS_TYPE_MULTITRADE_2:
      case BONUS_TYPE_MULTITRADE_3:
      case BONUS_TYPE_TRADE_KOKU:
      case BONUS_TYPE_TRADE_POINTS:
        $nextState = 'bonusMultiTrades';
        break;
      case BONUS_TYPE_MANAGE_DEBT:
      case BONUS_TYPE_REMOVE_GOODS:
        $nextState = 'bonusManageCardResources';
        break;
      case BONUS_TYPE_DELIVER_TOP_DECK:
        $nextState = 'continue';
        $cards = Cards::drawCardsToDeliver($player,1);
        if(count($cards) > 0){
          //ACTION IS NOT UNDOABLE
          $this->addCheckpoint(ST_BONUS_CHOICE);
        }
        break;
      case BONUS_TYPE_BUILD_NEAR_SHIPS:
        $nextState = 'bonusFreeBuild';
        break;
      case BONUS_TYPE_FREE_SAIL:
        $nextState = 'bonusFreeSail';
        break;
      case BONUS_TYPE_CITY_CARD_DRAW:
        $nextState = 'bonusCityDraw';
        //checkpoint because we reveal cards
        $this->addCheckpoint(ST_BONUS_CITY_DRAW);
        break;
      case BONUS_TYPE_REVEAL_CARD:
        //Will be played as a card
      default:
        throw new UnexpectedException(900,"Not supported bonus type $bonusType");
    }

    $this->gamestate->nextState($nextState);
  } 

  public function listPossibleBonusTypes(Player $player) : array
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
  public function canSelectBonusType(Player $player,int $bonusType, ?int $bonusKey = null) : bool
  { 
    $bonuses = $this->listPossibleBonusTypes($player);
    if(isset($bonusKey)){
      if(!array_key_exists('datas',$bonuses) ) return false;
      if(!array_key_exists($bonusType,$bonuses['datas']) ) return false;
      if(!array_key_exists($bonusKey, $bonuses['datas'][$bonusType]) ) return false;
    }
    else {
      if(!in_array($bonusType,$bonuses) ) return false;
    }

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
    if(in_array(BONUS_TYPE_PLACE_LION,$bonuses) && !empty(ShoreSpaces::getAllEmptySpaces())){
      return false;
    }
    return true;
  }
}
;