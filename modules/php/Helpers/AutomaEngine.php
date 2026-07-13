<?php
namespace ROG\Helpers;

use RiverOfGoldNightMarket;
use ROG\Core\Notifications;
use ROG\Managers\AutomaCards;
use ROG\Managers\Cards;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\AutomaPlayer;
use ROG\Models\ScenarioType;

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
        $playAgain = $actionCard->play($player);

        //When Seishin’s action deck is empty, shuffle her discard pile facedown to make a new deck. Seishin then claims a mastery based on her die result
        $deckSize = AutomaCards::countAutomaActionDeckSize(CARD_AUTOMA_LOCATION_DECK);
        if($deckSize == 0){
            AutomaCards::reshuffleAutomaActionDeck($player, CARD_AUTOMA_LOCATION_PLAYED, CARD_AUTOMA_LOCATION_DECK,);
            $this->claimMasteries($player);
        }

        if($playAgain){
            $player->rollDie();
            $this->playTurn($player);
        }
    }
    
    public function claimMasteries(AutomaPlayer $player) {
        $this->game->trace(__CLASS__.'.'.__FUNCTION__."()");

        $playerDie = $player->getDie();
        $nbMasteriesToClaimAtOnce = 1;
        $scenarioReserveClaims = Cards::getAssignedScenario(ScenarioType::PHOENIX_1);
        if(isset($scenarioReserveClaims)){
            $nbMasteriesToClaimAtOnce = 2;
        }
        $nbClaims = 0;

        // RULE :
        //If her die is a 1, 2, or 3, she claims and scores the leftmost available mastery
        //If her die is a 4, 5, or 6, she claims and scores the rightmost available mastery.
        $orderFromLeft = $playerDie < 4;
        
        $newClaim = true;
        while($newClaim && $nbClaims < $nbMasteriesToClaimAtOnce){
            $newClaim = false;
            $masteryCards = Tiles::getMasteryToClaim($orderFromLeft);
            foreach ($masteryCards as $tile) {
                if(Players::claimMastery($player,$tile)) {
                    $nbClaims++;
                    $newClaim = true;
                    break;
                }
            }
        }
    }
}