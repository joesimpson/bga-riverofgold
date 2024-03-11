<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Players;
use ROG\Models\Player;

trait TradeTrait
{
   
  public function argTrade()
  { 
    $activePlayer = Players::getActive();
    $possibles = $this->listPossibleTrades($activePlayer);
    $args = [
      'p' => $possibles,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $typeSrc
   * @param int $typeDest
   */
  public function actTradeSelect($typeSrc,$typeDest)
  { 
    self::checkAction('actTradeSelect'); 
    self::trace("actTradeSelect($typeSrc,$typeDest)");

    $player = Players::getCurrent();
    $pId = $player->id;
    $this->addStep();
    
    if(!$this->canTrade($player,$typeSrc, $typeDest)){
      throw new UnexpectedException(405,"You cannot do that trade");
    }
    $qtySrc = RESOURCES_TO_TRADE[$typeSrc]['src'];
    $qtyDest = RESOURCES_TO_TRADE[$typeDest]['dest'];
    if( RESOURCE_TYPE_MONEY== $typeSrc){
      Players::spendMoney($player,-$qtySrc);
    }
    else {
      $player->giveResource(-$qtySrc,$typeSrc);
    }
    $player->giveResource(+$qtyDest,$typeDest);

    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @return array of ['src' => $typeSrc, 'dest' => $typeDest] ;
   */
  public function listPossibleTrades($player)
  { 
    $possibleTrades = [];
    foreach (RESOURCES_TO_TRADE as $typeSrc => $quantity1) {
      foreach (RESOURCES_TO_TRADE as $typeDest => $quantity2) {
        if($this->canTrade($player,$typeSrc, $typeDest)){
            $possibleTrades[] = ['src' => [$typeSrc =>$quantity1['src'] ], 'dest' => [$typeDest =>$quantity2['dest'] ]] ;
        }
      }
  }
    return $possibleTrades;
  }
  
  /**
   * @param Player $player
   * @param int $typeSrc
   * @param int $typeDest
   * @return bool true if this player can trade resources from type SRC to DEST
   */
  public function canTrade($player,$typeSrc, $typeDest)
  { 
    if($typeSrc == $typeDest) return false;
    if(RESOURCE_TYPE_MONEY == $typeSrc && RESOURCE_TYPE_SUN != $typeDest ) return false;
    if(RESOURCE_TYPE_MONEY != $typeSrc && RESOURCE_TYPE_SUN == $typeDest ) return false;
    if(RESOURCE_TYPE_SUN == $typeSrc ) return false;
    if(RESOURCE_TYPE_MONEY == $typeDest ) return false;

    $currentSrc = $player->getResource($typeSrc);
    if($currentSrc < RESOURCES_TO_TRADE[$typeSrc]['src']) return false;

    $currentDest = $player->getResource($typeDest);
    if($currentDest >= NB_MAX_RESOURCE) return false;

    return true;
  }

}
;