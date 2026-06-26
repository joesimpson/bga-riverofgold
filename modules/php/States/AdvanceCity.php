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
use ROG\Helpers\Utils;
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
      int $markerId,
      int $version,
      int $activePlayerId, array $args,
    )
  {
    $this->game->checkVersion($version);
    $this->game->trace(__CLASS__.".".__FUNCTION__."( $space, $activePlayerId)");
    
    $player = Players::get($activePlayerId);
    $citySpaces = $args['citySpaces'];
    $possibleMarkers = array_keys($citySpaces);
    if (!in_array($markerId,$possibleMarkers)) {
      throw new UnexpectedException(503,"Invalid marker $markerId");
    } 
    $choices = $citySpaces[$markerId];
    if (!in_array($space,$choices)) {
      throw new UnexpectedException(503,"Invalid space $space");
    } 

    // game logic  
    Log::addStep();

    //TODO JSA NEW ACTION ADVANCE...
    $clanMarker = Meeples::get($markerId);
    $citySpace = CitySpaces::getCitySpaceById($space);
    Meeples::moveClanMarkerOnCity($player,$clanMarker,$citySpace);
    
    Players::claimMasteries($player);
    Stats::inc("nbActionsAdvance", $player->getId());
    Globals::setTurnMainActionDone(MAIN_ACTION::ADVANCE->value);

    if(Utils::goToBonusStepIfNeeded($player,false,false)){
      return ST_BONUS_CHOICE;
    }
    return ST_CONFIRM_CHOICES;
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
  /**
   * @return array [ meeple_id => $possibleSpaces, ] list of all possible spaces linked to each player city marker (in case of multiples in the future)
   */
  public static function listPossibleSpacesToAdvance(Player $player) : array {

    $possibleSpacesByMarker = [];
    $cityMarker = Meeples::getCityMarker($player->getId());
    if(isset($cityMarker)){
      $die = $player->getDie();
      $possibleSpaces = CitySpaces::getEmptySpaces($die);
      //TODO JSA FILTER with rules "If you do not have enough influence to pay the full cost, you cannot do this action."
      $possibleSpaces = array_filter($possibleSpaces, function (int $spaceId) use ($cityMarker){
          $space = CitySpaces::getCitySpaceById($spaceId);
          //must go to a column to the right of your current space
          $canGo = $space->column > $cityMarker->getCityColumn();
          return $canGo;
        });
      if( count($possibleSpaces) > 0){
        $possibleSpacesByMarker[ $cityMarker->getId()] = $possibleSpaces;
      }
    }

    return $possibleSpacesByMarker;
  }
 
}