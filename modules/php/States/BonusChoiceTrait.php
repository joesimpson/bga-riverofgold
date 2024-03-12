<?php

namespace ROG\States;

use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Players;
use ROG\Models\Player;

trait BonusChoiceTrait
{
   
  public function argBonusChoice()
  { 
    $activePlayer = Players::getActive();
    $possibles = $this->listPossibleBonusResources($activePlayer);
    $args = [
      'p' => $possibles,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   * @param int $resourceType
   */
  public function actBonus($resourceType)
  { 
    self::checkAction('actBonus'); 
    self::trace("actBonus($resourceType)");

    $player = Players::getCurrent();
    $this->addStep();
    
    $player->giveResource(1,$resourceType);

    $this->gamestate->nextState('next');
  } 

  /**
   * @param Player $player
   * @return array of int
   */
  public function listPossibleBonusResources($player)
  { 
    $possibles = [];
    foreach ([RESOURCE_TYPE_SILK,RESOURCE_TYPE_RICE, RESOURCE_TYPE_POTTERY] as $res) {
      if( $this->canReceiveResource($player,$res)){
        $possibles[] = $res;
      }
    }
    return $possibles;
  }
  
  /**
   * @param Player $player
   * @param int $resourceType
   * @return bool true if this player can receive another resource of this type,
   * false otherwise
   * 
   */
  public function canReceiveResource($player,$resourceType)
  { 
    if(!in_array($resourceType,[RESOURCE_TYPE_SILK, RESOURCE_TYPE_POTTERY,RESOURCE_TYPE_RICE] )) return false;
    $max = NB_MAX_RESOURCE;
    if(array_key_exists($resourceType,RESOURCES_LIMIT) ) {
      $max = RESOURCES_LIMIT[$resourceType];
    }
    $current = $player->getResource($resourceType);
    if($current >=$max) return false;

    return true;
  }

}
;