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
    }

    $args['types'] = $types;
    $args['skip'] = $canSkip;

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

    switch($currentBonus){
      case BONUS_TYPE_MANAGE_DEBT:
        //Remove money from debt card by PAYING from player money
        $card->addResource($player,-$qty,$type);
        $player->giveResource(-$qty,$type);

        $debt = $card->getResource($type);
        if($debt <= 15){
          //DEBT 15 or less : Gain your second ship (if not yet placed). Place it on the middle river starting space.
          $ship = Meeples::getBoatOnCard($card);
          if(isset($ship)){
            $ship->setLocation(MEEPLE_LOCATION_RIVER);
            $ship->setPosition(STARTING_BOATS_SPACES[1]);
            Notifications::newBoat($player,$ship);
          }
        }
        //TODO JSA DEBT 0  : Gain 2 influence in each region, then gain all influence track rewards you have reached this game again.
        
        //Interest: For each 5 of debt remaining on this card, add 1 to your debt
        $interests = intval($debt / 5);
        $card->addResource($player,$interests,$type);
        break;
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