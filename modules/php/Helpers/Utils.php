<?php
namespace ROG\Helpers;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Models\CustomerCard;
use ROG\Models\MAIN_ACTION;
use ROG\Models\Meeple;
use ROG\Models\Player;

abstract class Utils 
{
    public static function filter(&$data, $filter)
    {
        $data = array_values(array_filter($data, $filter));
    }

    public static function die($args = null)
    {
        if (is_null($args)) {
            throw new \BgaVisibleSystemException(
                implode('<br>', self::$logmsg)
            );
        }
        throw new \BgaVisibleSystemException(json_encode($args));
    }

    /**
     * @param int $num1 
     * @param int $num2
     * @return int
     */
    public static function positive_modulo($num1,$num2)
    {
        $r = $num1 % $num2;
        if ($r < 0)
        {
            $r += abs($num2);
        }
        return $r;
    }

    public static function gameVersion() : int
    {
        $options = Game::get()->bga->tableOptions;
        $gameVersion = $options->get(BGA_GAMESTATE_GAMEVERSION);
        return intval($gameVersion);
    }
    ////////////////////////////////////////////////////////////////
    //////// GAME SPECIFIC
    ////////////////////////////////////////////////////////////////
    ///**
    // * @param int region
    // * @return array of int 
    // * 
    // * Examples : 
    // * 1 -> [6,2],
    // * 2 -> [1,3],
    // * 3 -> [2,4],
    // * 4 -> [3,5],
    // * 5 -> [4,6],
    // * 6 -> [5,1],
    // */
    //public static function getAdjacentRegions($region)
    //{
    //    $regions = [];
    //    //$nbRegions = count(REGIONS);
    //    //$regions[] = ($region - 1) % $nbRegions +1;
    //    //$regions[] = ($region + 1) % $nbRegions +1
    //    switch($region){
    //        //Maybe we cannot consider other side of board as adjacent
    //        case 1: return [2];
    //        case 2: return [1,3];
    //        case 3: return [2,4];
    //        case 4: return [3,5];
    //        case 5: return [4,6];
    //        //Maybe we cannot consider other side of board as adjacent
    //        case 6: return [5];
    //    }
    //    return $regions;
    //}

    public static function countCardsCost(Collection $cards, int $resourceToCount) : int
    {
        $nbResources = $cards->map(function($card) use ($resourceToCount) {
            $cost = $card->getCost();
            if(array_key_exists($resourceToCount,$cost)){
            return $cost[$resourceToCount];
            }
            return 0;
        })->reduce(function ($ax, $dx) {
            return $ax + (int)$dx;
        }, 0);
        return $nbResources;
    }
    
    public static function playTradersAbilities(Player &$player) : void
    {
        $regionDie = $player->getDie();
        foreach(TRADER_TYPES as $traderType){
            if($regionDie == Cards::getCustomerRegionFromType($traderType) 
                && Cards::hasPlayerDeliveredOrder($player->getId(),$traderType)){
                CustomerCard::playOngoingAbility($player,$traderType);
            }
        }
    }
    
    public static function getShindoshiMarker(int $player_id, int $type) : Meeple | null
    {
        $cardMarkers = Meeples::getPlayerCardsMarkers($player_id,CARD_TYPE_CUSTOMER,[$type]);
        if($cardMarkers->count() > 0){
            return $cardMarkers->first();
        }
        return null;
    }
    
    /**
     * @param int $stack : 1 or 2
     * @return bool true if we don't want to see next tiles in that stack until some actions
     */
    public static function hideTopEraDeck(int $stack) : bool
    {
        if(Globals::getTurnMainActionDone() == MAIN_ACTION::BUILD->value) {
            $builtTileLocation = Globals::getLastBuiltLocationOrigin();
            if($stack == 1 && $builtTileLocation == TILE_LOCATION_BUILDING_DECK_ERA_1 ){
                return true;
            }
            if($stack == 2 && $builtTileLocation == TILE_LOCATION_BUILDING_DECK_ERA_2 ){
                return true;
            }
        }
        return false;
    }

    public static function getInfluenceTrackRewards(int $region){

        $customTracks = Globals::getRegionCustomTracks();
        if(isset($customTracks) && array_key_exists($region,$customTracks)){
            $trackId = $customTracks[$region];
            if(array_key_exists($trackId,CUSTOM_REGION_TRACKS)){
                return CUSTOM_REGION_TRACKS[$trackId];
            }
        } 
        return INFLUENCE_TRACK_REWARDS[$region];
    }

    
    /**
     * @param array $array
     * @param int $key in array
     * @param int $value to change from array datas if the key is found in the array, else nothing is done
     */
    public static function updateDataFromArray ($array, $key, &$value) {
        if(array_key_exists($key,$array)) $value = $array[$key];
    }
    
    /**
     * @return string player color linked to player clan
     */
    public static function getPlayerClanColor(int $clan) : string {
        return array_search($clan,CLANS_COLORS);
    }

    /**
     * @return int fake automa player id OR null if no automa in current game
     */
    public static function getAutomaPId() : int |null {
        if(Utils::isGameWithAutoma()){
            return AUTOMA_PLAYER_ID;
        }
        return null;
    }

    public static function getAutomaName() : string {
        return clienttranslate("Seishin");
    }
    public static function getAutomaColor() : string {
        if(Utils::isGameWithAutoma()){
            return Utils::getPlayerClanColor(Globals::getAutomaClan());
        }
        return '';
    }

    public static function getAutomaDifficultyName(int $level) : string {
        switch($level){
            case OPTION_SEISHIN_LEVEL_1: return clienttranslate("Easy");
            case OPTION_SEISHIN_LEVEL_2: return clienttranslate("Normal");
            case OPTION_SEISHIN_LEVEL_3: return clienttranslate("Hard");
            case OPTION_SEISHIN_LEVEL_4: return clienttranslate("Expert");
            case OPTION_SEISHIN_LEVEL_5: return clienttranslate("Master");
        }
        return '';
    }
    
    public static function isGameWithAutoma() : bool {
        $difficulty = Globals::getOptionSeishin();
        if(!isset($difficulty)) return false;
        if(OPTION_SEISHIN_OFF == $difficulty){
            return false;
        }
        return true;
    }
}
