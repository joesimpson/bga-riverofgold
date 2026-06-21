<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use feException;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\Collection;
use ROG\Managers\Players;
use ROG\Managers\ShoreSpaces;
use ROG\Models\MAIN_ACTION;
use ROG\Models\ScenarioType;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertFalse;

final class BuildTest extends TestCase
{

    // -------------------------------------------------

    public function test_Args_Build(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        Globals::setChoices(0);
        $expectedArgs = [
            'spaces' => new Collection([
                ShoreSpaces::getShoreSpace(1),
                ShoreSpaces::getShoreSpace(2),
                ShoreSpaces::getShoreSpace(3),
            ]),
            'tiles' => [31,32,33,34],
            'markerForEraTiles' => null,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBuild();
        
        self::assertEquals($expectedArgs, $args);
        assertSame(null, Globals::getTurnMainActionDone());
    }
    
    public function test_Args_Build_WithMasterEngineer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $expectedArgs = new Collection([
                ShoreSpaces::getShoreSpace(1 ),
                ShoreSpaces::getShoreSpace(2 ),
                ShoreSpaces::getShoreSpace(3 ),
                ShoreSpaces::getShoreSpace(7 ),
                ShoreSpaces::getShoreSpace(8 ),
                ShoreSpaces::getShoreSpace(9 ),
                ShoreSpaces::getShoreSpace(10),
                ShoreSpaces::getShoreSpace(11),
                ShoreSpaces::getShoreSpace(12),
                ShoreSpaces::getShoreSpace(13),
                ShoreSpaces::getShoreSpace(14),
                ShoreSpaces::getShoreSpace(15),
                ShoreSpaces::getShoreSpace(16),
                ShoreSpaces::getShoreSpace(18),
                ShoreSpaces::getShoreSpace(19),
                ShoreSpaces::getShoreSpace(20),
                ShoreSpaces::getShoreSpace(21),
                ShoreSpaces::getShoreSpace(22),
                ShoreSpaces::getShoreSpace(23),
                ShoreSpaces::getShoreSpace(24),
                ShoreSpaces::getShoreSpace(25),
                ShoreSpaces::getShoreSpace(26),
                ShoreSpaces::getShoreSpace(27),
                ShoreSpaces::getShoreSpace(28),
                ShoreSpaces::getShoreSpace(30),
            ]);

        $args = $game->argBuild();
        
        assertEquals($expectedArgs, $args['spaces']);
    }
    
    public function test_Args_Build_WithMasterEngineer_Scenario1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        $expectedArgs = new Collection([
                // ONLY LEFT
                ShoreSpaces::getShoreSpace(2 ),
                ShoreSpaces::getShoreSpace(8 ),
                ShoreSpaces::getShoreSpace(10),
                ShoreSpaces::getShoreSpace(12),
                ShoreSpaces::getShoreSpace(14),
                ShoreSpaces::getShoreSpace(16),
                ShoreSpaces::getShoreSpace(19),
                ShoreSpaces::getShoreSpace(21),
                ShoreSpaces::getShoreSpace(22),
                ShoreSpaces::getShoreSpace(24),
                ShoreSpaces::getShoreSpace(26),
                ShoreSpaces::getShoreSpace(28),
            ]);

        $args = $game->argBuild();
        
        assertEquals($expectedArgs, $args['spaces']);
    }
    
    public function test_Args_Build_OpponentWithScenario1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$test_activePlayerId = 2;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        $expectedArgs = new Collection([
                //Empty Left + RIGHT IN REGION 1
                ShoreSpaces::getShoreSpace(1 ),
                ShoreSpaces::getShoreSpace(2 ),
                ShoreSpaces::getShoreSpace(3),
            ]);

        $args = $game->argBuild();
        
        assertEquals($expectedArgs, $args['spaces']);
    }
    
    public function test_listPossibleSpacesToBuild_Scenario1_Automa_REGION1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        Globals::setAutomaDie(1);
        //IF PLAYER HAS THE SCENARIO, 
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        $expectedArgs = new Collection([
                // ONLY RIGHT IN REGION 1
                ShoreSpaces::getShoreSpace(1 ),
                ShoreSpaces::getShoreSpace(3 ),
            ]);

        $buildSpaces = $game->listPossibleSpacesToBuild($player);
        
        assertEquals($expectedArgs, $buildSpaces);
    }
    public function test_listPossibleSpacesToBuild_Scenario1_Automa_REGION2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $player = Players::automaPlayer();
        Globals::setAutomaDie(2);
        //IF PLAYER HAS THE SCENARIO, 
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        $expectedArgs = new Collection([
                // ONLY RIGHT IN REGION 2
                ShoreSpaces::getShoreSpace(7 ),
                ShoreSpaces::getShoreSpace(9 ),
            ]);

        $buildSpaces = $game->listPossibleSpacesToBuild($player);
        
        assertEquals($expectedArgs, $buildSpaces);
    }
    
    public function test_listPossibleSpacesToBuild_ScenarioUnicorn1_OwnerBlockedByResources(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['die_face'] = 3;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        $player = Players::get(1);
        //IF PLAYER HAS THE SCENARIO, 
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
         TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."11",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
         TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."13",'type' => MEEPLE_TYPE_RESOURCE_SILK,  'player_id' => null,  ];
         TestDatas::$tokens[45] = ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."14",'type' => MEEPLE_TYPE_RESOURCE_POTTERY,  'player_id' => null,  ];
        $expectedArgs = new Collection([
                ShoreSpaces::getShoreSpace(12 ),
                ShoreSpaces::getShoreSpace(15 ),
            ]);

        $buildSpaces = $game->listPossibleSpacesToBuild($player);
        
        assertEquals($expectedArgs, $buildSpaces);
    }
    public function test_listPossibleSpacesToBuild_ScenarioUnicorn1_CoopNotBlockedByResources(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['die_face'] = 3;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        $player = Players::get(1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 2, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
         TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."11",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
         TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."13",'type' => MEEPLE_TYPE_RESOURCE_SILK,  'player_id' => null,  ];
         TestDatas::$tokens[45] = ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."14",'type' => MEEPLE_TYPE_RESOURCE_POTTERY,  'player_id' => null,  ];
        $expectedArgs = new Collection([
                ShoreSpaces::getShoreSpace(11 ),
                ShoreSpaces::getShoreSpace(12 ),
                ShoreSpaces::getShoreSpace(13 ),
                ShoreSpaces::getShoreSpace(14 ),
                ShoreSpaces::getShoreSpace(15 ),
            ]);

        $buildSpaces = $game->listPossibleSpacesToBuild($player);
        
        assertEquals($expectedArgs, $buildSpaces);
    }

    public function test_Args_Build_Shin3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_3;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        Globals::setChoices(0);
        $expectedArgs = [
            'spaces' => new Collection([
                ShoreSpaces::getShoreSpace(1),
                ShoreSpaces::getShoreSpace(2),
                ShoreSpaces::getShoreSpace(3),
            ]),
            'tiles' => [31,32,33,34, 21, 101, ],
            'markerForEraTiles' => 43,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBuild();
        
        self::assertEquals($expectedArgs, $args);
    }
    
    public function test_Args_Build_WithLionMarker_Own(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        Globals::setChoices(0);
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE.'1','type' => MEEPLE_TYPE_LION_MARKER,  'player_id' => 1,  ];
        $expectedArgs = [
            'spaces' => new Collection([
                ShoreSpaces::getShoreSpace(1),
                ShoreSpaces::getShoreSpace(2),
                ShoreSpaces::getShoreSpace(3),
            ]),
            'tiles' => [31,32,33,34],
            'markerForEraTiles' => null,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBuild();
        
        self::assertEquals($expectedArgs, $args);
    }
    public function test_Args_Build_WithLionMarker_Opponent(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        Globals::setChoices(0);
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE.'1','type' => MEEPLE_TYPE_LION_MARKER,  'player_id' => 2,  ];
        $expectedArgs = [
            'spaces' => new Collection([
                //No Space 1
                ShoreSpaces::getShoreSpace(2),
                ShoreSpaces::getShoreSpace(3),
            ]),
            'tiles' => [31,32,33,34],
            'markerForEraTiles' => null,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBuild();
        
        self::assertEquals($expectedArgs, $args);
    }

    // -------------------------------------------------
    
    public function test_actBuildSelect_KO_NoMoney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        
        $position = 1;
        $tileId = 1;
        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot build on $position");
        $game->actBuildSelect($position,$tileId,999999);
    }
    
    public function test_actBuildSelect_KO_NotEnoughMoney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":5}';
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        
        $position = 1;
        $tileId = 1;
        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot build on $position");
        $game->actBuildSelect($position,$tileId,999999);
    }
    
    public function test_actBuildSelect_KO_WrongRegion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        
        $position = 29;
        $tileId = 1;
        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot build on $position");
        $game->actBuildSelect($position,$tileId,999999);
    }
    
    public function test_actBuildSelect_KO_WrongTileId(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        
        $position = 1;
        $tileId = 1;
        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot build tile $tileId");
        $game->actBuildSelect($position,$tileId,999999);
    }
    
    public function test_actBuildSelect_KO_WrongTileLocation(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        
        $position = 1;
        $tileId = 999;
        $this->expectException(feException::class);
        $this->expectExceptionMessage("Class Pieces: getMany, some pieces have not been found ! Table tiles [$tileId]");
        $game->actBuildSelect($position,$tileId,999999);
    }
    
    public function test_actBuildSelect_Pass_Standard(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":20}';
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = '[]';
        TestDatas::$cards[101]['type'] = PATRON_GOVERNOR;

        $position = 1;
        $tileId = 31;
        $game->actBuildSelect($position,$tileId,999999);
        
        //Test go to next state
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
        assertSame(MAIN_ACTION::BUILD->value, Globals::getTurnMainActionDone());
        //Test spend money to build :
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(14, $resources[RESOURCE_TYPE_MONEY]);
        //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$tileId]['tile_location']);
        assertSame($position, TestDatas::$tiles[$tileId]['tile_state']);
        //Test new clan marker
        assertSame(TestDatas::$tokens[999], ['result_associative_index' => 999, 'meeple_id' => 999, 'meeple_state' => $position, 'meeple_location'=> "tile-$tileId",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1, ] );
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsBuild']);
        //Test gained influence :
        assertSame(1, TestDatas::$tokens[1]['meeple_state']);
    }
    
    public function test_actBuildSelect_Pass_RowEnd(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":20}';
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = '[]';
        TestDatas::$cards[101]['type'] = PATRON_GOVERNOR;

        $position = 1;
        $tileId = 34;
        $game->actBuildSelect($position,$tileId,999999);
        
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(BUILDING_ROW_END_FAVOR, $resources[RESOURCE_TYPE_SUN]);
        //Test spend money to build :
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(14, $resources[RESOURCE_TYPE_MONEY]);
        //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$tileId]['tile_location']);
        assertSame($position, TestDatas::$tiles[$tileId]['tile_state']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsBuild']);
        //Test new clan marker
        assertSame(TestDatas::$tokens[999], ['result_associative_index' => 999, 'meeple_id' => 999, 'meeple_state' => $position, 'meeple_location'=> "tile-$tileId",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1, ] );
        //Test gained influence :
        assertSame(1, TestDatas::$tokens[1]['meeple_state']);
    }
    
    public function test_actBuildSelect_Pass_MasterEngineer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":20}';
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = '[]';
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        $position = 30;
        $tileId = 31;
        $game->actBuildSelect($position,$tileId,999999);
        
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_actBuildSelect_Pass_PatronTrader(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":20}';
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = '[]';
        TestDatas::$cards[101]['type'] = PATRON_TRADER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        $position = 1;
        $tileId = 31;
        $game->actBuildSelect($position,$tileId,999999);
        
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
        //Test score +1
        assertSame(20, TestDatas::$players[TestDatas::$test_activePlayerId]['player_score']);
    }

    public function test_actBuildSelect_Pass_Darling(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":20}';
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = '[]';
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        $position = 1;
        $tileId = 31;
        $game->actBuildSelect($position,$tileId,999999);
        
        $expectedBonuses = json_encode([BONUS_TYPE_SET_DIE]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_actBuildSelect_Pass_LadyOfLions(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":20}';
        TestDatas::$players[1]['bonuses'] = '[]';
        TestDatas::$cards[101]['type'] = PATRON_LIONS_LADY;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE.'1','type' => MEEPLE_TYPE_LION_MARKER,  'player_id' => 1,  ];
        $position = 1;
        $tileId = 31;
        $expectedBonuses = [
            'datas'=>[
                BONUS_TYPE_BUILDING_REWARD => [ 1=> ['tile'=> $tileId ,'position'=>$position,'bonusQuantity'=>1,]], 
            ],
            BONUS_TYPE_PLACE_LION,
        ];

        $game->actBuildSelect($position,$tileId,999999);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertFalse( array_key_exists(101,TestDatas::$tokens));//deleted clan marker
        assertSame($tileId, Globals::getLastBuiltTile());
    }
    
    public function test_actBuildSelect_Pass_EnoughMoneyWithArtisanMarker(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        //TestDatas::$test_activePlayerId = 2;
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":5}';
        $position = 1;
        $tileId = 31;

        //TEST 1 with Artisan marker -> PASS 
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ARTISAN.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ];
        $game->actBuildSelect($position,$tileId,999999);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_MONEY]);

        //Test 2 without Artisan marker -> KO
        unset(TestDatas::$tokens[101]);
        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot build on $position");
        $game->actBuildSelect($position,$tileId,999999);
    }
    
    public function test_actBuildSelect_Pass_CustomerTrader(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 2;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":20}';
        TestDatas::$cards[13]['type'] = CARD_TRADER_2;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        $position = 7;
        $tileId = 31;
        
        $game->actBuildSelect($position,$tileId,999999);
        
        //ONGOING Ability must be activated right now :
        assertSame(20, TestDatas::$players[1]['player_score']);//+1
        assertSame(json_encode([BONUS_TYPE_DRAW]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }

    public function test_actBuildSelect_Pass_CustomerShin3_Era1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 2;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":20}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_3;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        
        $position = 9;
        $tileId = 21;
        
        $game->actBuildSelect($position,$tileId,999999);

        //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$tileId]['tile_location']);
        assertSame($position, TestDatas::$tiles[$tileId]['tile_state']);
        assertSame(TILE_LOCATION_BUILDING_DECK_ERA_1, Globals::getLastBuiltLocationOrigin());
        //Test new clan marker on building Instead of old one :
        assertSame(TestDatas::$tokens[43], ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> "tile-$tileId",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1, ] );
        assertSame(1, TestDatas::$stats[1]['nbActionsBuild']);
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_RICE]);//No influence gain
        assertSame(4, $resourcesP1[RESOURCE_TYPE_MOON]);
        assertSame(1, $resourcesP1[RESOURCE_TYPE_SUN ]);
        assertSame(8, $resourcesP1[RESOURCE_TYPE_MONEY]);
    }

    public function test_actBuildSelect_Pass_CustomerShin3_Era2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 2;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":20}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_3;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        
        $position = 9;
        $tileId = 101;
        
        $game->actBuildSelect($position,$tileId,999999);

        //Test built Tile :
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[$tileId]['tile_location']);
        assertSame($position, TestDatas::$tiles[$tileId]['tile_state']);
        assertSame(TILE_LOCATION_BUILDING_DECK_ERA_2, Globals::getLastBuiltLocationOrigin());
        //Test new clan marker on building Instead of old one :
        assertSame(TestDatas::$tokens[43], ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> "tile-$tileId",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1, ] );
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsBuild']);
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resourcesP1[RESOURCE_TYPE_RICE]);//Influence gain
        assertSame(4, $resourcesP1[RESOURCE_TYPE_MOON]);
        assertSame(1, $resourcesP1[RESOURCE_TYPE_SUN ]);
        assertSame(8, $resourcesP1[RESOURCE_TYPE_MONEY]);
    }
    
    public function test_actBuildSelect_Pass_Scenario_Mantis1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 6;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":20}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::MANTIS_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$tokens[27] = TestDatas::$tokens[26];
        TestDatas::$tokens[27]['meeple_id'] = 27;
        TestDatas::$tokens[27]['result_associative_index'] = 27;
        TestDatas::$tokens[27]['player_id'] = ROGUE_PLAYER_ID;
        TestDatas::$tokens[27]['meeple_state'] = 14;
        $position = 30;
        $tileId = 31;
        $expectedNotifs = [
            "spendMoney-1",
            "build-1",
            "newClanMarker-1",
            "gainInfluence-1",
            "moveRogueShip-1",
        ];
        
        $game->actBuildSelect($position,$tileId,999999);

        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //MOVED rogue ship
        assertSame(13, TestDatas::$tokens[27]['meeple_state']);
    }
    
    public function test_actBuildSelect_Pass_Scenario_Mantis1_removeRogueShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 2;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":4,"5":0,"6":20}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 2, 'type' => ScenarioType::MANTIS_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$tokens[27] = TestDatas::$tokens[26];
        TestDatas::$tokens[27]['meeple_id'] = 27;
        TestDatas::$tokens[27]['result_associative_index'] = 27;
        TestDatas::$tokens[27]['player_id'] = ROGUE_PLAYER_ID;
        TestDatas::$tokens[27]['meeple_state'] = 3;
        TestDatas::$tokens[21]['meeple_state'] = 1;
        TestDatas::$tokens[22]['meeple_state'] = 2;
        $position = 9;
        $tileId = 31;
        $expectedNotifs = [
            "spendMoney-1",
            "build-1",
            "newClanMarker-1",
            "gainInfluence-1",
            "moveRogueShip-1",
            "removeShip-1",
        ];
        
        $game->actBuildSelect($position,$tileId,999999);

        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //REMOVED rogue ship
        assertFalse(array_key_exists(27,TestDatas::$tokens));
    }
    
    public function test_actBuildSelect_Pass_Scenario_Unicorn1_removeResourceWithCoop_WithoutResource(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 2;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":3,"5":0,"6":20}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 2, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        $position = 9;
        $tileId = 31;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."$position",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        
        $game->actBuildSelect($position,$tileId,999999);

        $expectedNotifs = [
            "spendMoney-1",
            "removeClanMarker-1",//removeResourceMarker
            //"spendResource-1",
            "build-1",
            "newClanMarker-1",
            "gainInfluence-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertFalse(array_key_exists(43,TestDatas::$tokens));
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(8, $resources[RESOURCE_TYPE_MONEY]);//-12
    }
    
    public function test_actBuildSelect_Pass_Scenario_Unicorn1_removeResourceWithCoop_WithResource(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 2;
        TestDatas::$players[1]['resources'] = '{"1":4,"2":5,"3":6,"4":3,"5":0,"6":20}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 2, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        $position = 9;
        $tileId = 31;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."$position",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        
        $game->actBuildSelect($position,$tileId,999999);

        $expectedNotifs = [
            "spendMoney-1",
            "removeClanMarker-1",//removeResourceMarker
            "spendResource-1",
            "build-1",
            "newClanMarker-1",
            "gainInfluence-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertFalse(array_key_exists(43,TestDatas::$tokens));
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(4, $resources[RESOURCE_TYPE_SILK]);
        assertSame(5, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(5, $resources[RESOURCE_TYPE_RICE]);//-1
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(8, $resources[RESOURCE_TYPE_MONEY]);//-12
    }
    
    public function test_actBuildSelect_Pass_Scenario_Unicorn1_LadyOfLions(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":1,"4":4,"5":0,"6":20}';
        TestDatas::$players[1]['bonuses'] = '[]';
        TestDatas::$cards[101]['type'] = PATRON_LIONS_LADY;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 2, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        $position = 1;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."$position",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."$position",'type' => MEEPLE_TYPE_LION_MARKER,  'player_id' => 1,  ];
        $tileId = 31;
        $expectedBonuses = [
            'datas'=>[
                BONUS_TYPE_BUILDING_REWARD => [ 1=> ['tile'=> $tileId ,'position'=>$position,'bonusQuantity'=>1,]], 
            ],
            BONUS_TYPE_PLACE_LION,
        ];

        $game->actBuildSelect($position,$tileId,999999);
        
        $expectedNotifs = [
            "spendMoney-1",
            "removeClanMarker-1",//lion
            "addBonus-1",
            "addBonus-1",
            "removeClanMarker-1",//removeResourceMarker
            "spendResource-1",
            "build-1",
            "newClanMarker-1",
            "gainInfluence-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertFalse(array_key_exists(44,TestDatas::$tokens));//deleted clan marker
        assertFalse(array_key_exists(43,TestDatas::$tokens));
    }
    // -------------------------------------------------
}