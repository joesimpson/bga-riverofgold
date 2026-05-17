<?php

//namespace ROG\States;
namespace Bga\Games\RiverOfGoldNightMarket\States;

use RiverOfGoldNightMarket;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\StateType;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Log;
use ROG\Managers\Players;
use ROG\Models\Player;

class BonusMultiTrades extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_BONUS_MULTI_TRADES,
      type: StateType::ACTIVE_PLAYER,
    );
  }

  /**
   * Game state arguments
   *
   */
  public function getArgs(): array
  {

    $player = Players::getActive();
    $currentBonus = Globals::getCurrentBonus();
    $currentBonusDatas = Globals::getCurrentBonusDatas();

    $canSkip = true;
    $trades = [];
    $possibleTrades = [];
    $nbTrades = 0;
    if(isset($currentBonusDatas)){
      $nbTrades = $currentBonusDatas['bonusQuantity'];
    }
    //FILTER on bonus type :
    switch($currentBonus){
      case BONUS_TYPE_MULTITRADE_2:
        $trades = [
          BONUS_TYPE_POINTS =>  [ 'amount' => 2, ] ,
          BONUS_TYPE_CHOICE =>  [ 'amount' => 1,
            'selection' => [
              RESOURCE_TYPE_SILK,
              RESOURCE_TYPE_RICE,
              RESOURCE_TYPE_POTTERY, 
            ],
          ],
          RESOURCE_TYPE_MONEY => [ 'amount' => 3, ] ,
        ];
        break;
      case BONUS_TYPE_MULTITRADE_3:
        $trades = [
          BONUS_TYPE_POINTS =>  [ 'amount' => 3, ] ,
          BONUS_TYPE_CHOICE =>  [ 'amount' => 1,
            'selection' => [
              RESOURCE_TYPE_SILK,
              RESOURCE_TYPE_RICE,
              RESOURCE_TYPE_POTTERY, 
            ],
          ],
          RESOURCE_TYPE_MONEY => [ 'amount' => 3, ] ,
        ];
        break;
    }

    if($nbTrades < 1){
      $trades = [];
    }
    
    // FILTER ON player min/max 
    $possibleSrc = [];
    $possibleDest = [];
    foreach ($trades as $key => $datas) {
      $typeSrc = $key;
      $amount = $datas['amount'];
      $selection = [$typeSrc];
      if(array_key_exists('selection',$datas)){
        $selection = $datas['selection'];
        
      }
      foreach ($selection as $typeS) {
        if($player->canSpendResource($typeS,$amount)) {
          $possibleSrc[$typeS] = [$typeS => $amount, 'from' => $selection ];
        }
        if($player->canReceiveResource($typeS,$amount)) {
          $possibleDest[$typeS] = [$typeS =>  $amount, 'from' => $selection];
        }
      }
    }
    //merge results
    foreach ($possibleSrc as $typeSrc => $datasSrc) {
      foreach ($possibleDest as $typeDest => $datasDest) {
        if($typeDest == $typeSrc) continue;
        if(in_array($typeSrc, $datasDest['from'])) continue;
        if(in_array($typeDest, $datasSrc['from'])) continue;
        
        //unset($datasSrc['from']);
        //unset($datasDest['from']);
        $possibleTrades[] = [
          'src' => ['type'=>$typeSrc, 'amount'=>$datasSrc[$typeSrc]], 
          'dest' => ['type'=>$typeDest, 'amount'=>$datasDest[$typeDest]],
        ];
      }
    }

    $args = [
      'skip' => $canSkip,
      'c' => $currentBonus,
      'nb' => $nbTrades,
      'trades' => $possibleTrades,
    ];

    $this->game->addArgsForUndo($args);
    return $args;
  }     
  
  public function onEnteringState(int $activePlayerId, array $args) {
    $this->game->trace(__CLASS__.".".__FUNCTION__."($activePlayerId)");
    
    $nbTrades = $args['nb'];
    if($nbTrades < 1) return ST_BONUS_CHOICE;
    
  }

  /**
   * Player action
   */
  #[PossibleAction]
  public function actMultiTrade(
      int $src, 
      int $dest, 
      int $version,
      int $activePlayerId, array $args,
    )
  {
    $this->game->checkVersion($version);
    $this->game->trace(__CLASS__.".".__FUNCTION__."( $src, $dest, $activePlayerId)");
    
    $player = Players::get($activePlayerId);
    $trades = $args['trades'];
    $found = null;
    foreach($trades as $tradeDatas){
      if(intval($tradeDatas['src']['type']) == $src && intval($tradeDatas['dest']['type']) == $dest){
        $found = $tradeDatas;
      }
    }
    if (!isset($found)) {
      throw new UnexpectedException(503,"Invalid src $src / dest $dest, see ".json_encode($trades));
    } 

    // game logic  
    Log::addStep();

    $currentBonusDatas = Globals::getCurrentBonusDatas();

    $srcDatas = $found['src'];
    $destDatas = $found['dest'];
    $qtySrc = $srcDatas['amount'];
    $qtyDest = $destDatas['amount'];

    switch($src){
      case BONUS_TYPE_POINTS:
        $player->addPoints(-$qtySrc);
        break;
      default:
        $player->giveResource(-$qtySrc,$src);
        break;
    }
    switch($dest){
      case BONUS_TYPE_POINTS:
        $player->addPoints(+$qtyDest);
        Players::claimScoreMasteries($player);
        break;
      default:
        $player->giveResource(+$qtyDest,$dest);
        break;
    }

    $currentBonusDatas['bonusQuantity']--;
    Globals::setCurrentBonusDatas($currentBonusDatas);

    //stay in state until all trades are done
    return self::class;
  }

  /**
   * Player action
   */
  #[PossibleAction]
  public function actSkipBonuses(
      int $version,
      int $activePlayerId, array $args,
    )
  {
    $this->game->checkVersion($version);
    $this->game->trace(__CLASS__.".".__FUNCTION__."($activePlayerId)");
    
    Log::addStep();

    if(!$args['skip']){
      throw new UnexpectedException(405,"You should not skip these bonuses !");
    }

    Globals::setCurrentBonus(null);
    Globals::setCurrentBonusDatas(null);

    return ST_BONUS_CHOICE;
  }

  function zombie(int $playerId, array $args) {
    $this->game->trace(__CLASS__.".".__FUNCTION__."($playerId)");
    return ST_BONUS_CHOICE;
  }

  
  /**
   * Player action : undo ALL
   *
   * @throws UnexpectedException
   */
  #[PossibleAction]
  public function actRestart(int $version,)
  {
    $this->game->checkVersion($version);
    $this->game->processRestartTurn();
  }
  
  /**
   * Player action : undo last step
   *
   * @throws UnexpectedException
   */
  #[PossibleAction]
  public function actUndoToStep(int $stepId, int $version,)
  {
    $this->game->checkVersion($version);
    $this->game->processUndoToStep($stepId);
  }
 
}