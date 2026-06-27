<?php

//namespace ROG\States;
namespace Bga\Games\RiverOfGoldNightMarket\States;

use Bga\GameFramework\Actions\Types\IntArrayParam;
use RiverOfGoldNightMarket;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\StateType;
use ROG\Core\Game;
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
      #[IntArrayParam(name:'sr')] array $regionsToPayLanterns, 
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
    $possibleSpaces = array_map(function (array $datas)  {
      return $datas['space'];
    },$choices,);

    if (!in_array($space,$possibleSpaces)) {
      throw new UnexpectedException(503,"Invalid space $space");
    } 
    
    $spaceDatas = array_values(array_filter($choices,function (array $datas) use ($space) {
      return $datas['space'] == $space;
    }))[0];

    $lanternCosts = $spaceDatas['cost'];
    if($regionsToPayLanterns == null || count($regionsToPayLanterns) !== count($lanternCosts)){
      throw new UnexpectedException(504,"Invalid number of influence to pay lanterns");
    }

    // game logic  
    Log::addStep();

    $index = 0;
    foreach($lanternCosts as $cost){
      $region = $regionsToPayLanterns[$index];
      Players::spendInfluence($player,$region, $cost);
      $index++;
    }

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
      $influences = $player->getAllInfluences();  
      $possibleSpaces = CitySpaces::getEmptySpaces($die);
      
      $possibleSpaces = array_map(function (int $spaceId) use ($cityMarker,$influences) {
        $space = CitySpaces::getCitySpaceById($spaceId);
        //must go to a column to the right of your current space
        $canGo = $space->column > $cityMarker->getCityColumn();
        $lanternCosts = [];
        if($canGo){
          //Rule : "If you do not have enough influence to pay the full cost, you cannot do this action."
          $canPay = self::canPayLanterns($influences, $cityMarker->getCityColumn() ,$space->column, $lanternCosts);
          $canGo = $canGo && $canPay;
          if($canGo){
            $lanternCosts;
          }
        }
        return ['space' =>$spaceId, 'p' => $canGo, 'cost' =>$lanternCosts, ];
      },$possibleSpaces,);
      $possibleSpaces = array_filter($possibleSpaces, function (array $datas) {
          return $datas['p'];
        });
        
      //$possibleSpaces = array_map(function (array $datas)  {
      //  return [$datas['space'] => $datas ];
      //},$possibleSpaces,);

      if( count($possibleSpaces) > 0){
        $possibleSpacesByMarker[ $cityMarker->getId()] = $possibleSpaces;
      }
    }

    return $possibleSpacesByMarker;
  }

  
  /**
   * @param array $influences : list of player current influences
   * @param int $fromColumn : a column on the left (current player position)
   * @param int $toColumn : a column on the right (possible player destination) 
   * @param array &$lanternsCosts : costs computed by this function
   * @return bool true only when player influence array has enough influence (same or different region by groups) to pay each lanterns from $fromColumn to $toColumn
   */
  public static function canPayLanterns(array $influences, int $fromColumn, int $toColumn, array &$lanternCosts = []) : bool {

    $sumInfluence = 0; 
    $usedInfluence = [];
    foreach($influences as $region => $influence){
      $sumInfluence += $influence;
      $usedInfluence[$region] = 0;
    }

    $distinctCosts = [];
    $sumCosts = 0; 
    foreach(CITY_LANTERNS as $col => $cost){
      if($col <= $toColumn && $col > $fromColumn){
        $lanternCosts[] = $cost;
        if(!array_key_exists($cost,$distinctCosts)) $distinctCosts[$cost] = 0;
        $distinctCosts[$cost]++;
        $sumCosts += $cost;
      }
    }

    if($sumCosts > $sumInfluence) return false;

    //sort($distinctCosts); // ! will change keys

    //Game::get()->trace("");
    //Game::get()->trace("canPayLanterns($fromColumn, $toColumn)... with array influences ".json_encode($influences));

    $debts = [];
    //Try to pay low cost first, because low influence cannot be used for something else
    foreach($distinctCosts as $cost => $neededOfThatCost){
      $nbFoundOfThatCost = 0;
      $sumBiggers = 0; 
      foreach($influences as $region => &$influence){
        if($influence == $cost){
          $debtSum = array_reduce($debts,  function ($ax, $dx) {  return $ax + (int)$dx;  }, 0);
          if($debtSum>0) {
              foreach($debts as $i => &$debt){
                if($influence == 0) break;
                if($debt <1){
                  unset($i); 
                  continue;
                }
                //pay debt from previous turns before
                $payableDebt = min($debt, $influence);
                $usedInfluence[$region] += $payableDebt;
                $influence -= $payableDebt;
                //Game::get()->trace("reduce debt $debt by $payableDebt from region $region : with array influences ".json_encode($influences));
                $debt -= $payableDebt;
                if($debt <1){
                  unset($i); 
                }
              }
          }
          else {
            $nbFoundOfThatCost++;
            $usedInfluence[$region] += $cost;
            //Game::get()->trace("pay influence $influence from region $region: with array influences ".json_encode($influences));
            $influence = 0;
          }
        }
        else if($influence > $cost){
          $sumBiggers += $influence;
        }
      }
      //Game::get()->trace("canPayLanterns($fromColumn, $toColumn)... cost = $cost (neededOfThatCost=$neededOfThatCost): nbFoundOfThatCost=$nbFoundOfThatCost, sumBiggers=$sumBiggers with array influences ".json_encode($influences));

      $missingOfThatCost = ($neededOfThatCost - $nbFoundOfThatCost);
      for($k =1; $k<=$missingOfThatCost;$k++) $debts[] = $cost;
      $debtSum = array_reduce($debts,  function ($ax, $dx) {  return $ax + (int)$dx;  }, 0);
      if($debtSum>0){
        
        if($sumBiggers >= $debtSum){
          //Game::get()->trace("wait new turn to spend debt $debtSum : ".json_encode($debts));
        } else {
          //Game::get()->trace("next turn is not enough to spend debt $debtSum with $sumBiggers : ".json_encode($debts));
          return false;
        }
      }
    }
    
    $debtSum = array_reduce($debts,  function ($ax, $dx) {  return $ax + (int)$dx;  }, 0);
    //Game::get()->trace("end remaining debt $debtSum : ".json_encode($debts));
    if($debtSum>0){
      foreach($debts as $i => &$debt){
        //Game::get()->trace("end remaining debt $debt in ".json_encode($debts));
        //TRY to pay with all remaining influence
        foreach($influences as $region => &$influence){
          if($influence == 0) continue;
          if($debt>0) {
            $payableDebt = min($debt, $influence);
            $influence -= $payableDebt;
            //Game::get()->trace("reduce debt $debt by $payableDebt from region $region : with influence $influence / array influences ".json_encode($influences));
            $debt -= $payableDebt;
            if($debt <1){
              unset($i); 
              break;
            }
          }
          else break;
        }
      }
    }

    $debtSum = array_reduce($debts,  function ($ax, $dx) {  return $ax + (int)$dx;  }, 0);
    if($debtSum > 0){
      //Game::get()->trace("KO : no new turn to spend debt $debtSum");
      return false;
    }

    return true;
  }
 
}