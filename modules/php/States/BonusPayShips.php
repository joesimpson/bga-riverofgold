<?php

//namespace ROG\States;
namespace Bga\Games\RiverOfGoldNightMarket\States;

use Bga\GameFramework\Actions\Types\IntArrayParam;
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
use ROG\Models\Player;

class BonusPayShips extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_BONUS_PAY_SHIPS,
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
    $lastSailedShipId = Globals::getLastSailedShip();
    $ships_pids = [];
    if(isset($lastSailedShipId) && $lastSailedShipId>0){
      $lastSailedShip = Meeples::get($lastSailedShipId);
      $lastSailedSpace = $lastSailedShip->getPosition();
      $ships_pids = Meeples::getOpponentIdsInRiverSpace($lastSailedSpace,$player->getId());
    }
    $costPerPlayer = NB_MONEY_FOR_PAYING_SHIPS;
    $pointsPerPlayer = NB_POINTS_MISTRESS_OF_WINDS;
    $maxToSelect = $player->getMoney()/$costPerPlayer;
    $args = [
      'ships_pids' => $ships_pids,
      'max' => $maxToSelect,
      'cost_pp' => $costPerPlayer,
      'points_pp' => $pointsPerPlayer,
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
  public function actPayShips(
      #[IntArrayParam()] array $p_ids, 
      int $version,
      int $activePlayerId, array $args,
      )
  {
    $this->game->checkVersion($version);
    $this->game->trace("actPayShips(".json_encode($p_ids).", $activePlayerId)");
    
    $player = Players::get($activePlayerId);
    $ships_pids = $args['ships_pids'];
    //$maxPids = count($ships_pids);
    $maxPids = min($args['max'], count($ships_pids));
    if (!isset($p_ids) || $maxPids < count($p_ids)) {
      throw new UnexpectedException(120,"You must select $maxPids players max");
    }
    $nbPids = count($p_ids);
    $diff = array_diff($p_ids, $ships_pids); 
    if (count($diff) !== 0) {
      throw new UnexpectedException(121,("These players are not a valid set"));
    }

    // game logic  
    Log::addStep();
    Players::spendMoney($player,$nbPids);
    foreach($p_ids as $p_id){
      $opponent = Players::get($p_id);
      $opponent->giveResource($args['cost_pp'],RESOURCE_TYPE_MONEY);
      $player->addPoints($args['points_pp']);
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