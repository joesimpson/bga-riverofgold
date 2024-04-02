<?php

namespace ROG\States;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Models\Meeple;
use ROG\Models\Player;

trait BonusUpgradeShipTrait
{
   
  public function argBonusUpgrade()
  { 
    $activePlayer = Players::getActive();
    $possibles = $this->listPossibleShipsToUpgrade($activePlayer);
    $args = [
      'p' => $possibles,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $shipId
   */
  public function actBonusUpgrade($shipId)
  { 
    self::checkAction('actBonusUpgrade'); 
    self::trace("actBonusUpgrade($shipId)");

    $player = Players::getCurrent();
    $this->addStep();

    $ship = Meeples::get($shipId);
    if($ship->getPId() != $player->getId() || !$this->isPossibleShipToUpgrade($player,$ship)){
      throw new UnexpectedException(405,"You cannot upgrade this ship");
    }

    $ship->setType(MEEPLE_TYPE_SHIP_ROYAL);
    Notifications::upgradeShip($player,$ship);
    
    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @return array of meeples id
   */
  public function listPossibleShipsToUpgrade($player)
  { 
    $possibles = [];
    $royalShip = $player->getRoyalShip();
    if(isset($royalShip)) return $possibles;
    $boats = Meeples::getBoats($player->getId());
    foreach($boats as $boat){
      if($this->isPossibleShipToUpgrade($player,$boat)){
        $possibles[] = $boat->getId();
      }
    }
    return $possibles;
  }
  
  /**
   * @param Player $player
   * @param Meeple $ship
   * @return bool true if this player can upgrade this ship,
   * false otherwise
   * 
   */
  public function isPossibleShipToUpgrade($player,$ship)
  { 
    if(MEEPLE_TYPE_SHIP_ROYAL == $ship->getType()) return false;
    if(MEEPLE_LOCATION_RIVER != $ship->getLocation()) return false;

    return true;
  }

}
;