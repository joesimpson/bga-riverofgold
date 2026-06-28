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
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;
use ROG\Models\Meeple;
use ROG\Models\Player;
use ROG\Models\ShoreSpace;

class BonusFreeBuild extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_BONUS_FREE_BUILD,
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
    $possibleTiles = Tiles::getInLocation(TILE_LOCATION_BUILDING_ROW)->getIds();
    
    $args = [
      'c' => $currentBonus,
      'cbd' => $currentBonusDatas,
      'spaces' => [],
      'tiles' => $possibleTiles,
    ];
    
    switch($currentBonus){
      case BONUS_TYPE_BUILD_NEAR_SHIPS:
        $ships = Meeples::getBoats($player->getId());
        $shipSpaces = array_unique($ships->map(function(Meeple $ship){ 
          return $ship->getPosition();
        })->toArray());
        $emptyShoreSpaces = ShoreSpaces::getEmptySpacesAdjacentToRiver($shipSpaces);
        $spaces = $this->game->filterSpacesToBuild($player,$emptyShoreSpaces, true);
        $spaces->map(function(ShoreSpace $s) { return $s->cost = 0; $s;});
        $args['spaces'] = $spaces;
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
  public function actFreeBuild(
      #[IntParam(name: 'p')] int $position,
      #[IntParam(name: 't')] int $tileId,
      int $version,
      int $activePlayerId, array $args,
      )
  {
    $this->game->checkVersion($version);
    $this->game->trace("actFreeBuild($position, $tileId, $activePlayerId)");
    
    $player = Players::get($activePlayerId);

    $possibleSpaces = $args['spaces'];
    $possibleSpacesIds = $possibleSpaces->map(function($space) {return $space->id;})->toArray();
    if(!in_array($position, $possibleSpacesIds)){
      throw new UnexpectedException(10,"You cannot build on $position, see ids: ".json_encode($possibleSpacesIds));
    }
    $possibleTiles = $args['tiles'];
    if(!in_array($tileId,$possibleTiles)){
      throw new UnexpectedException(12,"You cannot build tile $tileId, see ids: ".json_encode($possibleTiles));
    }

    // game logic  
    Log::addStep();
    
    $this->game->processBuild($player, $position, $tileId, $args, true);

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