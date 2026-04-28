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
use ROG\Managers\Tiles;
use ROG\Models\Player;

class BonusBuildingReward extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_BONUS_BUILDING_REWARD,
      type: StateType::ACTIVE_PLAYER,
    );
  }

  /**
   * Game state arguments
   *
   */
  public function getArgs(): array
  {

    //For now we manage only last built tile
    $lastBuiltTile = Globals::getLastBuiltTile();

    $args = [
      'p' => [
        $lastBuiltTile => [
          1, //=>owner reward
          2, // visitor reward
        ],
      ],

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
  public function actSelectReward(
      int $choice, 
      int $tileId, 
      int $version,
      int $activePlayerId, array $args,
      )
  {
    $this->game->checkVersion($version);
    $this->game->trace(__CLASS__.".".__FUNCTION__."( $choice, $activePlayerId)");
    
    $player = Players::get($activePlayerId);
    $tilesDatas = $args['p'];
    if (!in_array($tileId,array_keys($tilesDatas))) {
      throw new UnexpectedException(503,"Invalid tile $tileId");
    } 
    $choices = $tilesDatas[$tileId];
    if (!in_array($choice,$choices)) {
      throw new UnexpectedException(503,"Invalid choice $choice");
    } 

    // game logic  
    Log::addStep();
    $tile = Tiles::get($tileId);
    $region = $tile->getRegion();
    switch($choice){
      case 1://OWNER REWARD
        Notifications::buildingOwnerRewards($player,$tile);
        $rewards = $tile->ownerReward;
        foreach($rewards->entries as $reward){
          $reward->rewardPlayer($player,$region,$tile);
        }
        Players::claimMasteries($player);
        break;
      case 2://VISITOR
        Notifications::buildingVisitorRewards($player,$tile);
        $rewards = $tile->visitorReward;
        foreach($rewards->entries as $reward){
          $reward->rewardPlayer($player,$region,$tile);
        }
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