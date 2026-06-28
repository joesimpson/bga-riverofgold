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
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Models\Player;

class BonusSelectRegion extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_BONUS_SELECT_REGION,
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

    $regions = REGIONS;
    $expectedNbr = 0;
    //FILTER on bonus type :
    switch($currentBonus){
      case BONUS_TYPE_INF_SELECT_REGION:
        $expectedNbr = 1;
        $from = $currentBonusDatas['region'];
        $regions = [];
        foreach(REGIONS as $region){
            //REMOVE previous region from the list
            if($region == $from) continue;
            $regions[] = $region;
        }
        break;
      case BONUS_TYPE_REWARDS_SELECT_REGION:
        $regions = REGIONS;
        $expectedNbr = $currentBonusDatas['bonusQuantity'];
        break;
    }

    $args = [
      'c' => $currentBonus,
      'cbd' => $currentBonusDatas,
      'p' => $regions,
      'nbr' => $expectedNbr,
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
  public function actSelectRegion(
      #[IntArrayParam(name:'choice')] array $choice, 
      int $version,
      int $activePlayerId, array $args,
    )
  {
    $this->game->checkVersion($version);
    $this->game->trace(__CLASS__.".".__FUNCTION__."( $activePlayerId) ".json_encode($choice));
    
    $player = Players::get($activePlayerId);
    $choices = $args['p'];
    $expectedNbr = $args['nbr'];
    if ($expectedNbr != count($choice)) {
      throw new UnexpectedException(503,"You need to select $expectedNbr choices");
    } 

    foreach($choice as $selectedRegion){
      if (!in_array($selectedRegion,$choices)) {
        throw new UnexpectedException(503,"Invalid choice $selectedRegion");
      } 
    }

    // game logic  
    Log::addStep();

    $currentBonus = $args['c'];
    $currentBonusDatas = $args['cbd'];
    switch($currentBonus){
      case BONUS_TYPE_INF_SELECT_REGION:
        $region = $choice[0];
        Players::gainInfluence($player, $region, $currentBonusDatas['bonusQuantity']);
        Players::claimMasteries($player);
        break;
      case BONUS_TYPE_REWARDS_SELECT_REGION:
        foreach($choice as $region){
          Notifications::trackRewards($player,$region);
          Players::gainInfluenceTrackRewards($player,$region, 0, $player->getInfluence($region) );
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