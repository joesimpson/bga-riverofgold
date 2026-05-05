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

enum BonusAdvanceCityChoice: int
{
  case ADVANCE  = 1;
  case POINTS   = 2;
}
class BonusAdvanceCity extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_BONUS_ADVANCE_CITY,
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
    $player = Players::getActive();
    $player_id = $player->getId();

    $possibleActions = [];
    switch($currentBonus){
      default:
      case BONUS_TYPE_ADVANCE_OR_POINTS:
        $possibleActions = [
          BonusAdvanceCityChoice::ADVANCE->value,
          BonusAdvanceCityChoice::POINTS->value,
        ];
        break;
    }

    $args = [
      'c' => $currentBonus,
      'p' => $possibleActions,
      'score' => 5,
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
  public function actSelectAdvance(
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

    switch($choice){
      case BonusAdvanceCityChoice::ADVANCE->value:
        throw new UnexpectedException(404,"Not Yet AVAILABLE !");
        break;
      case BonusAdvanceCityChoice::POINTS->value :
        $player->addPoints(5);
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