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

enum BonusBuildingRewardChoice: int
{
    case OWNER     = 1;
    case VISITOR   = 2;
}
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

    $currentBonus = Globals::getCurrentBonus();
    $currentBonusDatas = Globals::getCurrentBonusDatas();
    $player = Players::getActive();
    $player_id = $player->getId();

    $possibleActions = [];
    switch($currentBonus){
      default:
      case BONUS_TYPE_BUILDING_REWARD:
        $lastBuiltTile = $currentBonusDatas['tile'];
        $tile = Tiles::get($lastBuiltTile);
        $possibleActions = [
          $lastBuiltTile => [
            'type' => $tile->getType(),
            'choices' => [
              BonusBuildingRewardChoice::OWNER->value,
              BonusBuildingRewardChoice::VISITOR->value,
            ],
          ],
        ];
        //$tilesDatas[$lastBuiltTile] = $tile->getUiData();
        //$tilesDatas[$lastBuiltTile] = ['type' => $tile->getType(), 'choices' => ];
        break;
      case BONUS_TYPE_ANY_OWNER_REWARD:
        $tilesIds = Tiles::getPlayerBuildingTilesIds($player_id);
        $tiles = Tiles::getMany($tilesIds);
        //foreach($tilesIds as $tileId){
        foreach($tiles as $tileId => $tile){
          $possibleActions[$tileId]['type'] = $tile->getType();
          $possibleActions[$tileId]['choices'] = [BonusBuildingRewardChoice::OWNER->value,];
        }
        //$tilesDatas = $tiles->uiAssoc();
        //$tilesDatas = $tiles->map(function ($tile) {
        //    return ['type' => $tile->getType()];
        //})->toAssoc();
        break;
    }

    $args = [
      'c' => $currentBonus,
      'p' => $possibleActions,
      //'t' => $tilesDatas,
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
    $choices = $tilesDatas[$tileId]['choices'];
    if (!in_array($choice,$choices)) {
      throw new UnexpectedException(503,"Invalid choice $choice");
    } 

    // game logic  
    Log::addStep();
    $tile = Tiles::get($tileId);
    $region = $tile->getRegion();
    $multiplier = 0;
    $clanMarkers = $tile->getMeeples();
    foreach($clanMarkers as $clanMarker){
      if($clanMarker->getPId() == $player->getId()) $multiplier++;
    }
    switch($choice){
      case BonusBuildingRewardChoice::OWNER->value:
        Notifications::buildingOwnerRewards($player,$tile);
        $rewards = $tile->ownerReward;
        foreach($rewards->entries as $reward){
          for($k=1;$k<=$multiplier;$k++){
            $reward->rewardPlayer($player,$region,$tile);
          }
        }
        Players::claimMasteries($player);
        break;
      case BonusBuildingRewardChoice::VISITOR->value:
        Notifications::buildingVisitorRewards($player,$tile);
        $rewards = $tile->visitorReward;
        foreach($rewards->entries as $reward){
          for($k=1;$k<=$multiplier;$k++){
            $reward->rewardPlayer($player,$region,$tile);
          }
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