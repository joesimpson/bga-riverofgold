<?php
namespace ROG\Helpers;

use ROG\Core\Game;
use ROG\Core\Globals;
use ROG\Core\Notifications;
use ROG\Managers\Cards;
use ROG\Managers\Meeples;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Managers\Tiles;
use ROG\Models\AutomaActionType;
use ROG\Models\AutomaPlayer;
use ROG\Models\CITY_CARD_EFFECT;
use ROG\Models\CITY_CARD_TYPE;
use ROG\Models\CustomerCard;
use ROG\Models\MAIN_ACTION;
use ROG\Models\Meeple;
use ROG\Models\Player;
use ROG\Models\ScenarioType;
use ROG\Models\SCORING_CITY_TYPE;

abstract class Utils 
{

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

    public static function randomDieFace() : int
    {
        return DIE_FACES[array_rand(DIE_FACES)];
    }

    public static function getClanName(int $clanId) : string
    {
        switch($clanId){
            case CLAN_CRAB:     return clienttranslate('Crab Clan');
            case CLAN_MANTIS:   return clienttranslate('Mantis Clan');
            case CLAN_CRANE:    return clienttranslate('Crane Clan');
            case CLAN_SCORPION: return clienttranslate('Scorpion Clan');
            case CLAN_PHOENIX:  return clienttranslate('Phoenix Clan');
            case CLAN_LION:     return clienttranslate('Lion Clan');
            case CLAN_DRAGON:   return clienttranslate('Dragon Clan');
            case CLAN_UNICORN:  return clienttranslate('Unicorn Clan');
        }
        return '';
    }

    public static function resourceName(int $resourceType) : string
    {
        $resourceName = '';
        switch($resourceType){
            case RESOURCE_TYPE_SILK: $resourceName = clienttranslate('Silk'); break;
            case RESOURCE_TYPE_POTTERY: $resourceName = clienttranslate('Porcelain'); break;
            case RESOURCE_TYPE_RICE: $resourceName = clienttranslate('Rice'); break;
            case RESOURCE_TYPE_MOON: $resourceName = clienttranslate('Divine favor Limit'); break;
            case RESOURCE_TYPE_SUN: $resourceName = clienttranslate('Divine favor'); break;
            case RESOURCE_TYPE_MONEY: $resourceName = clienttranslate('Koku'); break;
            case RESOURCE_TYPE_CITY_CARD: $resourceName = clienttranslate('city card claimed'); break;
        }
        return $resourceName;
    }

    public static function countCardsCost(Collection $cards, int $resourceToCount) : int
    {
        $nbResources = $cards->map(function($card) use ($resourceToCount) {
            $cost = $card->getCost();
            if(array_key_exists($resourceToCount,$cost)){
            return $cost[$resourceToCount];
            }
            return 0;
        })->sum();
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
    
    public static function isPlayerActionDone() : bool
    {
        $mainActionDone = Globals::getTurnMainActionDone();
        $mainActionDone = isset($mainActionDone) && $mainActionDone !='null' && $mainActionDone !='';
        return $mainActionDone;
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
        $color = array_search($clan,CLANS_COLORS);
        if($color) return $color;
        return '000000';//black unless clan is known
    }
    
    /**
     * @return array [ 'color'=>$color, 'clan'=>$clan]
     */
    public static function pickNextAvailableClan(Collection $players) : array {
        $assignedClans = $players->map( function (Player $player) { return $player->getClan();})->toArray();
        $assignedClans[] = Globals::getAutomaClan();
        $assignedClans[] = Globals::getRogueClan();
        $assignedClans[] = Globals::getScorpionEnemy();
        $assignedClans[] = Globals::getLionEnemy();
        $unAssignedClans = [];
        foreach(CLANS_COLORS as $color => $clan){
            if(in_array($clan,$assignedClans)){
                continue;
            }
            $unAssignedClans[] = [ 'color'=>$color, 'clan'=>$clan];
        }
        shuffle($unAssignedClans);
        $clanToPick = array_shift($unAssignedClans);
        return $clanToPick;
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

    public static function isGameWithCityOfLies() : bool {
        $city = Globals::getOptionCity();
        if(!isset($city)) return false;
        if(OPTION_CITY_OF_LIES_OFF == $city){
            return false;
        }
        return true;
    }
    
    public static function isGameWithScenarios() : bool {
        $scenarios = Globals::getOptionScenarios();
        if(!isset($scenarios)) return false;
        if(OPTION_SCENARIOS_OFF == $scenarios){
            return false;
        }
        return true;
    }

    public static function getEnumsUI() : array {
        return [
            'AutomaActionType' => AutomaActionType::ui(),
            'ScenarioType' => ScenarioType::ui(),
            'CITY_CARD_TYPE' => CITY_CARD_TYPE::ui(),
            'CITY_CARD_EFFECT' => CITY_CARD_EFFECT::ui(),
            'SCORING_CITY_TYPE' => SCORING_CITY_TYPE::ui(),
        ];
    }

    /**
     * Apply rule to move the rogue ship if it is currently in one of the river spaces $riverSpaces
     */
    public static function moveRogueShipFrom(Player $player,array $riverSpaces) {
        if($player instanceof AutomaPlayer) return;
        $ship = Meeples::getRogueShip();
        if(!isset($ship)) return;
        $pId = $player->getId();
        Game::get()->trace(__CLASS__.".".__FUNCTION__."($pId)".json_encode($riverSpaces));
        $shipPos = $ship->getPosition();
        if(!in_array($shipPos, $riverSpaces)){
            Game::get()->trace("Rogue ship $shipPos is not in list of river spaces to check : ".json_encode($riverSpaces));
            return;
        }

        $boats = Meeples::getBoats($pId);
        $boatsRiverSpaces = $boats->map(function(Meeple $b) {return $b->getPosition();})->toArray();
        Game::get()->trace("boatsRiverSpaces : ".json_encode($boatsRiverSpaces));
        $playerShoreSpaces = Tiles::getPlayerBuildingTilesShoreSpace($pId);
        $riverSpaces = ShoreSpaces::getUniqueAdjacentRiverSpaces($playerShoreSpaces);
        Game::get()->trace("buildings riverSpaces : ".json_encode($riverSpaces));

        $fromPos = $shipPos;
        $moveShip = true;
        while($shipPos > 0 && $moveShip){
            $moveShip = false;

            //move it upriver until it reaches a river space that does not contain your ships 
            // and is not adjacent to your buildings.
            if(in_array($shipPos,$boatsRiverSpaces)){
                $moveShip = true;
            }

            else if(in_array($shipPos,$riverSpaces)){
                $moveShip = true;
            }
            
            if($moveShip) $shipPos--;
            if($shipPos > 0) $ship->setPosition($shipPos);
        }
    
        if($fromPos != $shipPos){
            //NOTIFY ALL MOVES in 1
            Notifications::moveRogueShip($player,$ship);
        }
        if($shipPos == 0){
            //If it moves past the top river space, remove it from the board.
            Meeples::removeShip($player,$ship, clienttranslate('${player_name} removes the rogue ship from the board'));
        }
    }

    /**
     * Go to bonus transition after current turn action
     * @param Player $player
     * @param bool $changeActivePlayer (Default false) 
     * @param bool $applyNextState (Default true) : manual change of state
     * @return bool true if state changed
     */
    public static function goToBonusStepIfNeeded(?Player $player, bool $changeActivePlayer = false, bool $applyNextState = true) : bool
    {
        if(!isset($player)) return false;
        //refresh datas
        $updatedPlayer = Players::get($player->getId());
        $bonuses = $updatedPlayer->getBonuses();
        if(isset($bonuses) && count($bonuses)>0){
            $updatedPlayer->giveExtraTime();
            if($changeActivePlayer){
                //Change active player when in a game state !
                Players::changeActive($updatedPlayer->getId());
                Game::get()->addCheckpoint(ST_BONUS_CHOICE);
            }
            $currentState = Game::get()->gamestate->getCurrentMainStateId();
            Globals::setStateBeforeBonus($currentState);
            if($applyNextState) Game::get()->gamestate->nextState('bonus');
            return true;
        }
        return false;
    }
}
