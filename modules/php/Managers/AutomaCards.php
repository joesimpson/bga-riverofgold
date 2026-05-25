<?php

namespace ROG\Managers;

use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Utils;
use ROG\Models\AutomaActionType;

/* Class to manage all the Automa cards */

class AutomaCards extends Cards
{

    ///////////////////////////////////////////////////////////////////////////////////////
    
    /** Creation of the cards
     * @param Collection $players
     */
    public static function setupNewGame($players, $options)
    {

        if(!Utils::isGameWithAutoma()){
            return;
        }
        $difficulty = Globals::getOptionSeishin();

        $cards = [];
        
        $actions = self::getAutomaActionCardsTypes();
        foreach ($actions as $type => $card) {
            $cards[] = [
                'type' => $type,
                'subtype' => CARD_TYPE_AUTOMA_ACTION,
                'location' => CARD_AUTOMA_LOCATION_DECK,
                'nbr' => $card['nbr'][$difficulty],
            ];
        }

        $cardsDatas = self::create($cards);
        //$cardsCollection = self::getMany($cardsDatas);
        $cardsCollection = self::getInLocation(CARD_AUTOMA_LOCATION_DECK);
        Notifications::initSeishinDeck($cardsCollection);
        self::shuffle(CARD_AUTOMA_LOCATION_DECK);
    }
  
    /**
     * @return array of all the different types of Automa ACTION Cards
     */
    public static function getAutomaActionCardsTypes(): array
    { 
        $f = function ($t) {
            return [
                //Number of same in deck according to difficulties
                'nbr' => [
                        OPTION_SEISHIN_LEVEL_1 => $t[0],
                        OPTION_SEISHIN_LEVEL_2 => $t[1],
                        OPTION_SEISHIN_LEVEL_3 => $t[2],
                        OPTION_SEISHIN_LEVEL_4 => $t[3],
                        OPTION_SEISHIN_LEVEL_5 => $t[4],
                    ],
                'title' => $t[5],
            ];
        };
        return [
            AutomaActionType::SAIL_HIGHER ->value  =>  $f([ 3, 3, 2, 1, 0, clienttranslate('Sail the Higher Ship'),        ]), 
            AutomaActionType::SAIL_LOWER  ->value  =>  $f([ 2, 2, 2, 2, 2, clienttranslate('Sail the Lower Ship'),         ]), 
            AutomaActionType::DELIVER     ->value  =>  $f([ 1, 2, 2, 2, 2, clienttranslate('Deliver to a Customer'),       ]), 
            AutomaActionType::BUILD       ->value  =>  $f([ 2, 2, 2, 2, 2, clienttranslate('Build a Building'),            ]), 
            AutomaActionType::ADVANCE_CITY->value  =>  $f([ 1, 1, 1, 1, 1, clienttranslate('Advance in the City of Lies'), ]), 
        ];
    }

}
