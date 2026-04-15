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
    }
    public function nextState(string $transition = ''): void
    {
        switch(GamestateMachine::$test_current_state){
            default:
                GamestateMachine::$test_current_state = getMyMachineStates()[GamestateMachine::$test_current_state]['transitions'][$transition];
                break;
        }
        
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
            'player_colors' => ["ff0000", "008000", "0000ff", "ffffff"],
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
            case "SELECT COUNT(*) FROM `cards` WHERE (`card_location` = 'clans_draft')":
                return count(array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'clans_draft';}));
            case "SELECT COUNT(*) FROM `cards` WHERE (`card_location` = 'clans_assigned')":
                return count(array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'clans_assigned';}));
        }
        if (preg_match("/^SELECT COUNT\(\*\) FROM `cards` WHERE `player_id` = (?P<player_id>.*) AND \`card_location` = '(?P<card_location>.*)'\ AND \(`type` IN \((?P<types>.*)\)\)$/", $sql, $matches) == 1) {
            $types = explode(',', str_replace("'","",$matches['types']) );
            $count = count(array_filter(TestDatas::$cards,function ($card) use($matches, $types ) {return $card['card_location'] == $matches['card_location'] && $card['player_id'] == $matches['player_id'] && in_array($card['type'], $types );}));
            logForTests("getUniqueValueFromDb: count is $count for ".json_encode($types));
            return $count;
        }
        logForTests("getUniqueValueFromDb: /?\ ");
        return null;
    }

    public function getCollectionFromDb(string $sql, bool $singleColumn = false): array
    {
        logForTests("getCollectionFromDb: $sql");
        logForTests(" /!\ __ don't know what to return for this request __ /!\ [$sql] ");
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
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_location` = 'deck') ORDER BY card_state DESC LIMIT 2":
                $filtered = array_filter(TestDatas::$cards,function ($card) {return $card['card_location'] == 'deck';});
                logForTests("MOCK deck ".json_encode($filtered));
                return count($filtered) > 1 ? [ $filtered[array_keys($filtered)[1]], $filtered[array_keys($filtered)[2]] ] : [];
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
        if( str_starts_with( $sql, 'SELECT *, player_id AS `result_associative_index` FROM `player`' )){
            logForTests("MOCK select players");
            return [
                TestDatas::$players[1],
                TestDatas::$players[2],
            ];
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

        logForTests(" /!\ __ don't know what to return for this request __ /!\ [$sql] ");
        return [];
    }

    public function reloadPlayersBasicInfos(): void
    {
    }

    public function getCurrentPlayerId(bool $bReturnNullIfNotLogged = false): string|int
    {
        return TestDatas::$test_activePlayerId; 
    }
    public function getDoubleKeyCollectionFromDB(string $sql, bool $nullIfEmpty = false): array
    {
        logForTests("getDoubleKeyCollectionFromDB: $sql");
        return [];
    }
    public static function DbAffectedRow(): int
    {
        return 1;
    }
    public static function DbGetLastId(): int
    {
        return 1;
    }
    public static function DbQuery(string $sql): null|\mysqli_result|bool
    {
        logForTests('DbQuery: ['.$sql.']');
        switch($sql){
            case "INSERT INTO `meeples` (`meeple_location`, `meeple_state`, `type`, `player_id`) VALUES('tile-31','1','2','1')":
                TestDatas::$tokens[999] = ['result_associative_index' => 999, 'meeple_id' => 999, 'meeple_state' => 1, 'meeple_location'=> 'tile-31','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1, ];
                return true;
            case "INSERT INTO `meeples` (`meeple_location`, `meeple_state`, `type`, `player_id`) VALUES('tile-34','1','2','1')":
                TestDatas::$tokens[999] = ['result_associative_index' => 999, 'meeple_id' => 999, 'meeple_state' => 1, 'meeple_location'=> 'tile-34','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1, ];
                return true;

            case "UPDATE `stats` SET `stats_value` = `stats_value` + 1 WHERE `stats_type` = 20 AND `stats_player_id` = 1":
                TestDatas::$stats[1]['nbActionsBuild']++;
                return true;
            case "UPDATE `stats` SET `stats_value` = `stats_value` + 1 WHERE `stats_type` = 22 AND `stats_player_id` = 1":
                TestDatas::$stats[1]['nbActionsDeliver']++;
                return true;

        }
        if (preg_match("/^UPDATE `player` SET `resources` = '(?P<resources>.*)' WHERE  `player_id` = (?P<pid>\d+)$/", $sql, $matches) == 1) {
            $resources = $matches['resources'];
            $resources = str_replace('\\','',$resources);
            $pid = $matches['pid'];
            logForTests("DbQuery --- updated resources for player $pid ... '$resources'");
            TestDatas::$players[$pid]['resources'] = $resources;
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `bonuses` = '(?P<bonuses>.*)' WHERE  `player_id` = (?P<pid>\d+)$/", $sql, $matches) == 1) {
            $bonuses = $matches['bonuses'];
            $pid = $matches['pid'];
            logForTests("DbQuery --- updated bonuses for player $pid ... '$bonuses'");
            TestDatas::$players[$pid]['bonuses'] = $bonuses;
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `skip_roll_die` = '(?P<skip_roll_die>.*)' WHERE  `player_id` = (?P<pid>\d+)$/", $sql, $matches) == 1) {
            $skip_roll_die = $matches['skip_roll_die'];
            $pid = $matches['pid'];
            logForTests("DbQuery --- updated skip_roll_die for player $pid ... '$skip_roll_die'");
            TestDatas::$players[$pid]['skip_roll_die'] = intval($skip_roll_die);
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `die_face` = '(?P<die_face>.*)' WHERE  `player_id` = (?P<pid>\d+)$/", $sql, $matches) == 1) {
            $die_face = $matches['die_face'];
            $pid = $matches['pid'];
            logForTests("DbQuery --- updated die_face for player $pid ... '$die_face'");
            TestDatas::$players[$pid]['die_face'] = intval($die_face);
            return true;
        }
        if (preg_match("/^UPDATE `player` SET `player_score` = `player_score` \+ (?P<player_score>\d+) WHERE `player_id` = (?P<pid>\d+)$/", $sql, $matches) == 1) {
            $player_score = $matches['player_score'];
            $pid = $matches['pid'];
            logForTests("DbQuery --- INC player_score for player $pid ... '$player_score'");
            TestDatas::$players[$pid]['player_score'] += intval($player_score);
            return true;
        }
        if (preg_match("/^UPDATE `meeples` SET `meeple_state` = '(?P<meeple_state>\d+)' WHERE  `meeple_id` = (?P<meeple_id>\d+)$/", $sql, $matches) == 1) {
            $meeple_state = $matches['meeple_state'];
            $meeple_id = $matches['meeple_id'];
            logForTests("DbQuery --- updated state for token $meeple_id : $meeple_state");
            TestDatas::$tokens[$meeple_id]['meeple_state'] = intval($meeple_state);
            return true;
        }
        if (preg_match("/^UPDATE `cards` SET `card_location` = '(?P<card_location>.*)' WHERE  `card_id` = (?P<card_id>\d+)$/", $sql, $matches) == 1) {
            $card_location = $matches['card_location'];
            $card_id = $matches['card_id'];
            logForTests("DbQuery --- updated card_location for card $card_id : $card_location");
            TestDatas::$cards[$card_id]['card_location'] = $card_location;
            return true;
        }
        if (preg_match("/^UPDATE `cards` SET `player_id` = '(?P<player_id>.*)' WHERE  `card_id` = (?P<card_id>\d+)$/", $sql, $matches) == 1) {
            $player_id = $matches['player_id'];
            $card_id = $matches['card_id'];
            logForTests("DbQuery --- updated player_id for card $card_id : $player_id");
            TestDatas::$cards[$card_id]['player_id'] = intval($player_id);
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
        if (preg_match("/^UPDATE `tiles` SET `tile_state` = '(?P<tile_state>\d+)' WHERE \(`tile_id` IN \('(?P<tile_id>\d+)'\)\)$/", $sql, $matches) == 1) {
            $tile_state = $matches['tile_state'];
            $tile_id = $matches['tile_id'];
            logForTests("DbQuery --- updated state for tile $tile_id : $tile_state");
            TestDatas::$tiles[$tile_id]['tile_state'] = intval($tile_state);
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
    }

    /**
     */
    public function notifyPlayer(int $playerId, string $notificationType, string $notificationLog, array $notificationArgs): void
    {
        logForTests("notifyPlayer ($playerId) $notificationType : $notificationLog, with args ".json_encode($notificationArgs)."", "NOTIF");
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
