<?php

declare(strict_types=1);

namespace Bga\GameFramework;

use Bga\GameFramework\Db\Globals;
use Exception;
use TestDatas;

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
            //RECODE the states machines by reading diagram from states.inc.php
            case ST_NEXT_TURN: 
                switch($transition){
                    case 'end': GamestateMachine::$test_current_state = ST_END_SCORING;
                        break;
                    default: GamestateMachine::$test_current_state = ST_BEFORE_TURN;
                        break;
                }
                break;
            default:
                 break;
        }
        
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
        return null;
    }

    public function getCollectionFromDb(string $sql, bool $singleColumn = false): array
    {
        logForTests("getCollectionFromDb: $sql");
        return [];
    }
    public static function getObjectListFromDB(string $sql, bool $bUniqueValue = false): array
    {
        logForTests("getObjectListFromDB: $sql");
        
        switch($sql){
            case 'SELECT name AS `result_associative_index` , `value` , `name` FROM `my_global_variables`':
                logForTests("MOCK select globals");
                return [
                    ['name' => 'turn', 'value' => 1, 'result_associative_index' => 'turn'],
                    ['name' => 'era', 'value' => 1, 'result_associative_index' => 'era'],
                    ['name' => 'round', 'value' => 1, 'result_associative_index' => 'round'],
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
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('3'))":
                return [
                    TestDatas::$cards[3],
                ];
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
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `player_id` , `type` , `subtype` , `tile_start_location` , `tile_row` , `tile_col` FROM `tiles` WHERE (`tile_id` IN ('1'))":
                return [
                        TestDatas::$tiles[1],
                    ];
        }
        if( str_starts_with( $sql, 'SELECT *, player_id AS `result_associative_index` FROM `player`' )){
            logForTests("MOCK select players");
            return [
                TestDatas::$players[1],
                TestDatas::$players[2],
            ];
        }

        logForTests(" /!\ __ don't know what to return for this request __ /!\ ");
        return [];
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
        logForTests("DbQuery: $sql");
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
        logForTests("notifyAllPlayers: $notificationType, $notificationLog", "NOTIF");
    }

    /**
     */
    public function notifyPlayer(int $playerId, string $notificationType, string $notificationLog, array $notificationArgs): void
    {
        logForTests("notifyPlayer: $playerId, $notificationType, $notificationLog", "NOTIF");
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
