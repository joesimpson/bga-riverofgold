<?php

namespace ROG\States;

use Bga\GameFramework\Actions\CheckAction;
use Bga\GameFramework\States\PossibleAction;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Log;
use ROG\Helpers\Utils;
use ROG\Managers\Cards;
use ROG\Managers\Players;

trait ConfirmUndoTrait
{
    /**
     * Add a NOT undoable step in Log module
     * @param int $state
     */
    public function addCheckpoint($state)
    {
        Globals::setChoices(0);
        Log::checkpoint($state);
    }

    /**
     * Add an undoable step in Log module
     */
    public function addStep()
    {
        Log::addStep();
    }

    public function argsConfirmTurn()
    {
        $activePlayer = Players::getActive();
        $data = [];
        //send current turn player 
        $data['c'] = Globals::getTurnPlayer();
        $data['trade'] = count($this->listPossibleTrades($activePlayer))>0;
        $this->addArgsForPlayCards($data,$activePlayer);
        $this->addArgsForUndo($data);
        return $data;
    }
    /**
     * Update state args with some parameters used for canceling actions. To be called in each state args where we will want to cancel.
     * 
     */
    public static function addArgsForUndo(&$args)
    {
        $args['previousSteps'] = Log::getUndoableSteps();
        $args['previousChoices'] = Globals::getChoices();
    }

    public function stConfirmTurn()
    {
        if (Globals::getChoices() == 0) {
            $this->actConfirmTurn(Utils::gameVersion(),true);
        }
    }

    #[PossibleAction]
    public function actConfirmTurn(int $version, ?bool $auto = false)
    {
        $this->checkVersion($version);
        if (!$auto) {
            self::checkAction('actConfirmTurn');
        }

        $player = Players::getCurrent();
        $pId = $player->getId();
        /*
        $nbCardsToMoveToHand = Cards::countPlayerCards($pId,CARD_LOCATION_WAIT_FOR_HAND);
        if($nbCardsToMoveToHand>0){
            $cards = Cards::getPlayerFutureHandOrders($pId);
            foreach($cards as $card){
                $card->setLocation(CARD_LOCATION_HAND);
                Notifications::giveCardTo($player,$card);
            }

            //Refill is almost done, the player needs to make a choice
            $this->addCheckpoint(ST_DISCARD_CARD);
            $this->gamestate->nextState('refillHand');
            return;
        }
        */
        
        $this->gamestate->nextState('confirm');
    }

    #[PossibleAction]
    #[CheckAction(false)]
    public function actRestart(int $version,)
    {
        $this->checkVersion($version);
        self::checkAction('actRestart');
        self::processRestartTurn();
    }
    /**
     * undo ALL player steps
     *
     * @throws UnexpectedException
     */
    public static function processRestartTurn(){
        $player = Players::getCurrent();
        $pId = $player->id;
        if (Globals::getChoices($pId) < 1) {
            throw new UnexpectedException(404,'No choice to undo. You may need to reload the page.');
        }
        Log::undoTurn();
        Notifications::restartTurn($player);
    }

    #[PossibleAction]
    #[CheckAction(false)]
    public function actUndoToStep(int $stepId, int $version,)
    {
        $this->checkVersion($version);
        self::checkAction('actRestart');
        self::processUndoToStep($stepId);
    }
    /**
     * undo to step
     * @param int $stepId
     *
     * @throws UnexpectedException
     */
    public static function processUndoToStep(int $stepId){
        $player = Players::getCurrent();
        $steps = Log::getUndoableSteps($player->id);
        if(!in_array($stepId,$steps)){
            throw new UnexpectedException(404,'This step is not undoable anymore. You may need to reload the page.');
        }
        Log::undoToStep($stepId);
        Notifications::undoStep($player, $stepId);
    }
}
