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

class BonusSelectRegion extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_BONUS_SELECT_REGION,
      type: StateType::ACTIVE_PLAYER,
    );
  }

  /**
   * Game state arguments
   *
   */
  public function getArgs(): array
  {

    $currentBonus = Globals::getCurrentBonus();
    $currentBonusDatas = Globals::getCurrentBonusDatas();

    $regions = REGIONS;
    //FILTER on bonus type :
    switch($currentBonus){
      case BONUS_TYPE_INF_SELECT_REGION:
        $from = $currentBonusDatas['region'];
        $regions = [];
        foreach(REGIONS as $region){
            //REMOVE previous region from the list
            if($region == $from) continue;
            $regions[] = $region;
        }
        break;
    }

    $args = [
      'c' => $currentBonus,
      'cbd' => $currentBonusDatas,
      'p' => $regions,
    ];

    $this->game->addArgsForUndo($args);
    return $args;
  }     
  
  public function onEnteringState(int $activePlayerId, array $args) {
    $this->game->trace(__CLASS__.".".__FUNCTION__."($activePlayerId)");
  }

  /**
   * Player action
   */
  #[PossibleAction]
  public function actSelectRegion(
      int $choice, 
      int $version,
      int $activePlayerId, array $args,
    )
  {
    $this->game->checkVersion($version);
    $this->game->trace(__CLASS__.".".__FUNCTION__."( $choice, $activePlayerId)");
    
    $player = Players::get($activePlayerId);
    $choices = $args['p'];
    if (!in_array($choice,$choices)) {
      throw new UnexpectedException(503,"Invalid choice $choice");
    } 

    // game logic  
    Log::addStep();

    $currentBonus = $args['c'];
    $currentBonusDatas = $args['cbd'];
    $region = $choice;
    switch($currentBonus){
      case BONUS_TYPE_INF_SELECT_REGION:
        Players::gainInfluence($player, $region, $currentBonusDatas['bonusQuantity']);
        Players::claimMasteries($player);
        break;
    }

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