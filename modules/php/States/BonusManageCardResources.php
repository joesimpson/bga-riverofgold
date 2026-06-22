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
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Models\Player;
use ROG\Models\ScenarioCard;

class BonusManageCardResources extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_BONUS_MANAGE_CARD_RESOURCES,
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
    $cardId = $currentBonusDatas['card_id'];
    $card = Cards::get($cardId);
    $args = [
      'c' => $currentBonus,
      'card_id' => $cardId,
    ];

    $cardResources = $card->getResources();
    $types = [];
      
    $canSkip = true;
    //FILTER on bonus type :
    switch($currentBonus){
      case BONUS_TYPE_MANAGE_DEBT:
        //Player has to decide to REDUCE DEBT OR NOT before Interests
        $canSkip = false;
        // FILTER ON player min/max 
        $limit['min'] = 0;
        $limit['max'] = min($player->getMoney(), $cardResources[RESOURCE_TYPE_MONEY]);
        $types[RESOURCE_TYPE_MONEY] = $limit;
        break;
        
      case BONUS_TYPE_REMOVE_GOODS:
        $args['meeples'] = $currentBonusDatas['meeplesIds'];
        $args['spaces'] = $currentBonusDatas['shoreSpacesIds'];
        $args['resources'] = $currentBonusDatas['resources'];
        foreach($args['resources'] as $resType){
          if(!$player->canSpendResource($resType,1)) continue;
          $limit['min'] = 1;
          $limit['max'] = 1;
          $types[$resType] = $limit;
        }
        break;
    }

    $args['types'] = $types;
    $args['skip'] = $canSkip;

    $this->game->addArgsForUndo($args);
    return $args;
  }     
  
  public function onEnteringState(int $activePlayerId, array $args) {
    $this->game->trace(__CLASS__.".".__FUNCTION__."($activePlayerId)");
    
  }

  #[PossibleAction]
  public function actSkipBonuses(
      int $version,
      int $activePlayerId, array $args,
    )
  {
    $this->game->checkVersion($version);
    $this->game->trace(__CLASS__.".".__FUNCTION__."($activePlayerId)");
    
    Log::addStep();

    if(!$args['skip']){
      throw new UnexpectedException(405,"You should not skip these bonuses !");
    }

    Globals::setCurrentBonus(null);
    Globals::setCurrentBonusDatas(null);
    return ST_BONUS_CHOICE;
  }
  /**
   * Player action
   */
  #[PossibleAction]
  public function actManageCardResources(
      int $qty,
      int $type,
      int $version,
      int $activePlayerId, array $args,
    )
  {
    $this->game->checkVersion($version);
    $this->game->trace(__CLASS__.".".__FUNCTION__."( $qty, $type, $activePlayerId)");
    
    $player = Players::get($activePlayerId);
    /** @var ScenarioCard */
    $card = Cards::get($args['card_id']);
    $types = $args['types'];
    if (!in_array($type,array_keys($types))) {
      throw new UnexpectedException(201,"Invalid resource $type ");
    } 

    $typeDatas = $types[$type];
    $min = $typeDatas['min'];
    $max = $typeDatas['max'];
    if (isset($min) && $min > $qty) {
      throw new UnexpectedException(201,"Invalid quantity $qty < $min");
    } 
    if (isset($max) && $max < $qty) {
      throw new UnexpectedException(201,"Invalid quantity $qty > $max");
    } 

    Log::addStep();
    
    // game logic  
    $currentBonus = $args['c'];
    $stayInState = false;

    switch($currentBonus){
      case BONUS_TYPE_MANAGE_DEBT:
        $card->abilityOnManageResources($player, $qty,$type);
        break;
      case BONUS_TYPE_REMOVE_GOODS:
        $card->abilityOnManageResources($player, $qty,$type);
        $currentBonusDatas = Globals::getCurrentBonusDatas();
          $key = array_search($type,$currentBonusDatas['resources']);
          unset($currentBonusDatas['resources'][$key]);
          $currentBonusDatas['resources'] = array_values($currentBonusDatas['resources']);
        Globals::setCurrentBonusDatas($currentBonusDatas);
        //stay here until other resources are managed or skipped
        $stayInState = (count($currentBonusDatas['resources']) > 0);
        break;
    }
    if($stayInState){
      return self::class;
    }

    Globals::setCurrentBonus(null);
    Globals::setCurrentBonusDatas(null);
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