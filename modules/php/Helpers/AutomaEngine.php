<?php
namespace ROG\Helpers;

use RiverOfGoldNightMarket;
use ROG\Core\Notifications;
use ROG\Managers\AutomaCards;
use ROG\Managers\Players;
use ROG\Models\AutomaPlayer;

/**
 * Define rules for the automa turns
 */
class AutomaEngine {

    function __construct(
        protected RiverOfGoldNightMarket $game,
    ) {
    
    }

    public function playTurn(AutomaPlayer $player) {
        $this->game->trace(__CLASS__.'.'.__FUNCTION__."()");

        //$player = Players::automaPlayer();
        $actionCard = AutomaCards::giveActionCardToAutoma($player);
        $actionCard->play($player);
    }
}