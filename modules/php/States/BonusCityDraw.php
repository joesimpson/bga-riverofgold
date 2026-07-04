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
use ROG\Managers\CityCards;
use ROG\Managers\Players;
use ROG\Models\Player;

class BonusCityDraw extends GameState
{
   
  function __construct(
    protected RiverOfGoldNightMarket $game,
  ) {
    parent::__construct($game,
      id: ST_BONUS_CITY_DRAW,
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
    
    $privateDatas = [];
    
    switch($currentBonus){
      case BONUS_TYPE_CITY_CARD_DRAW:
        $cityLocation = $currentBonusDatas['location'];
        $cards = CityCards::getInLocationOrdered($cityLocation);
        $privateDatas[$player->getId()] = [
          'cards' => $cards->uiAssoc(),
        ];
        break;
    }

    $args = [
      'c' => $currentBonus,
      'cbd' => $currentBonusDatas,
      '_private' => $privateDatas,
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
  public function actTakeCityCard(
      #[IntParam(name: 'c')] int $cardId,
      int $version,
      int $activePlayerId, array $args,
      )
  {
    $this->game->checkVersion($version);
    $this->game->trace("actTakeCityCard($cardId, $activePlayerId)");
    
    $player = Players::get($activePlayerId);

    $privateArgs= $args['_private'][$player->getId()];
    $possibleCards = isset($privateArgs['cards']) ? $privateArgs['cards'] : [];
    if(!in_array($cardId,array_keys($possibleCards))){
      throw new UnexpectedException(406,"Card $cardId is not selectable");
    }

    // game logic  
    Log::addStep();

    $card = CityCards::getCityCard($cardId);
    $fromLocation = $card->getLocation();
    $card->setPId($player->getId());
    $card->setState(1);
    $card->setLocation(CARD_CITY_LOCATION_HAND);
    Notifications::giveCityCardTo($player,$card,$fromLocation);
    $card->onAssignment($player);
    
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