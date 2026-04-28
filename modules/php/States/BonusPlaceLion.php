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
use ROG\Helpers\Utils;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Models\Player;

class BonusPlaceLion extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_BONUS_PLACE_LION,
      type: StateType::ACTIVE_PLAYER,
    );
  }

  /**
   * Game state arguments
   *
   */
  public function getArgs(): array
  {
    $args = [
      'spaces' => ShoreSpaces::getAllEmptySpaces(),
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
  public function actPlaceLion(
      int $shore_space, 
      int $version,
      int $activePlayerId, array $args,
      )
  {
    $this->game->checkVersion($version);
    $this->game->trace("actPlaceLion( $shore_space, $activePlayerId)");
    
    $player = Players::get($activePlayerId);
    $spaces = $args['spaces'];
    if (!in_array($shore_space,$spaces)) {
      throw new UnexpectedException(503,"Invalid space $shore_space");
    } 

    // game logic  
    Log::addStep();
    Meeples::placeLionOnShoreSpace($player, $shore_space);

    return ST_BONUS_CHOICE;
  }

  function zombie(int $playerId, array $args) {
    $this->game->trace(__CLASS__.".".__FUNCTION__."($playerId)");
    $spaces = $args['spaces'];
    if(!empty($spaces)){
      $first = $spaces[0];
      return $this->actPlaceLion($first,Utils::gameVersion(),$playerId,$args);
    }
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