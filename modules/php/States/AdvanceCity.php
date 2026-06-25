<?php

//namespace ROG\States;
namespace Bga\Games\RiverOfGoldNightMarket\States;

use RiverOfGoldNightMarket;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\StateType;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Core\Stats;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Log;
use ROG\Managers\CitySpaces;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Models\CitySpace;
use ROG\Models\MAIN_ACTION;
use ROG\Models\Player;

class AdvanceCity extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_PLAYER_TURN_ADVANCE,
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
    $player_id = $player->getId();
    
    $possibleSpaces = AdvanceCity::listPossibleSpacesToAdvance($player);
    $possibleSpaces = array_keys($possibleSpaces);

    $args = [
      'citySpaces' => $possibleSpaces,
    ];

    $this->game->addArgsForUndo($args);
    return $args;
  }     

  public function onEnteringState(int $activePlayerId, array $args) {
    $this->game->trace(__CLASS__.".".__FUNCTION__."($activePlayerId)");
  }

  /**
   * Player MAIN action during turn
   */
  #[PossibleAction]
  public function actSelectAdvanceDest(
      int $space, 
      int $version,
      int $activePlayerId, array $args,
    )
  {
    $this->game->checkVersion($version);
    $this->game->trace(__CLASS__.".".__FUNCTION__."( $space, $activePlayerId)");
    
    $player = Players::get($activePlayerId);
    $choices = $args['citySpaces'];
    if (!in_array($space,$choices)) {
      throw new UnexpectedException(503,"Invalid space $space");
    } 

    // game logic  
    Log::addStep();

    //TODO JSA NEW ACTION ADVANCE...
    $clanMarker = Meeples::getCityMarker($activePlayerId);
    if(!isset($clanMarker)) {
      //SHOULD NOT HAPPEN
      throw new UnexpectedException(504,"No city marker found for player $activePlayerId");
    }
    $citySpace = CitySpaces::getCitySpaceById($space);
    Meeples::moveClanMarkerOnCity($player,$clanMarker,$citySpace);
    
    Players::claimMasteries($player);
    Stats::inc("nbActionsAdvance", $player->getId());
    Globals::setTurnMainActionDone(MAIN_ACTION::ADVANCE->value);

    return ST_BONUS_CHOICE;
  }
  
  /*
  public function process_Advance(
    Player $player, 
    int $citySpace, 
  ){
    $this->game->trace(__CLASS__.".".__FUNCTION__."( $citySpace )");

  }
  */
  
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

  function zombie(int $playerId, array $args) {
    $this->game->trace(__CLASS__.".".__FUNCTION__."($playerId)");
    return ST_BONUS_CHOICE;
  }

  //--------------------------
  public static function listPossibleSpacesToAdvance(Player $player) : array {

    $die = $player->getDie();
    $allSpaces = CitySpaces::getAllCitySpaces();
    //TODO JSA FILTER with rules
    $possibleSpaces = array_filter($allSpaces, function (CitySpace $space) use ($die){ return $space->region == $die;} ,);

    return $possibleSpaces;
  }
 
}