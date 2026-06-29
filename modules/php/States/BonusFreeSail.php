<?php

//namespace ROG\States;
namespace Bga\Games\RiverOfGoldNightMarket\States;

use Bga\GameFramework\Actions\Types\IntParam;
use RiverOfGoldNightMarket;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\StateType;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Collection;
use ROG\Helpers\Log;
use ROG\Helpers\Utils;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\Meeple;
use ROG\Models\Player;
use ROG\Models\ShoreSpace;

class BonusFreeSail extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_BONUS_FREE_SAIL,
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
    
    $args = [
      'c' => $currentBonus,
      'cbd' => $currentBonusDatas,
      'spaces' => [],
    ];
    
    switch($currentBonus){
      case BONUS_TYPE_FREE_SAIL:
        $boats = Meeples::getBoats($player->getId());
        $possibleSpaces = $boats->map(function(Meeple $boat){ 
          $shipSpaces = [];
          for($k=1;$k<=NB_RIVER_SPACES;$k++){
            //ship position is between 1 and NB_RIVER_SPACES
            if($boat->getPosition() == $k) continue;
            $shipSpaces[] = $k;
          }
          return $shipSpaces;
        })->toAssoc();
        $args['spaces'] = $possibleSpaces;
        break;
    }

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
  public function actFreeSail(
      #[IntParam(name: 's')] int $shipId,
      #[IntParam(name: 'r')] int $riverSpace,
      int $version,
      int $activePlayerId, array $args,
      )
  {
    $this->game->checkVersion($version);
    $this->game->trace("actFreeSail($shipId,$riverSpace, $activePlayerId)");
    
    $player = Players::get($activePlayerId);

    $possibleSpaces = $args['spaces'];
    $possibleShips = array_keys($possibleSpaces);
    if(!in_array($shipId, $possibleShips)){
      throw new UnexpectedException(20,"You cannot Sail ship $shipId, see : ".json_encode($possibleShips));
    } 
    $possibleShipsDest = $possibleSpaces[$shipId];
    if(!in_array($riverSpace, $possibleShipsDest)){
      throw new UnexpectedException(21,"You cannot Sail to $riverSpace, see : ".json_encode($possibleShipsDest));
    } 

    // game logic  
    Log::addStep();
    
    $ship = Meeples::get($shipId);
    $upriver = true;//Force true to skip "complete journey"
    $this->game->process_Sail($player,$ship,$riverSpace,$upriver );

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