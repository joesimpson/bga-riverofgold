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
use ROG\Managers\CitySpaces;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
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
    $possibleSpaces = [];
    switch($currentBonus){
      default:
      case BONUS_TYPE_ADVANCE_OR_POINTS:
        $possibleActions = [
          BonusAdvanceCityChoice::ADVANCE->value,
          BonusAdvanceCityChoice::POINTS->value,
        ];
        $possibleSpaces = BonusAdvanceCity::listPossibleSpacesToAdvance($player);
        break;
    }

    $args = [
      'c' => $currentBonus,
      'p' => $possibleActions,
      'citySpaces' => $possibleSpaces,
      'score' => 5,
    ];

    $this->game->addArgsForUndo($args);
    return $args;
  }     
  
  public static function listPossibleSpacesToAdvance(Player $player) : array {
    if(! Utils::isGameWithCityOfLies() ) {
      return [];
    }
    $possibleSpacesByMarker = [];
    $cityMarker = Meeples::getCityMarker($player->getId());
    if(isset($cityMarker)){
      $possibleSpaces = CitySpaces::getEmptySpacesInColumn(1 + $cityMarker->getCityColumn());
      
      $possibleSpaces = array_map(function (int $spaceId) use ($cityMarker,) {
        $space = CitySpaces::getCitySpaceById($spaceId);
        //must go to the column to the right of your current space
        $canGo = $space->column == 1 + $cityMarker->getCityColumn();
        $lanternCosts = [];
        return ['space' =>$spaceId, 'p' => $canGo, 'cost' =>$lanternCosts, ];
      },$possibleSpaces,);
      $possibleSpaces = array_filter($possibleSpaces, function (array $datas) {
          return $datas['p'];
        });
      if( count($possibleSpaces) > 0){
        $possibleSpacesByMarker[ $cityMarker->getId()] = $possibleSpaces;
      }
    }

    return $possibleSpacesByMarker;
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
      ?int $space = null, 
      ?int $markerId = null,
    )
  {
    $this->game->checkVersion($version);
    $this->game->trace(__CLASS__.".".__FUNCTION__."( $choice, $activePlayerId,  $space, $markerId)");
    
    $player = Players::get($activePlayerId);
    $choices = $args['p'];
    if (!in_array($choice,$choices)) {
      throw new UnexpectedException(503,"Invalid choice $choice");
    } 

    // game logic  
    Log::addStep();

    switch($choice){
      case BonusAdvanceCityChoice::ADVANCE->value:
        $citySpaces = $args['citySpaces'];
        $possibleMarkers = array_keys($citySpaces);
        if (!in_array($markerId,$possibleMarkers)) {
          throw new UnexpectedException(503,"Invalid marker $markerId");
        } 
        $possibleSpaces = array_map(function (array $datas)  {
          return $datas['space'];
        },$citySpaces[$markerId],);
        if (!in_array($space,$possibleSpaces)) {
          throw new UnexpectedException(503,"Invalid space $space");
        } 
        AdvanceCity::processAdvance($player, $space, $markerId, [], [], [], );
        Players::claimMasteries($player);
        break;
      case BonusAdvanceCityChoice::POINTS->value :
        $player->addPoints(5);
        Players::claimScoreMasteries($player);
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