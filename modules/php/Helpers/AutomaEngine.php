<?php
namespace ROG\Helpers;

use RiverOfGoldNightMarket;
use ROG\Core\Notifications;
use ROG\Managers\AutomaCards;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
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

        //When Seishin’s action deck is empty, shuffle her discard pile facedown to make a new deck. Seishin then claims a mastery based on her die result
        $deckSize = AutomaCards::countAutomaActionDeckSize(CARD_AUTOMA_LOCATION_DECK);
        if($deckSize == 0){
            AutomaCards::reshuffleAutomaActionDeck($player, CARD_AUTOMA_LOCATION_PLAYED, CARD_AUTOMA_LOCATION_DECK,);
            $this->claimMasteries($player);
        }
    }
    
    public function claimMasteries(AutomaPlayer $player) {
        $this->game->trace(__CLASS__.'.'.__FUNCTION__."()");

        $playerDie = $player->getDie();
        // RULE :
        //If her die is a 1, 2, or 3, she claims and scores the leftmost available mastery
        //If her die is a 4, 5, or 6, she claims and scores the rightmost available mastery.
        $orderFromLeft = $playerDie < 4;
        $masteryCards = Tiles::getMasteryToClaim($orderFromLeft);
        foreach ($masteryCards as $tile) {
            if(Players::claimMastery($player,$tile)){
                break;
            }
        }
    }
}