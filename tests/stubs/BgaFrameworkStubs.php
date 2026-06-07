<?php

declare(strict_types=1);

namespace Bga\GameFramework;

use Exception;
use Tests\Utils\TestDatas;

class UserException extends \Exception
{
    /**
     * @param string|NotificationMessage $message Error message to be surrounded by `clienttranslate`, with optional arguments.
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}

enum StateType: string
{
    case ACTIVE_PLAYER = 'activeplayer';
    case MULTIPLE_ACTIVE_PLAYER = 'multipleactiveplayer';
    case PRIVATE = 'private';
    case GAME = 'game';
    case MANAGER = 'manager';
}

class GamestateMachine
{

    static int $test_current_state = ST_GAME_SETUP;

    public function changeActivePlayer(int $playerId): void
    {
        TestDatas::$test_activePlayerId = $playerId;
    }
    public function nextState(string $transition = ''): void
    {
        switch(GamestateMachine::$test_current_state){
            default:
                GamestateMachine::$test_current_state = getMyMachineStates()[GamestateMachine::$test_current_state]['transitions'][$transition];
                break;
        }
        
    }
    final public function jumpToState(int|string $next_state): void
    {
        GamestateMachine::$test_current_state = $next_state;
    }

    public function setAllPlayersMultiactive(): void
    {
    }

    final public function setAllPlayersNonMultiactive(string $next_state): bool
    {
        return false;
    }
    final public function setPlayerNonMultiactive(int $player, string $nextState): bool
    {
        return false;
    }
}


abstract class Table
{
    public function __construct()
    {
    }

    public function error(string $message): void
    {
        logForTests($message, "ERROR");
    }
    
    public function dump(string $message, mixed $object): void
    {
        logForTests($message, "DUMP");
        logForTests($object, "DUMP");
    }
    final public function debug(string $message): void
    {
        logForTests($message, "DEBUG");
    }
    final public function trace(string $message): void
    {
        logForTests($message, "TRACE");
    }
    final public function getGameinfos(): array
    {
        return [
            'player_colors' => array( 
                // --------- V1 :
                "ff0000", // RED
                "72c3b1", // Cyan for Mantis
                "0000ff", // BLUE
                "ffffff", // WHITE
                // --------- V2 :
                "f07f16", // Orange
                "ffff00", // Yellow
                "298a47", // Green for Dragon
                "982fff", // Purple
            ),
            'favorite_colors_support' => true,
        ];
    }
    final public function reattributeColorsBasedOnPreferences(array $players, array $colors): void
    {
    }
    protected function initGameStateLabels(array $stateLabels): void
    {
    }
    public function getGameStateValue(string $label, ?int $default = null): int|string
    {
        switch($label){
            case 'logging' : return 0;
        }
        return '0';
    }
    
    final public function setGameStateInitialValue(string $label, int $value): void
    {
    }

    public function activePrevPlayer(): int
    {
        TestDatas::$test_activePlayerId = 2;
        return TestDatas::$test_activePlayerId;
    }

    public function activeNextPlayer(): int|string
    {
        TestDatas::$test_activePlayerId = 2;
        return TestDatas::$test_activePlayerId;
    }

    public function getPlayerAfter(int $playerId): int
    {
        return 0;
    }

    public function giveExtraTime(int $playerId, ?int $specificTime = null): void
    {
    }

    public static function getUniqueValueFromDb(string $sql): mixed
    {
        logForTests("getUniqueValueFromDb: $sql");
        switch($sql){
            case "SELECT COUNT(*) FROM `player`":
                return count(TestDatas::$players);
            case "SELECT MAX(`player_no`) FROM `player`":
                return max(array_map(function ($p) {return $p['player_no'];},TestDatas::$players,));
            case "SELECT COUNT(*) FROM `cards` WHERE (`card_location` = 'deck')":
                return count(array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'deck';}));
            case "SELECT COUNT(*) FROM `cards` WHERE (`card_location` = 'discard')":
                return count(array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'discard';}));
            case "SELECT COUNT(*) FROM `cards` WHERE (`card_location` = 'clans_draft')":
                return count(array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'clans_draft';}));
            case "SELECT COUNT(*) FROM `cards` WHERE (`card_location` = 'clans_assigned')":
                return count(array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'clans_assigned';}));
            case "SELECT COUNT(*) FROM `cards` WHERE (`card_location` = 'aut_deck')":
                return count(array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'aut_deck';}));
        }
        if (preg_match("/^SELECT COUNT\(\*\) FROM `cards` WHERE `player_id` = (?P<player_id>.*) AND \`card_location` = '(?P<card_location>.*)'\ AND \(`type` IN \((?P<types>.*)\)\)$/", $sql, $matches) == 1) {
            $types = explode(',', str_replace("'","",$matches['types']) );
            $count = count(array_filter(TestDatas::$cards,function ($card) use($matches, $types ) {return $card['card_location'] == $matches['card_location'] && $card['player_id'] == $matches['player_id'] && in_array($card['type'], $types );}));
            logForTests("getUniqueValueFromDb: count is $count for ".json_encode($types));
            return $count;
        }
        if (preg_match("/^SELECT COUNT\(\*\) FROM `cards` WHERE `player_id` = (?P<player_id>.*) AND \(`card_location` = '(?P<card_location>.*)'\)$/", $sql, $matches) == 1) {
            $count = count(array_filter(TestDatas::$cards,function ($card) use($matches, ) {return $card['card_location'] == $matches['card_location'] && $card['player_id'] ==intval( $matches['player_id']) ;}));
            logForTests("getUniqueValueFromDb: count is $count ");
            return $count;
        }
        if (preg_match("/^SELECT COUNT\(\*\) FROM `cards` WHERE `player_id` = (?P<player_id>.*) AND \(`card_location` = '(?P<card_location>.*)'\) AND \(`type` = (?P<type>.*)\)$/", $sql, $matches) == 1) {
            $count = count(array_filter(TestDatas::$cards,function ($card) use($matches, ) {return $card['card_location'] == $matches['card_location'] && $card['player_id'] ==intval( $matches['player_id']) && $card['type']== intval($matches['type']);}));
            logForTests("getUniqueValueFromDb: count is $count ");
            return $count;
        }
        if (preg_match("/^SELECT COUNT\(\*\) FROM `cards` WHERE `subType` = (?P<subtype>.*) AND `card_location` = '(?P<card_location>.*)'$/", $sql, $matches) == 1) {
            $subtype = $matches['subtype'];
            $card_location = $matches['card_location'];
            $count = count(array_filter(TestDatas::$cards,function ($card) use( $card_location, $subtype ) {return $card['card_location'] == $card_location && $card['subtype'] == $subtype ;}));
            logForTests("getUniqueValueFromDb: count is $count  ");
            return $count;
        }
        if (preg_match("/^SELECT MAX\(`(?P<field>.*)`\) FROM `cards` WHERE \(`card_location` = '(?P<card_location>.*)'\)$/", $sql, $matches) == 1) {
            $field = $matches['field'];
            $card_location = $matches['card_location'];
            $filtered = array_filter(TestDatas::$cards,function ($card) use( $card_location, ) {return $card['card_location'] == $card_location;});
            $max = 0;
            foreach($filtered as $card) {
                $max = max($max, $card[$field]);
            }
            logForTests("getUniqueValueFromDb: MAX($field ) is $max  ");
            return $max;
        }
        if (preg_match("/^SELECT COUNT\(\*\) FROM `tiles` WHERE \(`tile_location` = '(?P<tile_location>.*)'\)$/", $sql, $matches) == 1) {
            $tile_location = $matches['tile_location'];
            $count = count(array_filter(TestDatas::$tiles,function ($card) use($tile_location,) {return $card['tile_location'] == $tile_location;}));
            logForTests("getUniqueValueFromDb: count is $count for tile_location $tile_location");
            return $count;
        }
        if (preg_match("/^SELECT COUNT\(\*\) FROM `meeples` WHERE (`player_id` = (?P<player_id>.*) AND )*`meeple_location` = '(?P<meeple_location>.*)' AND `meeple_state` = (?P<meeple_state>\d+)$/", $sql, $matches) == 1) {
            $player_id = intval($matches['player_id']);
            $meeple_location = $matches['meeple_location'];
            $meeple_state = intval($matches['meeple_state']);
            $count = count(array_filter(TestDatas::$tokens,function ($token) use($player_id,$meeple_location, $meeple_state) {
                return ($player_id ==0 || $player_id>0 && $token['player_id'] == $player_id) && $token['meeple_location'] == $meeple_location && $token['meeple_state'] == $meeple_state ;
            }));
            logForTests("getUniqueValueFromDb: count is $count for meeple_location $player_id, $meeple_location, $meeple_state");
            return $count;
        }
        if (preg_match("/^SELECT COUNT\( distinct meeple_location\) FROM `meeples` WHERE `player_id` = (?P<pid>[\d|\-]+) AND \(`meeple_location` IN \((?P<meeple_locations>.*)\)\)$/", $sql, $matches) == 1) {
            $pid = intval($matches['pid']);
            $meeple_locations = explode(',', str_replace("'","",$matches['meeple_locations']) );
            //logForTests("getUniqueValueFromDb: tokens before filter = ".json_encode(TestDatas::$tokens));
            $tokens = array_filter(TestDatas::$tokens,function ($t) use($pid,$meeple_locations) {return $t['player_id'] == $pid && in_array($t['meeple_location'], $meeple_locations);});
            //logForTests("getUniqueValueFromDb: tokens after filter = ".json_encode($tokens));
            $locations = array_map(function ($t) {return $t['meeple_location'];}, $tokens,);
            $count = count( array_unique($locations));
            logForTests("getUniqueValueFromDb: count is $count");
            return $count;
        }
        if (preg_match("/^SELECT COUNT\( distinct meeple_state\) FROM `meeples` WHERE `player_id` NOT IN \('(?P<pid>.*)'\) AND `meeple_location` = '(?P<meeple_location>.*)' AND \(`meeple_state` IN \((?P<meeple_states>.*)\)\)$/", $sql, $matches) == 1) {
            $pidNot = intval($matches['pid']);
            $meeple_states = explode(',', str_replace("'","",$matches['meeple_states']) );
            $meeple_location = ($matches['meeple_location']);
            $tokens = array_filter(TestDatas::$tokens,function ($t) use($pidNot,$meeple_location, $meeple_states) {return $t['player_id'] != $pidNot && $t['meeple_location'] == $meeple_location && in_array($t['meeple_state'], $meeple_states);});
            logForTests("getUniqueValueFromDb: tokens after filter = ".json_encode($tokens));
            $states = array_map(function ($t) {return $t['meeple_state'];}, $tokens,);
            $count = count( array_unique($states));
            logForTests("getUniqueValueFromDb: count is $count");
            return $count;
        }
        logForTests("getUniqueValueFromDb: /?\ ");
        return null;
    }

    public function getCollectionFromDb(string $sql, bool $singleColumn = false): array
    {
        logForTests("getCollectionFromDb: $sql");
        logForTests(" /?\ __ don't know what to return for this request __ /?\ [$sql] ");
        return [];
    }
    public static function getObjectListFromDB(string $sql, bool $bUniqueValue = false): array
    {
        logForTests("getObjectListFromDB: $sql");
        
        switch($sql){
            case 'SELECT name AS `result_associative_index` , `value` , `name` FROM `global_variables`':
                logForTests("MOCK select globals");
                return [
                    ['name' => 'turn', 'value' => 1, 'result_associative_index' => 'turn'],
                    ['name' => 'era', 'value' => 1, 'result_associative_index' => 'era'],
                    ['name' => 'turnPlayer', 'value' => 1, 'result_associative_index' => 'turnPlayer'],
                    ['name' => 'firstPlayer', 'value' => 1, 'result_associative_index' => 'firstPlayer'],
                    ['name' => 'endScoring', 'value' => '[]', 'result_associative_index' => 'endScoring'],
                ];
            case 'SELECT *, player_id AS `result_associative_index` FROM `player` WHERE  `player_id` = 1 LIMIT 1': 
                logForTests("MOCK select  player one ");
                return [
                    TestDatas::$players[1],
                    ];
            case "SELECT *, player_id AS `result_associative_index` FROM `player` WHERE  `player_id` = '0' LIMIT 1":
            case 'SELECT *, player_id AS `result_associative_index` FROM `player` WHERE  `player_id` = 2 LIMIT 1': 
                logForTests("MOCK select  player two ");
                return [
                    TestDatas::$players[2],
                    ];
            case 'SELECT *, player_id AS `result_associative_index` FROM `player` WHERE  `player_id` = 3 LIMIT 1': 
                logForTests("MOCK select  player 3 ");
                return [ TestDatas::$players[3], ];
            case 'SELECT *, player_id AS `result_associative_index` FROM `player` WHERE  `player_id` = 4 LIMIT 1': 
                logForTests("MOCK select  player 4 ");
                return [ TestDatas::$players[4], ];
            case 'SELECT *, player_id AS `result_associative_index` FROM `player` WHERE  `player_id` = 5 LIMIT 1': 
                logForTests("MOCK select  player 5 ");
                return [ TestDatas::$players[5], ];
            case 'SELECT player_score,player_id FROM `player` WHERE `player_id` = 2':
                return [
                    TestDatas::$players[2],
                ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('1'))":
                return [
                    TestDatas::$cards[1],
                ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('2'))":
                return [
                    TestDatas::$cards[2],
                ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('3'))": return [ TestDatas::$cards[3], ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('11'))": return [ TestDatas::$cards[11], ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('12'))": return [ TestDatas::$cards[12], ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('13'))": return [ TestDatas::$cards[13], ];
                
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('101'))":
                return [
                    TestDatas::$cards[101],
                ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('102'))":
                return [
                    TestDatas::$cards[102],
                ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('103'))":
                return [
                    TestDatas::$cards[103],
                ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('104'))":
                return [
                    TestDatas::$cards[104],
                ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_location` = 'clans_draft')":
                //return [
                //    TestDatas::$cards[101],
                //    TestDatas::$cards[102],
                //    TestDatas::$cards[103],
                //    TestDatas::$cards[104],
                //];
                $draft = array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'clans_draft';});
                logForTests("MOCK draft ".json_encode($draft));
                return $draft;
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('2','3'))":
                return [
                    TestDatas::$cards[2],
                    TestDatas::$cards[3],
                ];

            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_location` = 'clans_draft') ORDER BY card_state DESC LIMIT 1":
                $draft = array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'clans_draft';});
                logForTests("MOCK draft ".json_encode($draft));
                return count($draft) > 0 ? [ $draft[array_keys($draft)[0]], ] : [];
            case "SELECT *, card_id AS `result_associative_index` FROM `cards` WHERE `player_id` = 1 AND `card_location` = 'clans_assigned'":
                $assigned = array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'clans_assigned' && $card['player_id'] == 1;});
                return $assigned;
            case "SELECT *, card_id AS `result_associative_index` FROM `cards` WHERE `player_id` = 2 AND `card_location` = 'clans_assigned'":
                $assigned = array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'clans_assigned' && $card['player_id'] == 2;});
                return $assigned;
            case "SELECT *, card_id AS `result_associative_index` FROM `cards` WHERE `player_id` = -123 AND `card_location` = 'clans_assigned'":
                $assigned = array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'clans_assigned' && $card['player_id'] == -123;});
                return $assigned;
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_location` = 'deck') ORDER BY card_state DESC LIMIT 1":
                $filtered = array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'deck';});
                $mockDeck = count($filtered) > 0 ? [ $filtered[array_keys($filtered)[0]], ] : [];
                logForTests("MOCK deck ".json_encode($mockDeck));
                return $mockDeck;
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_location` = 'deck') ORDER BY card_state DESC LIMIT 2":
                $filtered = array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'deck';});
                $mockDeck = count($filtered) > 1 ? [ $filtered[array_keys($filtered)[0]], $filtered[array_keys($filtered)[1]] ] : [];
                logForTests("MOCK deck ".json_encode($mockDeck));
                return $mockDeck;
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_location` = 'deck')":
                $filtered = array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'deck';});
                return $filtered;
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_location` = 'h')":
                $filtered = array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'h';});
                return $filtered;
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE `player_id` = 1 AND (`card_location` = 'h')":
                $filtered = array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'h' && $card['player_id'] == 1;});
                return $filtered;

            case "SELECT player_score,player_id FROM `player` WHERE `player_id` = 1":
            case "SELECT player_id AS `result_associative_index` , `player_score` FROM `player` WHERE `player_id` = 1":
                return[
                    TestDatas::$players[1],
                ];
            case "SELECT player_score,player_id FROM `player` WHERE `player_id` = 2":
            case "SELECT player_id AS `result_associative_index` , `player_score` FROM `player` WHERE `player_id` = 2":
                return[
                    TestDatas::$players[2],
                ];
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_id` IN ('1'))":
                return [
                        TestDatas::$tiles[1],
                    ];

            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_location` = 'm') ORDER BY tile_state DESC LIMIT 3":
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_location` = 'm')":
                return [
                        TestDatas::$tiles[1],
                        TestDatas::$tiles[2],
                        TestDatas::$tiles[3],
                    ];
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_location` = 's')":
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_location` = 's') ORDER BY tile_state DESC LIMIT":
                return [
                        TestDatas::$tiles[11],
                        TestDatas::$tiles[12],
                        TestDatas::$tiles[13],
                        TestDatas::$tiles[14],
                        TestDatas::$tiles[15],
                        TestDatas::$tiles[16],
                    ];
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_location` = 'bd1')":
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_id` IN ('21','22','23','24','25','26'))":
                return [
                        TestDatas::$tiles[21],
                        TestDatas::$tiles[22],
                        TestDatas::$tiles[23],
                        TestDatas::$tiles[24],
                        TestDatas::$tiles[25],
                        TestDatas::$tiles[26],
                    ];
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_id` IN ('31'))":
                return [
                        TestDatas::$tiles[31],
                    ];
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_id` IN ('32'))":
                return [
                        TestDatas::$tiles[32],
                    ];
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_id` IN ('33'))":
                return [
                        TestDatas::$tiles[33],
                    ];
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_id` IN ('34'))":
                return [
                        TestDatas::$tiles[34],
                    ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('1'))": return [ TestDatas::$tokens[1], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('2'))": return [ TestDatas::$tokens[2], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('3'))": return [ TestDatas::$tokens[3], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('4'))": return [ TestDatas::$tokens[4], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('5'))": return [ TestDatas::$tokens[5], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('6'))": return [ TestDatas::$tokens[6], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('11'))": return [ TestDatas::$tokens[11], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('12'))": return [ TestDatas::$tokens[12], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('13'))": return [ TestDatas::$tokens[13], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('14'))": return [ TestDatas::$tokens[14], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('15'))": return [ TestDatas::$tokens[15], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE (`meeple_id` IN ('16'))": return [ TestDatas::$tokens[16], ];
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE `player_id` = 1 AND (`meeple_location` = 'i-1')":
                $filtered = array_filter(TestDatas::$tokens,function ($token) {return $token['meeple_location'] == 'i-1' && $token['player_id'] == 1;});
                return $filtered;
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE `player_id` = 1 AND (`meeple_location` = 'i-3')":
                $filtered = array_filter(TestDatas::$tokens,function ($token) {return $token['meeple_location'] == 'i-3' && $token['player_id'] == 1;});
                return $filtered;
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE `player_id` = 1 AND (`meeple_location` = 'i-6')":
                $filtered = array_filter(TestDatas::$tokens,function ($token) {return $token['meeple_location'] == 'i-6' && $token['player_id'] == 1;});
                return $filtered;
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE `player_id` = 1 AND (`meeple_location` = 'artisan-1')":
                $filtered = array_filter(TestDatas::$tokens,function ($token) {return $token['meeple_location'] == 'artisan-1' && $token['player_id'] == 1;});
                return $filtered;
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE `player_id` = 2 AND (`meeple_location` = 'i-1')":
                $filtered = array_filter(TestDatas::$tokens,function ($token) {return $token['meeple_location'] == 'i-1' && $token['player_id'] == 2;});
                return $filtered;
            case "SELECT meeple_id AS `result_associative_index` , `meeple_id` , `meeple_location` , `meeple_state` , `type` , `player_id` FROM `meeples` WHERE `player_id` = 2 AND (`meeple_location` = 'artisan-1')":
                $filtered = array_filter(TestDatas::$tokens,function ($token) {return $token['meeple_location'] == 'artisan-1' && $token['player_id'] == 2;});
                return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `cards` WHERE \(`card_id` IN \((?P<card_ids>.*)\)\)$/", $sql, $matches) == 1) {
            $filtered = [];
            $card_ids = explode(',',str_replace("'","",$matches['card_ids']));
            foreach($card_ids as $cardIdString){
                $card_id = intval($cardIdString);
                if(!array_key_exists($card_id,TestDatas::$cards)) continue;
                $filtered[] = TestDatas::$cards[$card_id];
            }
            logForTests("MOCK select cards with ids ".json_encode($card_ids).": ".json_encode($filtered));
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `cards` WHERE \(`card_location` = '(?P<card_location>.*)'\)$/", $sql, $matches) == 1) {
            $card_location = $matches['card_location'];
            $filtered = array_filter(TestDatas::$cards,function ($card) use ($card_location, ){return $card['card_location'] == $card_location ;});
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `cards` WHERE \(`card_location` = '(?P<card_location>.*)'\)( ORDER BY card_state (ASC|DESC))?( LIMIT (?P<limit>\d+))?$/", $sql, $matches) == 1) {
            //TODO SORT card_state
            $card_location = $matches['card_location'];
            $limit = array_key_exists('limit',$matches) ? intval($matches['limit']) : null;
            $filtered = array_filter(TestDatas::$cards,function ($card) use ($card_location,){return  $card['card_location'] == $card_location;});
            if(isset($limit)) $filtered = array_slice($filtered, 0, $limit, true);
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `cards` WHERE `player_id` = (?P<player_id>.*) AND \(`card_location` = '(?P<card_location>.*)'\)$/", $sql, $matches) == 1) {
            $card_location = $matches['card_location'];
            $player_id = $matches['player_id'];
            $filtered = array_filter(TestDatas::$cards,function ($card) use ($card_location, $player_id, ){return $card['card_location'] == $card_location && $card['player_id'] == $player_id ;});
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `cards` WHERE `type` = (?P<type>.*) AND `subtype` = (?P<subtype>.*) AND `card_location` = '(?P<card_location>.*)'$/", $sql, $matches) == 1) {
            $card_location = $matches['card_location'];
            $type = $matches['type'];
            $subtype = $matches['subtype'];
            $filtered = array_filter(TestDatas::$cards,function ($card) use ($card_location, $type, $subtype){return $card['card_location'] == $card_location && $card['type'] == $type && $card['subtype'] == $subtype;});
            return $filtered;
        }
        if (preg_match("/^SELECT .* FROM `cards` WHERE `subType` = (?P<subtype>.*) AND \(`type` IN \((?P<types>.*)\)\)$/", $sql, $matches) == 1) {
            $filtered = [];
            $subtype = $matches['subtype'];
            $types = explode(',',str_replace("'","",$matches['types']));
            $filtered = array_filter(TestDatas::$cards,function ($card) use ( $types, $subtype){return in_array($card['type'], $types) && $card['subtype'] == $subtype;});
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `meeples`$/", $sql, $matches) == 1) {
            return TestDatas::$tokens;
        }
        if (preg_match("/^SELECT (.*) FROM `meeples` WHERE `player_id` = (?P<player_id>.*) AND \(`meeple_location` = '(?P<meeple_location>.*)'\)$/", $sql, $matches) == 1) {
            $meeple_location = $matches['meeple_location'];
            $player_id = $matches['player_id'];
            $filtered = array_filter(TestDatas::$tokens,function ($token) use ($meeple_location, $player_id){return $token['meeple_location'] == $meeple_location && $token['player_id'] == $player_id;});
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `meeples` WHERE `player_id` = (?P<player_id>.*) AND \(`meeple_location` IN \('(?P<meeple_locations>.*)'\)\)$/", $sql, $matches) == 1) {
            $meeple_locations = explode(',',str_replace("'","",$matches['meeple_locations']));
            $player_id = $matches['player_id'];
            $filtered = array_filter(TestDatas::$tokens,function ($token) use ($meeple_locations, $player_id){return in_array($token['meeple_location'], $meeple_locations) && $token['player_id'] == $player_id;});
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `meeples` WHERE (\(?`meeple_location` = '(?P<meeple_location>.*)'\)?)?( AND `meeple_state` = (?P<meeple_state>\d+))?$/", $sql, $matches) == 1) {
            $meeple_location = array_key_exists('meeple_location',$matches) ? $matches['meeple_location'] : null;
            $meeple_state = array_key_exists('meeple_state',$matches) ? $matches['meeple_state'] : null;
            //logForTests("BEFORE FILTER: ".json_encode(TestDatas::$tokens));
            $filtered = array_filter(TestDatas::$tokens,function ($token) use ($meeple_location,$meeple_state){return (!isset($meeple_location) || $token['meeple_location'] == $meeple_location) && (!isset($meeple_state) || intval($meeple_state) == $token['meeple_state']) ;});
            logForTests("MOCK select tokens: ".json_encode($filtered));
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `meeples` WHERE \(`meeple_id` IN \((?P<meeple_ids>.*)\)\)$/", $sql, $matches) == 1) {
            $filtered = [];
            $meeple_ids = explode(',',str_replace("'","",$matches['meeple_ids']));
            foreach($meeple_ids as $id){
                $meeple_id = intval($id);
                if(!array_key_exists($meeple_id,TestDatas::$tokens)) continue;
                $filtered[] = TestDatas::$tokens[$meeple_id];
            }
            logForTests("MOCK select meeples with ids ".json_encode($meeple_ids).": ".json_encode($filtered));
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `tiles` WHERE \(?`tile_location` = '(?P<tile_location>.*)'\)?$/", $sql, $matches) == 1) {
            $tile_location = $matches['tile_location'];
            $filtered = array_filter(TestDatas::$tiles,function ($tile) use ($tile_location,){return $tile['tile_location'] == $tile_location ;});
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `tiles` WHERE \(`tile_location` = '(?P<tile_location>.*)'\) ORDER BY tile_state (?P<order>\w+)$/", $sql, $matches) == 1) {
            $order = $matches['order'];
            $tile_location = $matches['tile_location'];
            $filtered = array_filter(TestDatas::$tiles,function ($tile) use ($tile_location,){return $tile['tile_location'] == $tile_location ;});
            uasort($filtered, function ($a,$b) use ($order)  {
                if($order == 'DESC') return $b["tile_state"] <=> $a["tile_state"];
                return $a["tile_state"] <=> $b["tile_state"];
            });
            logForTests("MOCK select tiles (sorted tile_state): ".json_encode($filtered));
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `tiles` WHERE \(`tile_location` = '(?P<tile_location>.*)'\) AND `tile_state` = (?P<tile_state>\w+)$/", $sql, $matches) == 1) {
            $tile_location = $matches['tile_location'];
            $tile_state = intval($matches['tile_state']);
            $filtered = array_filter(TestDatas::$tiles,function ($tile) use ($tile_location, $tile_state){return $tile['tile_location'] == $tile_location && $tile['tile_state'] == $tile_state;});
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `tiles` WHERE \(`tile_location` = '(?P<tile_location>.*)'\) ORDER BY tile_state (?P<order>\w+) LIMIT (?P<limit>\d+)$/", $sql, $matches) == 1) {
            $order = $matches['order'];
            $tile_location = $matches['tile_location'];
            $limit = intval($matches['limit']);
            $filtered = array_filter(TestDatas::$tiles,function ($tile) use ($tile_location,){return $tile['tile_location'] == $tile_location ;});
            $filtered = array_slice($filtered, 0, $limit, true);
            uasort($filtered, function ($a,$b) use ($order)  {
                if($order == 'DESC') return $b["tile_state"] <=> $a["tile_state"];
                return $a["tile_state"] <=> $b["tile_state"];
            });
            logForTests("MOCK select tiles (sorted tile_state): ".json_encode($filtered));
            return $filtered;
        }
        
        if (preg_match("/^SELECT .* FROM `tiles` WHERE `tile_location` = '(?P<tile_location>.*)' AND \(`tile_id` IN \((?P<tile_ids>.*)\)\)$/", $sql, $matches) == 1) {
            $tile_ids = explode(',', str_replace("'","",$matches['tile_ids']) );
            $tile_location = ($matches['tile_location']);
            $filtered = array_filter(TestDatas::$tiles,function ($t) use($tile_location, $tile_ids) {return $t['tile_location'] == $tile_location && in_array($t['tile_id'], $tile_ids);});
            return $filtered;
        }
        if (preg_match("/^SELECT .* FROM `tiles` WHERE `tile_location` = '(?P<tile_location>.*)' AND \(`tile_state` IN \((?P<tile_states>.*)\)\)$/", $sql, $matches) == 1) {
            $tile_states = explode(',', str_replace("'","",$matches['tile_states']) );
            $tile_location = ($matches['tile_location']);
            $filtered = array_filter(TestDatas::$tiles,function ($t) use($tile_location, $tile_states) {return $t['tile_location'] == $tile_location && in_array($t['tile_state'], $tile_states);});
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `tiles` WHERE `subType` = (?P<subType>\d+) AND \(`type` IN \((?P<types>.*)\)\)$/", $sql, $matches) == 1) {
            $subType = intval($matches['subType']);
            $types = explode(',',str_replace("'","",$matches['types']));
            $filtered = array_filter(TestDatas::$tiles,function ($tile) use ($types, $subType){return in_array($tile['type'],$types) && $tile['subtype'] == $subType ;});
            logForTests("MOCK select tiles with subType $subType and types ".json_encode($types).": ".json_encode($filtered));
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `tiles` WHERE `player_id` = (?P<player_id>.*) AND `tile_location` = '(?P<tile_location>.*)'$/", $sql, $matches) == 1) {
            $tile_location = $matches['tile_location'];
            $player_id = intval($matches['player_id']);
            $filtered = array_filter(TestDatas::$tiles,function ($tile) use ($tile_location, $player_id){return $tile['tile_location'] == $tile_location && $tile['player_id'] == $player_id;});
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `tiles` WHERE \(`tile_id` IN \((?P<tile_ids>.*)\)\)$/", $sql, $matches) == 1) {
            $filtered = [];
            $tile_ids = explode(',',str_replace("'","",$matches['tile_ids']));
            foreach($tile_ids as $id){
                $tile_id = intval($id);
                if(!array_key_exists($tile_id,TestDatas::$tiles)) continue;
                $filtered[] = TestDatas::$tiles[$tile_id];
            }
            logForTests("MOCK select tiles with ids ".json_encode($tile_ids).": ".json_encode($filtered));
            return $filtered;
        }
        if (preg_match("/^SELECT (.*) FROM `log`(.*)$/", $sql, $matches) == 1) {
            return TestDatas::$logs;
        }
        if( str_starts_with( $sql, 'SELECT *, player_id AS `result_associative_index` FROM `player`' )){
            logForTests("MOCK select players");
            return  TestDatas::$players;
        }
        if( str_starts_with( $sql, "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_location` = 'bd1') ORDER BY tile_state DESC LIMIT" )){
            logForTests("MOCK select deck 1 tiles");
            return [
                TestDatas::$tiles[21],
                TestDatas::$tiles[22],
                TestDatas::$tiles[23],
                TestDatas::$tiles[24],
                TestDatas::$tiles[25],
                TestDatas::$tiles[26],
            ];
        }
        
        if( str_starts_with( $sql, "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `type` , `subtype` FROM `tiles` WHERE (`tile_location` = 'bd2') ORDER BY tile_state DESC LIMIT" )){
            logForTests("MOCK select deck 2 tiles");
            return [
                TestDatas::$tiles[101],
                TestDatas::$tiles[102],
                TestDatas::$tiles[103],
                TestDatas::$tiles[104],
                TestDatas::$tiles[105],
                TestDatas::$tiles[106],
            ];
        }

        logForTests(" /?\ __ don't know what to return for this request __ /?\ [$sql] ");
        return [];
    }

    public function reloadPlayersBasicInfos(): void
    {
    }
    public function sendNotifications(): void
    {
        logForTests("mock sendNotifications()");
    }
    
    public function loadPlayersBasicInfos(): array
    {
        return TestDatas::$players; 
    }

    public function getCurrentPlayerId(bool $bReturnNullIfNotLogged = false): string|int
    {
        return TestDatas::$test_activePlayerId; 
    }
    public function getDoubleKeyCollectionFromDB(string $sql, bool $nullIfEmpty = false): array
    {
        logForTests("getDoubleKeyCollectionFromDB: $sql");
        logForTests(" /?\ __ don't know what to return for this request __ /?\ [$sql] ");
        return [];
    }
    public static function DbAffectedRow(): int
    {
        return 1;
    }
    public static function DbGetLastId(): int
    {
        return TestDatas::$lastInsertedId;
    }
    public static function DbQuery(string $sql): null|\mysqli_result|bool
    {
        logForTests('DbQuery: ['.$sql.']');
        switch($sql){
            case "INSERT INTO `meeples` (`meeple_location`, `meeple_state`, `type`, `player_id`) VALUES('tile-31','1','2','1')":
                TestDatas::$tokens[999] = ['result_associative_index' => 999, 'meeple_id' => 999, 'meeple_state' => 1, 'meeple_location'=> 'tile-31','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1, ];
                TestDatas::$lastInsertedId = 999;
                return true;
            case "INSERT INTO `meeples` (`meeple_location`, `meeple_state`, `type`, `player_id`) VALUES('tile-34','1','2','1')":
                TestDatas::$tokens[999] = ['result_associative_index' => 999, 'meeple_id' => 999, 'meeple_state' => 1, 'meeple_location'=> 'tile-34','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1, ];
                TestDatas::$lastInsertedId = 999;
                return true;

            case "UPDATE `stats` SET `stats_value` = `stats_value` + 1 WHERE `stats_type` = 20 AND `stats_player_id` = 1":
                TestDatas::$stats[1]['nbActionsBuild']++;
                return true;
            case "UPDATE `stats` SET `stats_value` = `stats_value` + 1 WHERE `stats_type` = 22 AND `stats_player_id` = 1":
                TestDatas::$stats[1]['nbActionsDeliver']++;
                return true;
            case "UPDATE `stats` SET `stats_value` = `stats_value` + 1 WHERE `stats_type` = 21 AND `stats_player_id` = 1":
                TestDatas::$stats[1]['nbActionsSail']++;
                return true;

        }
        
        if (preg_match("/^UPDATE `stats` SET `stats_value` = `stats_value` \+ (?P<stats_value>.*) WHERE `stats_type` = (?P<stats_type>\d+) AND `stats_player_id` IS NULL$/", $sql, $matches) == 1) {
            $stats_value = $matches['stats_value'];
            $stats_type = $matches['stats_type'];
            logForTests("DbQuery --- INC table stat  $stats_type += $stats_value... ");
            TestDatas::$stats['table'][$stats_type] += $stats_value;
            return true;
        }
        if (preg_match("/^UPDATE `stats` SET `stats_value` = `stats_value` \+ (?P<stats_value>.*) WHERE `stats_type` = (?P<stats_type>\d+) AND `stats_player_id` = (?P<stats_player_id>\d+)$/", $sql, $matches) == 1) {
            $stats_value = $matches['stats_value'];
            $stats_type = $matches['stats_type'];
            $stats_player_id = $matches['stats_player_id'];
            logForTests("DbQuery --- nothing done for INC stat $stats_value, $stats_type, $stats_player_id... ");
            return true;
        }
        //2 inserts at the same time
        if (preg_match("/^INSERT INTO `player` (.*) VALUES(\('(?P<player_id>\d+)','(?P<player_color>\w+)','(?P<player_name>\w+)','(?P<player_clan>\d+)','(?P<resources>[\w\":,{}]+)'\)),(\('(?P<player_id2>\d+)','(?P<player_color2>\w+)','(?P<player_name2>\w+)','(?P<player_clan2>\d+)','(?P<resources2>[\w\":,{}]+)'\))$/", $sql, $matches) == 1) {
            $player_clan = intval( $matches['player_clan']);
            $player_color = $matches['player_color'];
            $player_name = ($matches['player_name']);
            $resources = ($matches['resources']);
            $player_id = intval($matches['player_id']);
            $player_ids = array_keys(TestDatas::$players);
            $player_id = 1 + $player_ids[count($player_ids)-1];
            logForTests("DbQuery --- added player $player_id : $player_color, $player_name, $player_clan, '$resources' ");
            TestDatas::$players[$player_id] = ['result_associative_index' => $player_id, 'player_id' => $player_id, 'player_color' => $player_color, 'player_name'=> $player_name,'player_clan' => $player_clan,  'resources' => $resources, ];
            TestDatas::$lastInsertedId = $player_id;
            
            $player_clan = intval( $matches['player_clan2']);
            $player_color = $matches['player_color2'];
            $player_name = ($matches['player_name2']);
            $resources = ($matches['resources2']);
            $player_id = intval($matches['player_id2']);
            $player_ids = array_keys(TestDatas::$players);
            $player_id = 1 + $player_ids[count($player_ids)-1];
            logForTests("DbQuery --- added player $player_id : $player_color, $player_name, $player_clan, '$resources' ");
            TestDatas::$players[$player_id] = ['result_associative_index' => $player_id, 'player_id' => $player_id, 'player_color' => $player_color, 'player_name'=> $player_name,'player_clan' => $player_clan,  'resources' => $resources, ];
            TestDatas::$lastInsertedId = $player_id;
            return true;
        }
        $mutiplesGroups = "";
        for($k=1;$k<100;$k++) $mutiplesGroups .= "(,?\('(\w+)','(\w+)','(\w+)',(?:'(\w+)'|NULL),'([\w\":,{}]+)'\))?";
        if (preg_match("/^INSERT INTO `player` (.*) VALUES(,?\('(\w+)','(\w+)','(\w+)',(?:'(\w+)'|NULL),'([\w\":,{}]+)'\))?$mutiplesGroups$/", $sql, $matches) == 1) {
            $k =2;
            while(array_key_exists($k,$matches)){
                $player_id = intval($matches[$k+1]);
                $player_color = ($matches[$k+2]);
                $player_name = ($matches[$k+3]);
                $player_clan = intval($matches[$k+4]);
                $resources = ($matches[$k+5]);
                $player_ids = array_keys(TestDatas::$players);
                $player_id = 1 + $player_ids[count($player_ids)-1];
                logForTests("DbQuery --- added player $player_id : $player_color, $player_name, $player_clan, '$resources' ");
                TestDatas::$players[$player_id] = ['result_associative_index' => $player_id, 'player_id' => $player_id, 'player_color' => $player_color, 'player_name'=> $player_name,'player_clan' => $player_clan,  'resources' => $resources, ];
                TestDatas::$lastInsertedId = $player_id;
                $k+=6;
            }
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `resources` = '(?P<resources>.*)' WHERE  `player_id` = (?P<pid>[\d]+)$/", $sql, $matches) == 1) {
            $resources = $matches['resources'];
            $resources = str_replace('\\','',$resources);
            $pid = $matches['pid'];
            logForTests("DbQuery --- updated resources for player $pid ... '$resources'");
            TestDatas::$players[$pid]['resources'] = $resources;
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `bonuses` = '(?P<bonuses>.*)' WHERE  `player_id` = (?P<pid>[\d]+)$/", $sql, $matches) == 1) {
            $bonuses = $matches['bonuses'];
            $bonuses = str_replace('\\"','"',$bonuses);
            $pid = $matches['pid'];
            logForTests("DbQuery --- updated bonuses for player $pid ... '$bonuses'");
            TestDatas::$players[$pid]['bonuses'] = $bonuses;
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `skip_roll_die` = '(?P<skip_roll_die>.*)' WHERE  `player_id` = (?P<pid>[\d]+)$/", $sql, $matches) == 1) {
            $skip_roll_die = $matches['skip_roll_die'];
            $pid = $matches['pid'];
            logForTests("DbQuery --- updated skip_roll_die for player $pid ... '$skip_roll_die'");
            TestDatas::$players[$pid]['skip_roll_die'] = intval($skip_roll_die);
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `player_clan` = '(?P<player_clan>.*)' WHERE  `player_id` = (?P<pid>[\d]+)$/", $sql, $matches) == 1) {
            $player_clan = $matches['player_clan'];
            $pid = $matches['pid'];
            logForTests("DbQuery --- updated player_clan for player $pid ... '$player_clan'");
            TestDatas::$players[$pid]['player_clan'] = intval($player_clan);
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `player_color` = '(?P<player_color>.*)' WHERE  `player_id` = (?P<pid>[\d]+)$/", $sql, $matches) == 1) {
            $player_color = $matches['player_color'];
            $pid = $matches['pid'];
            logForTests("DbQuery --- updated player_color for player $pid ... '$player_color'");
            TestDatas::$players[$pid]['player_color'] = intval($player_color);
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `die_face` = '(?P<die_face>.*)' WHERE  `player_id` = (?P<pid>[\d]+)$/", $sql, $matches) == 1) {
            $die_face = $matches['die_face'];
            $pid = $matches['pid'];
            logForTests("DbQuery --- updated die_face for player $pid ... '$die_face'");
            TestDatas::$players[$pid]['die_face'] = intval($die_face);
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `player_score` = '+(?P<player_score>.*)'+ WHERE (\s*)`player_id` = (?P<pid>[\d]+)$/", $sql, $matches) == 1) {
            $player_score = intval($matches['player_score']);
            $pid = $matches['pid'];
            logForTests("DbQuery --- set player_score for player $pid ... $player_score");
            TestDatas::$players[$pid]['player_score'] = ($player_score);
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `player_score` = `player_score` \+ (?P<player_score>.*) WHERE (\s*)`player_id` = (?P<pid>[\d]+)$/", $sql, $matches) == 1) {
            $player_score = $matches['player_score'];
            $pid = $matches['pid'];
            logForTests("DbQuery --- INC player_score for player $pid ... '$player_score'");
            TestDatas::$players[$pid]['player_score'] += intval($player_score);
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `player_score_aux` = '(?P<player_score_aux>\d+)' WHERE (\s*)`player_id` = (?P<pid>[\d]+)$/", $sql, $matches) == 1) {
            $player_score = $matches['player_score_aux'];
            $pid = $matches['pid'];
            logForTests("DbQuery --- set player_score_aux for player $pid ... '$player_score'");
            TestDatas::$players[$pid]['player_score_aux'] = intval($player_score);
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `last_turn_played` = '(?P<last_turn_played>\w+)' WHERE  `player_id` = (?P<pid>[\d]+)$/", $sql, $matches) == 1) {
            $last_turn_played = $matches['last_turn_played'] == '1';
            $pid = $matches['pid'];
            logForTests("DbQuery --- update last_turn_played for player $pid ... $last_turn_played");
            TestDatas::$players[$pid]['last_turn_played'] = $last_turn_played;
            return true;
        }
        if (preg_match("/^UPDATE `meeples` SET `meeple_state` = '(?P<meeple_state>\d+)' WHERE  `meeple_id` = (?P<meeple_id>\d+)$/", $sql, $matches) == 1) {
            $meeple_state = $matches['meeple_state'];
            $meeple_id = $matches['meeple_id'];
            logForTests("DbQuery --- updated state for token $meeple_id : $meeple_state");
            TestDatas::$tokens[$meeple_id]['meeple_state'] = intval($meeple_state);
            return true;
        }
        if (preg_match("/^UPDATE `meeples` SET `type` = '(?P<type>\d+)' WHERE  `meeple_id` = (?P<meeple_id>\d+)$/", $sql, $matches) == 1) {
            $type = $matches['type'];
            $meeple_id = $matches['meeple_id'];
            logForTests("DbQuery --- updated type for token $meeple_id : $type");
            TestDatas::$tokens[$meeple_id]['type'] = intval($type);
            return true;
        }
        if (preg_match("/^DELETE FROM `meeples` WHERE  `meeple_id` = (?P<meeple_id>\d+)$/", $sql, $matches) == 1) {
            $meeple_id = $matches['meeple_id'];
            logForTests("DbQuery --- remove token $meeple_id");
            unset(TestDatas::$tokens[$meeple_id]);
            return true;
        }
        if (preg_match("/^INSERT INTO `meeples` (.*) VALUES\('(?P<meeple_location>.*)','(?P<meeple_state>\d+)','(?P<type>\d+)','(?P<player_id>[\d|\-]+)'\)$/", $sql, $matches) == 1) {
            $type = intval( $matches['type']);
            $meeple_location = $matches['meeple_location'];
            $meeple_state = intval($matches['meeple_state']);
            $player_id = intval($matches['player_id']);
            $meeple_ids = array_keys(TestDatas::$tokens);
            $meeple_id = 1 + $meeple_ids[count($meeple_ids)-1];
            logForTests("DbQuery --- added token $meeple_id : $meeple_location, $meeple_state,$type, $player_id ");
            TestDatas::$tokens[$meeple_id] = ['result_associative_index' => $meeple_id, 'meeple_id' => $meeple_id, 'meeple_state' => $meeple_state, 'meeple_location'=> $meeple_location,'type' => $type,  'player_id' => $player_id, ];
            TestDatas::$lastInsertedId = $meeple_id;
            return true;
        }
        $mutiplesGroups = "";
        for($k=1;$k<100;$k++) $mutiplesGroups .= "(,?\('(\w+)','(\w+)',(?:'(-?\w+)'|NULL),'(\w+)','(\w+)'\))?";
        if (preg_match("/^INSERT INTO `cards` (.*) VALUES(,?\('(\w+)','(\w+)',(?:'(-?\w+)'|NULL),'(\w+)','(\w+)'\))?$mutiplesGroups$/", $sql, $matches) == 1) {
            $k =2;
            while(array_key_exists($k,$matches)){
                $card_location = $matches[$k+1];
                $card_state = intval($matches[$k+2]);
                $player_id = null;
                $type = intval($matches[$k+4]);
                $subtype = intval($matches[$k+5]);
                $cards_ids = array_keys(TestDatas::$cards);
                $nbCards = count($cards_ids);
                $id = 1 + ($nbCards>0 ? $cards_ids[count($cards_ids)-1] : 0);
                logForTests("DbQuery --- added card $id : $card_location, $card_state, $player_id,$type, $subtype ");
                TestDatas::$cards[$id] = ['result_associative_index' => $id,'card_id' => $id, 'card_location' => $card_location, 'card_state' => $card_state, 'player_id' => $player_id, 'type' => $type, 'subtype' => $subtype,];
                TestDatas::$lastInsertedId = $id;
                $k+=6;
            }
            return true;
        }
        if (preg_match("/^UPDATE `cards` SET `card_location` = '(?P<card_location>.*)' WHERE  `card_id` = (?P<card_id>\d+)$/", $sql, $matches) == 1) {
            $card_location = $matches['card_location'];
            $card_id = $matches['card_id'];
            logForTests("DbQuery --- updated card_location for card $card_id : $card_location");
            TestDatas::$cards[$card_id]['card_location'] = $card_location;
            return true;
        }
        if (preg_match("/^UPDATE `cards` SET `card_state` = '(?P<card_state>.*)' WHERE \(`card_id` IN \((?P<card_ids>.*)\)\)$/", $sql, $matches) == 1) {
            $card_state = $matches['card_state'];
            $card_ids = explode(',', str_replace("'","",$matches['card_ids']));
            foreach($card_ids as $cardIdString){
                $card_id = intval($cardIdString);
                if(!array_key_exists($card_id,TestDatas::$cards)) continue;
                logForTests("DbQuery --- updated card_state for card $card_id : $card_state");
                TestDatas::$cards[$card_id]['card_state'] = intval($card_state);
            }
            return true;
        }
        if (preg_match("/^UPDATE `cards` SET `card_location` = '(?P<card_location>.*)',`card_state` = '(?P<card_state>.*)' WHERE \(`card_id` IN \((?P<card_ids>.*)\)\)$/", $sql, $matches) == 1) {
            $card_location = $matches['card_location'];
            $card_state = $matches['card_state'];
            $card_ids = explode(',', str_replace("'","",$matches['card_ids']));
            foreach($card_ids as $cardIdString){
                $card_id = intval($cardIdString);
                if(!array_key_exists($card_id,TestDatas::$cards)) continue;
                logForTests("DbQuery --- updated card_location, card_state for card $card_id : $card_location, $card_state");
                TestDatas::$cards[$card_id]['card_location'] = $card_location;
                TestDatas::$cards[$card_id]['card_state'] = intval($card_state);
            }
            return true;
        }
        if (preg_match("/^UPDATE `cards` SET `card_location` = '(?P<card_location>.*)',`card_state` = '(?P<card_state>.*)' WHERE \(`card_location` = '(?P<from_location>.*)'\)$/", $sql, $matches) == 1) {
            $card_location = $matches['card_location'];
            $from_location = $matches['from_location'];
            $card_state = $matches['card_state'];
            //logForTests("DbQuery ---   cards to filter ... ".json_encode(TestDatas::$cards));
            foreach(TestDatas::$cards as $card_id => &$card){
                if($card['card_location'] != $from_location) continue;
                logForTests("DbQuery --- updated card_location, card_state for card $card_id : $card_location, $card_state from ".$card['card_location']);
                TestDatas::$cards[$card_id]['card_location'] = $card_location;
                TestDatas::$cards[$card_id]['card_state'] = intval($card_state);
            }
            return true;
        }
        if (preg_match("/^UPDATE `cards` SET `card_state` = `card_state` \+ (?P<inc>.*) WHERE `card_location` = '(?P<from_location>.*)' AND \(`card_state` >= (?P<state_min>.*)\)$/", $sql, $matches) == 1) {
            $inc = intval($matches['inc']);
            $state_min = intval($matches['state_min']);
            $from_location = $matches['from_location'];
            foreach(TestDatas::$cards as $card_id => &$card){
                if($card['card_location'] != $from_location) continue;
                if($card['card_state'] < $state_min) continue;
                $card['card_state'] += $inc;
                logForTests("DbQuery --- updated card_state for card $card_id : ".$card['card_state']);
            }
            return true;
        }
        if (preg_match("/^UPDATE `cards` SET `player_id` = '(?P<player_id>.*)' WHERE  `card_id` = (?P<card_id>\d+)$/", $sql, $matches) == 1) {
            $player_id = $matches['player_id'];
            $card_id = $matches['card_id'];
            logForTests("DbQuery --- updated player_id for card $card_id : $player_id");
            TestDatas::$cards[$card_id]['player_id'] = intval($player_id);
            return true;
        }
        
        $mutiplesGroups = "";
        for($k=1;$k<100;$k++) $mutiplesGroups .= "(,?\('(\w+)','(\w+)',(?:'(-?\w+)'|NULL),'(\w+)','(\w+)'\))?";
        if (preg_match("/^INSERT INTO `tiles` (.*) VALUES(,?\('(\w+)','(\w+)',(?:'(-?\w+)'|NULL),'(\w+)','(\w+)'\))?$mutiplesGroups$/", $sql, $matches) == 1) {
            $k =2;
            while(array_key_exists($k,$matches)){
                $card_location = $matches[$k+1];
                $card_state = intval($matches[$k+2]);
                $player_id = intval($matches[$k+3]);
                $type = intval($matches[$k+4]);
                $subtype = intval($matches[$k+5]);
                $cards_ids = array_keys(TestDatas::$tiles);
                $nbCards = count($cards_ids);
                $id = 1 + ($nbCards>0 ? $cards_ids[count($cards_ids)-1] : 0);
                logForTests("DbQuery --- added tile $id : $card_location, $card_state, $player_id,$type, $subtype ");
                TestDatas::$tiles[$id] = ['result_associative_index' => $id,'tile_id' => $id, 'tile_location' => $card_location, 'tile_state' => $card_state, 'player_id' => $player_id, 'type' => $type, 'subtype' => $subtype,];
                TestDatas::$lastInsertedId = $id;
                $k+=6;
            }
            return true;
        }
        if (preg_match("/^DELETE FROM `tiles` WHERE  `tile_id` = (?P<tile_id>\d+)$/", $sql, $matches) == 1) {
            $tile_id = $matches['tile_id'];
            logForTests("DbQuery --- remove tile $tile_id");
            unset(TestDatas::$tiles[$tile_id]);
            return true;
        }
        if (preg_match("/^UPDATE `tiles` SET `player_id` = '(?P<player_id>.*)' WHERE  `tile_id` = (?P<card_id>\d+)$/", $sql, $matches) == 1) {
            $player_id = $matches['player_id'];
            $card_id = $matches['card_id'];
            logForTests("DbQuery --- updated player_id for tile $card_id : $player_id");
            TestDatas::$tiles[$card_id]['player_id'] = intval($player_id);
            return true;
        }
        if (preg_match("/^UPDATE `tiles` SET `tile_location` = '(?P<tile_location>.*)' WHERE  `tile_id` = (?P<tile_id>\d+)$/", $sql, $matches) == 1) {
            $tile_location = $matches['tile_location'];
            $tile_id = $matches['tile_id'];
            logForTests("DbQuery --- updated tile_location for tile $tile_id : $tile_location");
            TestDatas::$tiles[$tile_id]['tile_location'] = $tile_location;
            return true;
        }
        if (preg_match("/^UPDATE `tiles` SET `tile_state` = '(?P<tile_state>\d+)' WHERE  `tile_id` = (?P<tile_id>\d+)$/", $sql, $matches) == 1) {
            $tile_state = $matches['tile_state'];
            $tile_id = $matches['tile_id'];
            logForTests("DbQuery --- updated state for tile $tile_id : $tile_state");
            TestDatas::$tiles[$tile_id]['tile_state'] = intval($tile_state);
            return true;
        }
        if (preg_match("/^UPDATE `tiles` SET `tile_location` = '(?P<card_location>.*)',`tile_state` = '(?P<card_state>.*)' WHERE \(`tile_id` IN \((?P<card_ids>.*)\)\)$/", $sql, $matches) == 1) {
            $card_location = $matches['card_location'];
            $card_state = $matches['card_state'];
            $card_ids = explode(',', str_replace("'","",$matches['card_ids']));
            foreach($card_ids as $cardIdString){
                $card_id = intval($cardIdString);
                if(!array_key_exists($card_id,TestDatas::$tiles)) continue;
                logForTests("DbQuery --- updated tile_location, tile_state for tile $card_id : $card_location, $card_state");
                TestDatas::$tiles[$card_id]['tile_location'] = $card_location;
                TestDatas::$tiles[$card_id]['tile_state'] = intval($card_state);
            }
            return true;
        }
        if (preg_match("/^DELETE FROM `log` WHERE \(`id` > (?P<id>\d+)\)$/", $sql, $matches) == 1) {
            $log_id = intval($matches['id']);
            foreach(TestDatas::$logs as $id => $log){
                logForTests("DbQuery --- removed log $id (which is > $log_id)");
                if($id > $log_id ) unset(TestDatas::$logs[$id]);
            }
            return true;
        }
        if (preg_match("/^UPDATE `tiles` SET `tile_state` = '(?P<tile_state>\d+)' WHERE \(`tile_id` IN \('(?P<tile_id>\d+)'\)\)$/", $sql, $matches) == 1) {
            $tile_state = $matches['tile_state'];
            $tile_id = $matches['tile_id'];
            logForTests("DbQuery --- updated state for tile $tile_id : $tile_state");
            TestDatas::$tiles[$tile_id]['tile_state'] = intval($tile_state);
            return true;
        }
        if (preg_match("/^UPDATE `tiles` SET `tile_location` = '(?P<tile_location>\w+)',`tile_state` = '(?P<tile_state>\d+)' WHERE \(`tile_id` IN \('(?P<tile_id>\d+)'\)\)$/", $sql, $matches) == 1) {
            $tile_state = $matches['tile_state'];
            $tile_location = $matches['tile_location'];
            $tile_id = $matches['tile_id'];
            logForTests("DbQuery --- updated location,state for tile $tile_id : $tile_location, $tile_state");
            TestDatas::$tiles[$tile_id]['tile_state'] = intval($tile_state);
            TestDatas::$tiles[$tile_id]['tile_location'] = $tile_location;
            return true;
        }
        if( str_starts_with( $sql, 'UPDATE `global_variables`' ) || str_starts_with( $sql, 'REPLACE INTO `global_variables`' )){
            //Nothing needed for globals because they also are in saved memory
            return null;
        }
        if( str_starts_with( $sql, 'INSERT INTO `log`' )){
            //Nothing needed until we want to test the log module
            return null;
        }
        logForTests("DbQuery --- nothing done");
        return null;
    }
    public function getNextPlayerTable(): array
    {
        return [ 1 => 2, 2 => 1, 0 => 1,];
    }
    public function getActivePlayerId(): string|int
    {
        return TestDatas::$test_activePlayerId; 
    }

    /**
     */
    public function notifyAllPlayers(string $notificationType, string $notificationLog, array $notificationArgs): void
    {
        logForTests("notifyAllPlayers $notificationType : $notificationLog, with args ".json_encode($notificationArgs)."", "NOTIF");
        $suffix = '';
        if(isset($notificationArgs['player_id'])) $suffix = "-".($notificationArgs['player_id']);
        TestDatas::$notifs['all'][] = "$notificationType$suffix";
    }

    /**
     */
    public function notifyPlayer(int $playerId, string $notificationType, string $notificationLog, array $notificationArgs): void
    {
        logForTests("notifyPlayer ($playerId) $notificationType : $notificationLog, with args ".json_encode($notificationArgs)."", "NOTIF");
        TestDatas::$notifs[$playerId][] = $notificationType;
    }

    final public function checkAction(string $actionName, bool $bThrowException = true): bool
    {
        return true;
    }
}
 class TableOptions {
    public function get(int $optionId): ?int {
        switch($optionId){
            case BGA_GAMESTATE_GAMEVERSION: return 999999;
        }
        return 0;
    }

    function isTurnBased(): bool {
        return false;
    }
    function isRealTime(): bool {
        return false;
    }
}
class Bga {
        public Db\Globals $globals;
        public Notify $notify;
        public Legacy $legacy;
        public Tournament $tournament;
        public TableOptions $tableOptions;
        public UserPreferences $userPreferences;
        public TableStats $tableStats;
        public PlayerStats $playerStats;
        public Components\DeckFactory $deckFactory;
        public Components\Counters\CounterFactory $counterFactory;
        public Debug $debug;
        
        public Components\Counters\PlayerCounter $playerScore;
        public Components\Counters\PlayerCounter $playerScoreAux;
            
        public function __construct(
            $game,
        ) {
            $this->tableOptions = new TableOptions();
        }
    }
class Notify {
    public function player(int $playerId, string $notifName, string $message = '', array $args = []): void {
        logForTests("Notify_player $playerId $notifName : $message, with args ".json_encode($args)."", "NOTIF");
    }
    public function all(string $notifName, string $message = '', array $args = []): void {
        logForTests("Notify_ALL $notifName : $message, with args ".json_encode($args)."", "NOTIF");
    }
    public function __construct(
        $game,
    ) {
    }
}
class NotificationMessage {
    public function __construct(
        public string $message = '',
        public array $args = [],
    ) {}
}

namespace Bga\GameFramework\States;

use Bga\GameFramework\StateType;

#[\Attribute]
class PossibleAction
{
}

abstract class GameState
{
    public function __construct(
        $game,
        public int $id,
        public StateType $type,
        public ?string $name = null,
        public string $description = '',
        public string $descriptionMyTurn = '',
        public array $transitions = [],
        public bool $updateGameProgression = false,
        public ?int $initialPrivate = null,
    ) {
    }
}
class GameStateInMem extends GameState {}
