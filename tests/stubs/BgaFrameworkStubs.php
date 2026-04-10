<?php

declare(strict_types=1);

namespace Bga\GameFramework;

use Exception;

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
            case ST_NEXT_TURN: 
                GamestateMachine::$test_current_state = ST_BEFORE_TURN;
                break;
            default:
                 break;
        }
        
    }
}


abstract class Table
{
    //added for tests
    static int $test_activePlayerId = 1;
    static array $test_players = [
           1 => ['player_id' => 1, 'money' => 87 ,'ships' => 12 ,'player_name' => 'Player_1', 'player_score' => 19, 'contracts' => 1, 'compasses' => 0, 'nbActions' => 0, 'passed' => 0, 'actionPlayed' => 0, 'cardsPlayed' => '[]','result_associative_index' => 1, ],
           2 => ['player_id' => 2, 'money' => 88 ,'ships' => 15 ,'player_name' => 'Player_2', 'player_score' => 27, 'contracts' => 1, 'compasses' => 0, 'nbActions' => 0, 'passed' => 0, 'actionPlayed' => 0, 'cardsPlayed' => '[]','result_associative_index' => 2, ],
        ];

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
        static::$test_activePlayerId = 2;
        return static::$test_activePlayerId;
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
        $tokens = [
                
        ];
        $cards = [
           1 => ['result_associative_index' => 1,'card_id' => 1, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 1, 'type' => CARD_ARTISAN_1, 'subtype' => CARD_TYPE_CUSTOMER,],
           2 => ['result_associative_index' => 2,'card_id' => 2, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 2, 'type' => CARD_ARTISAN_2, 'subtype' => CARD_TYPE_CUSTOMER,],
           3 => ['result_associative_index' => 3,'card_id' => 3, 'card_location' => CARD_LOCATION_HAND, 'card_state' => 0, 'player_id' => 2, 'type' => CARD_ARTISAN_3, 'subtype' => CARD_TYPE_CUSTOMER,],

        ];
        $tiles = [
        ];
        
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
                    Table::$test_players[1],
                    ];
            case "SELECT *, player_id AS `result_associative_index` FROM `player` WHERE  `player_id` = '0' LIMIT 1":
            case 'SELECT *, player_id AS `result_associative_index` FROM `player` WHERE  `player_id` = 2 LIMIT 1': 
                logForTests("MOCK select  player two ");
                return [
                    Table::$test_players[2],
                    ];
            case 'SELECT player_score,player_id FROM `player` WHERE `player_id` = 2':
                return [
                    Table::$test_players[2],
                ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('1'))":
                return [
                    $cards[1],
                ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('2'))":
                return [
                    $cards[2],
                ];
            case "SELECT card_id AS `result_associative_index` , `card_id` , `card_location` , `card_state` , `player_id` , `type` , `subtype` FROM `cards` WHERE (`card_id` IN ('3'))":
                return [
                    $cards[3],
                ];
            case "SELECT player_score,player_id FROM `player` WHERE `player_id` = 1":
            case "SELECT player_id AS `result_associative_index` , `player_score` FROM `player` WHERE `player_id` = 1":
                return[
                    Table::$test_players[1],
                ];
            case "SELECT player_score,player_id FROM `player` WHERE `player_id` = 2":
            case "SELECT player_id AS `result_associative_index` , `player_score` FROM `player` WHERE `player_id` = 2":
                return[
                    Table::$test_players[2],
                ];
            case "SELECT tile_id AS `result_associative_index` , `tile_id` , `tile_location` , `tile_state` , `player_id` , `type` , `subtype` , `tile_start_location` , `tile_row` , `tile_col` FROM `tiles` WHERE (`tile_id` IN ('1'))":
                return [
                        $tiles[1],
                    ];
        }
        if( str_starts_with( $sql, 'SELECT *, player_id AS `result_associative_index` FROM `player`' )){
            logForTests("MOCK select players");
            return [
                Table::$test_players[1],
                Table::$test_players[2],
            ];
        }

        logForTests(" /!\ __ don't know what to return for this request __ /!\ ");
        return [];
    }

    public function getCurrentPlayerId(bool $bReturnNullIfNotLogged = false): string|int
    {
        return static::$test_activePlayerId; 
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
        return static::$test_activePlayerId; 
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
        logForTests("Notify_player $playerId $notifName : $message, with args ".json_encode($args)."");
    }
    public function all(string $notifName, string $message = '', array $args = []): void {
        logForTests("Notify_ALL $notifName : $message, with args ".json_encode($args)."");
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
