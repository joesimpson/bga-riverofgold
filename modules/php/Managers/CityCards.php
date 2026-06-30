<?php

namespace ROG\Managers;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Utils;
use ROG\Models\CITY_CARD_EFFECT;
use ROG\Models\CITY_CARD_TYPE;

/* Class to manage all the City of Lies cards */

class CityCards extends Cards
{
    
    public static function deckSizes() : array
    {
        return [
            CARD_CITY_LOCATION_OUTER_1 => self::countInLocation(CARD_CITY_LOCATION_OUTER_1),
            CARD_CITY_LOCATION_OUTER_2 => self::countInLocation(CARD_CITY_LOCATION_OUTER_2),
            CARD_CITY_LOCATION_INNER_1 => self::countInLocation(CARD_CITY_LOCATION_INNER_1),
            CARD_CITY_LOCATION_INNER_2 => self::countInLocation(CARD_CITY_LOCATION_INNER_2),
        ];
    }

    ///////////////////////////////////////////////////////////////////////////////////////
    
    /** Creation of the cards
     * @param array $players
     */
    public static function setupNewGame($players, $options)
    {

        if(!Utils::isGameWithCityOfLies()){
            return;
        }

        $nbPlayers = count($players);
        $nbPlayersWithAutoma = $nbPlayers + (Utils::isGameWithAutoma() ? 1 : 0);
        
        $cards = [];
        
        $types = CityCards::getCityCardsTypes();
        foreach ($types as $type => $card) {
            $cards[] = [
                'type' => $type,
                'subtype' => CARD_TYPE_CITY,
                'location' => $card['inner'] ? CARD_CITY_LOCATION_DECK_INNER : CARD_CITY_LOCATION_DECK_OUTER,
                'nbr' => $card['nbr'],
            ];
        }

        self::create($cards);

        self::shuffle(CARD_CITY_LOCATION_DECK_INNER);
        self::shuffle(CARD_CITY_LOCATION_DECK_OUTER);

        $nbPerPlayersCount = [1=>3, 2=>3, 3=>3, 4=>4, 5=>4,];
        $nbToPick = $nbPerPlayersCount[$nbPlayersWithAutoma];
        self::pickForLocation($nbToPick,CARD_CITY_LOCATION_DECK_OUTER,CARD_CITY_LOCATION_OUTER_1);
        self::shuffle(CARD_CITY_LOCATION_OUTER_1);
        self::pickForLocation($nbToPick,CARD_CITY_LOCATION_DECK_OUTER,CARD_CITY_LOCATION_OUTER_2);
        self::shuffle(CARD_CITY_LOCATION_OUTER_2);
        self::pickForLocation($nbToPick,CARD_CITY_LOCATION_DECK_INNER,CARD_CITY_LOCATION_INNER_1);
        self::shuffle(CARD_CITY_LOCATION_INNER_1);
        self::pickForLocation($nbToPick,CARD_CITY_LOCATION_DECK_INNER,CARD_CITY_LOCATION_INNER_2);
        self::shuffle(CARD_CITY_LOCATION_INNER_2);
    }
  
    /**
     * @return array of all the different types of Automa ACTION Cards
     */
    public static function getCityCardsTypes(): array
    { 
        $f = function ($t) {
            return [
                //Number of same in deck  
                'nbr' => $t[0],
                'inner' => $t[1],
                'effect' => $t[2],
                'title' => $t[3],
            ];
        };
        return [
            //OUTER CARDS
            CITY_CARD_TYPE::BRIBERY      ->value => $f([ 2, false, CITY_CARD_EFFECT::REVEAL->value   , clienttranslate('Bribery'),             ]), 
            CITY_CARD_TYPE::OFFLOAD      ->value => $f([ 2, false, CITY_CARD_EFFECT::REVEAL->value   , clienttranslate('Offload Contraband'),  ]), 
            CITY_CARD_TYPE::BLACK_MARKET ->value => $f([ 2, false, CITY_CARD_EFFECT::REVEAL->value   , clienttranslate('Black Market'),        ]), 
            CITY_CARD_TYPE::SHARED_CLI   ->value => $f([ 1, false, CITY_CARD_EFFECT::PREDICT->value  , clienttranslate('Shared Clients'),      ]), 
            CITY_CARD_TYPE::SHARED_ENG   ->value => $f([ 1, false, CITY_CARD_EFFECT::PREDICT->value  , clienttranslate('Shared Engineers'),    ]), 
            CITY_CARD_TYPE::SHARED_ENV   ->value => $f([ 1, false, CITY_CARD_EFFECT::PREDICT->value  , clienttranslate('Shared Envoys'),       ]), 
            CITY_CARD_TYPE::CARTEL       ->value => $f([ 1, false, CITY_CARD_EFFECT::REVEAL->value   , clienttranslate('Regional Cartel'),  ]), 

            //INNER CARDS
            CITY_CARD_TYPE::SUMMONS      ->value => $f([ 1, true, CITY_CARD_EFFECT::END->value    , clienttranslate('Summons to Court'),      ]), 
            CITY_CARD_TYPE::FULL_STOR    ->value => $f([ 1, true, CITY_CARD_EFFECT::END->value    , clienttranslate('Full Storehouses'),      ]), 
            CITY_CARD_TYPE::KIMONO_DRESS ->value => $f([ 1, true, CITY_CARD_EFFECT::END->value    , clienttranslate('Kimono Dressmaker'),     ]), 
            CITY_CARD_TYPE::NIGHT_MARKET ->value => $f([ 1, true, CITY_CARD_EFFECT::REVEAL->value , clienttranslate('Night Market'),          ]), 
            CITY_CARD_TYPE::CALL_TO_PORT ->value => $f([ 1, true, CITY_CARD_EFFECT::END->value    , clienttranslate('Call to Port'),          ]), 
            CITY_CARD_TYPE::SHRINE_PIL   ->value => $f([ 1, true, CITY_CARD_EFFECT::END->value    , clienttranslate('Shrine Pilgrimage'),     ]), 
            CITY_CARD_TYPE::OPPORTUNIST  ->value => $f([ 1, true, CITY_CARD_EFFECT::REVEAL->value , clienttranslate('Opportunist'),           ]), 
            CITY_CARD_TYPE::TEAHOUSE     ->value => $f([ 1, true, CITY_CARD_EFFECT::END->value    , clienttranslate('Teahouse Proprietor'),   ]), 
            CITY_CARD_TYPE::SAKE_BREW    ->value => $f([ 1, true, CITY_CARD_EFFECT::END->value    , clienttranslate('Sake Brewmaster'),       ]), 
            CITY_CARD_TYPE::TRAVEL_TRO   ->value => $f([ 1, true, CITY_CARD_EFFECT::REVEAL->value , clienttranslate('Traveling Troupe'),      ]), 
            
        ];
    }

}
