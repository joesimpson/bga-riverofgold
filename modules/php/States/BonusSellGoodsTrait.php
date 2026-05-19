<?php

namespace ROG\States;

use Bga\GameFramework\Actions\Types\IntParam;
use Bga\GameFramework\States\PossibleAction;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Players;
use ROG\Models\Player;

trait BonusSellGoodsTrait
{
   
  public function argBonusSellGoods()
  { 
    $activePlayer = Players::getActive();
    $possibles = $this->listPossibleGoodsToSell($activePlayer);
    $args = [
      'p' => $possibles,
    ];
    $this->addArgsForUndo($args);
    return $args;
  } 
   
  /**
   */
  #[PossibleAction]
  public function actStop(int $version)
  { 
    $this->checkVersion($version);
    self::checkAction('actStop'); 
    self::trace("actStop()");
    $this->addStep();
    $this->gamestate->nextState('next');
  } 

  /**
   * @param int $resourceType
   */
  #[PossibleAction]
  public function actSell(
    #[IntParam(name: 'src')] int $resourceType,
    int $version,
  )
  { 
    $this->checkVersion($version);
    self::checkAction('actSell'); 
    self::trace("actSell($resourceType)");

    $player = Players::getCurrent();
    $this->addStep();

    if(!$this->canSellResource($player,$resourceType)){
      throw new UnexpectedException(405,"You cannot sell this resource ($resourceType)");
    }
    
    $player->giveResource(-1,$resourceType);
    $player->giveResource(NB_MONEY_FOR_SELLING_MERCHANT_4,RESOURCE_TYPE_MONEY);

    $this->gamestate->nextState('continue');
  } 

  /**
   * @param Player $player
   * @return array of ['src' => $typeSrc, 'dest' => $typeDest] ;
   */
  public function listPossibleGoodsToSell($player)
  { 
    $possibles = [];
    foreach ([RESOURCE_TYPE_SILK,RESOURCE_TYPE_RICE, RESOURCE_TYPE_POTTERY] as $res) {
      if( $this->canSellResource($player,$res)){
        $possibles[] = [
          'src' => [$res => NB_RESOURCE_FOR_SELLING_MERCHANT_4],
          'dest' => [RESOURCE_TYPE_MONEY => NB_MONEY_FOR_SELLING_MERCHANT_4 
        ]] ;
      }
    }
    return $possibles;
  }
  
  /**
   * @param Player $player
   * @param int $resourceType
   * @return bool true if this player can sell another resource of this type,
   * false otherwise
   * 
   */
  public function canSellResource($player,$resourceType)
  { 
    if(!in_array($resourceType,[RESOURCE_TYPE_SILK, RESOURCE_TYPE_POTTERY,RESOURCE_TYPE_RICE] )) return false;
    $current = $player->getResource($resourceType);
    if($current < NB_RESOURCE_FOR_SELLING_MERCHANT_4) return false;

    return true;
  } 
}