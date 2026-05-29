<?php

namespace ROG\Managers;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Helpers\Utils;
use ROG\Models\AutomaActionCard;
use ROG\Models\AutomaActionType;
use ROG\Models\AutomaPlayer;

/* Class to manage all the Automa cards */

class AutomaCards extends Cards
{
    
    public static function countAutomaActionSize(string $deckLocation) : int
    {
        return self::DB()
                ->where('subType', CARD_TYPE_AUTOMA_ACTION)
                ->where(static::$prefix . 'location', $deckLocation)
                ->count();
    }
    public static function countAutomaActionDeckSize(string $deckLocation) : int
    {
        return AutomaCards::countAutomaActionSize($deckLocation);
    }
    public static function giveActionCardToAutoma(AutomaPlayer $player, ) : AutomaActionCard
    {
        Game::get()->trace(__CLASS__.".".__FUNCTION__);
        $fromDeck = CARD_AUTOMA_LOCATION_DECK;
        $toDeck = CARD_AUTOMA_LOCATION_PLAYED;
        $deckSize = AutomaCards::countAutomaActionDeckSize($fromDeck);
        if($deckSize == 0){
            //if fromDeck is empty, reshuffle
            AutomaCards::reshuffleAutomaActionDeck($player,$toDeck, $fromDeck);
        }
        $actionCard = AutomaCards::getTopOf($fromDeck);
        AutomaCards::insertOnTop($actionCard->getId(), $toDeck);
        $actionCard->setLocation($toDeck);
        Notifications::giveActionCardToAutoma($player,$actionCard);
        return $actionCard;
    }

    /**
     * @param string $fromDeck specifying the deck to clear
     * @param string $toDeck specifying the deck to reshuffle
     * @return int decksize after reshuffle
     */
    public static function reshuffleAutomaActionDeck(AutomaPlayer $player, string $fromDeck,string $toDeck) : int
    {
        Game::get()->trace(__CLASS__.".".__FUNCTION__);
        self::moveAllInLocation( $fromDeck, $toDeck, );
        self::shuffle($toDeck);
        $decksize = AutomaCards::countAutomaActionSize($toDeck);
        Notifications::reshuffleAutomaActionDeck($player, $decksize );
        return $decksize;
    }
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
