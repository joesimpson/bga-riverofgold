<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use feException;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Managers\Players;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class DraftTest extends TestCase
{

    // ----------------------------------------------------------------------

    public function testArgs_Draft(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DRAFT;

        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;
        $args = $game->argDraft();
        $expectedArgs = [
            'cards' => [ 
                    101 => [
                            'id' => 101,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_MASTER_ENGINEER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Master Engineer',
                            'name' => 'Kaiu Shihobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [],
                        ],
                    102 => [
                            'id' => 102,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_TRADER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Wily Trader',
                            'name' => 'Yasuki Taka',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [],
                        ],
                    103 => [
                        'id' => 103,
                        'location' => CARD_CLAN_LOCATION_DRAFT,
                        'pId' => 2,
                        'type' => PATRON_LADY,
                        'clan' => CLAN_SCORPION,
                        'abilityName' => 'Lady of Whispers',
                        'name' => 'Bayushi Kashiko',
                        'desc' => '',
                        'subtype' => CARD_TYPE_CLAN_PATRON,
                        'scenario_ids' => [],
                    ],
                    104 => [
                        'id' => 104,
                        'location' => CARD_CLAN_LOCATION_DRAFT,
                        'pId' => 2,
                        'type' => PATRON_GOVERNOR,
                        'clan' => CLAN_SCORPION,
                        'abilityName' => 'Governor of the City of lies',
                        'name' => 'Shosuro Hyobu',
                        'desc' => '',
                        'subtype' => CARD_TYPE_CLAN_PATRON,
                        'scenario_ids' => [],
                    ],
            ],
            'scenarios' => [],
        ];
        
        assertSame($expectedArgs, $args);
    }
    
    public function testArgs_Draft_Scenarios(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionScenarios(OPTION_SCENARIOS_ON);
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => null, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[302] = ['result_associative_index' => 302,'card_id' => 302, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => null, 'type' => 2,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[303] = ['result_associative_index' => 303,'card_id' => 303, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => null, 'type' => 3,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[304] = ['result_associative_index' => 304,'card_id' => 304, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => null, 'type' => 4,   'subtype' => CARD_TYPE_SCENARIO,];
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;

        $args = $game->argDraft();

        $expectedArgs = [
            'cards' => [ 
                    101 => [
                            'id' => 101,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_MASTER_ENGINEER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Master Engineer',
                            'name' => 'Kaiu Shihobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [301, ],
                        ],
                    102 => [
                            'id' => 102,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_TRADER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Wily Trader',
                            'name' => 'Yasuki Taka',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [301, ],
                        ],
                    103 => [
                        'id' => 103,
                        'location' => CARD_CLAN_LOCATION_DRAFT,
                        'pId' => 2,
                        'type' => PATRON_LADY,
                        'clan' => CLAN_SCORPION,
                        'abilityName' => 'Lady of Whispers',
                        'name' => 'Bayushi Kashiko',
                        'desc' => '',
                        'subtype' => CARD_TYPE_CLAN_PATRON,
                        'scenario_ids' => [304, ],
                    ],
                    104 => [
                        'id' => 104,
                        'location' => CARD_CLAN_LOCATION_DRAFT,
                        'pId' => 2,
                        'type' => PATRON_GOVERNOR,
                        'clan' => CLAN_SCORPION,
                        'abilityName' => 'Governor of the City of lies',
                        'name' => 'Shosuro Hyobu',
                        'desc' => '',
                        'subtype' => CARD_TYPE_CLAN_PATRON,
                        'scenario_ids' => [304, ],
                    ],
            ],
            'scenarios' => [
                301 => [
                    'id' => 301,
                    'location' => CARD_SCENARIO_LOCATION_DRAFT,
                    'pId' => null,
                    'type' => 1,
                    'clan' => CLAN_CRAB,
                    'name' => '',
                    'difficulty' => 2,
                    'subtype' => CARD_TYPE_SCENARIO,
                    'resources' => null,
                ],
                302 => [
                    'id' => 302,
                    'location' => CARD_SCENARIO_LOCATION_DRAFT,
                    'pId' => null,
                    'type' => 2,
                    'clan' => CLAN_MANTIS,
                    'name' => '',
                    'difficulty' => 2,
                    'subtype' => CARD_TYPE_SCENARIO,
                    'resources' => null,
                ],
                303 => [
                    'id' => 303,
                    'location' => CARD_SCENARIO_LOCATION_DRAFT,
                    'pId' => null,
                    'type' => 3,
                    'clan' => CLAN_CRANE,
                    'name' => '',
                    'difficulty' => 4,
                    'subtype' => CARD_TYPE_SCENARIO,
                    'resources' => null,
                ],
                304 => [
                    'id' => 304,
                    'location' => CARD_SCENARIO_LOCATION_DRAFT,
                    'pId' => null,
                    'type' => 4,
                    'clan' => CLAN_SCORPION,
                    'name' => '',
                    'difficulty' => 3,
                    'subtype' => CARD_TYPE_SCENARIO,
                    'resources' => null,
                ],
            ],
        ];
        assertSame($expectedArgs, $args);
    }
    // ----------------------------------------------------------------------
    public function testEnteringState_Draft_GoToNextPlayer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DRAFT;

        GamestateMachine::$test_current_state = ST_DRAFT_NEXT_PLAYER;
        $game->stDraftNextPlayer();
        
        //let next player play
        assertSame(ST_DRAFT_PLAYER, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_Draft_end(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;

        GamestateMachine::$test_current_state = ST_DRAFT_NEXT_PLAYER;
        $game->stDraftNextPlayer();
        
        //Continue to next state
        assertSame(ST_PLAYER_SETUP, GamestateMachine::$test_current_state);
    }

    public function testEnteringState_Draft_autoAssignLastCard(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DISCARD;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DISCARD;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DRAFT;

        GamestateMachine::$test_current_state = ST_DRAFT_NEXT_PLAYER;
        $game->stDraftNextPlayer();
        
        //Continue to next state
        assertSame(ST_PLAYER_SETUP, GamestateMachine::$test_current_state);
    }

    
    // ----------------------------------------------------------------------
    public function test_actTakeCard_KO_card_doesnotexist(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $cardId = 99999;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Card $cardId is not selectable");
        $game->actTakeCard($cardId,999999);
    }
    public function test_actTakeCard_KO_card_notselectable(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $cardId = 101;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Card $cardId is not selectable");
        $game->actTakeCard($cardId,999999);
        
    }
    
    public function test_actTakeCard_KO_selectableForOtherPlayer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 2;
        $cardId = 101;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER_MULTIACTIVE;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Card $cardId is not selectable");
        $game->actTakeCard($cardId,999999);
        
    }
    
    public function test_actTakeCard_KO_MandatoryScenario(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionScenarios(OPTION_SCENARIOS_ON);
        $cardId = 101;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER_MULTIACTIVE;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 1, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[304] = ['result_associative_index' => 304,'card_id' => 304, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 2, 'type' => 4,   'subtype' => CARD_TYPE_SCENARIO,];

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You need to select a Scenario for clan patron $cardId");
        $game->actTakeCard($cardId,999999);
        
    }

    public function test_actTakeCard_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        $cardId = 101;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;

        $game->actTakeCard($cardId,999999);
        
        assertSame(ST_DRAFT_NEXT_PLAYER, GamestateMachine::$test_current_state);
        assertSame(CARD_CLAN_LOCATION_ASSIGNED, TestDatas::$cards[$cardId]['card_location']);
        assertSame(1, TestDatas::$cards[$cardId]['player_id']);
    }
    
    public function test_actTakeCard_Pass_Multi(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        $cardId = 101;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER_MULTIACTIVE;

        $game->actTakeCard($cardId,999999);
        
        assertSame(ST_DRAFT_PLAYER_MULTIACTIVE, GamestateMachine::$test_current_state);
    }
    
    public function test_actTakeCard_Pass_ScionOfVoid(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        $cardId = 101;
        TestDatas::$cards[$cardId]['type'] = PATRON_SCION_OF_VOID;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;

        $game->actTakeCard($cardId,999999);
        
        assertSame(TILE_LOCATION_MASTERY_RESERVED, TestDatas::$tiles[4]['tile_location']);
        assertSame(TILE_LOCATION_MASTERY_RESERVED, TestDatas::$tiles[5]['tile_location']);
        assertSame(TILE_LOCATION_MASTERY_RESERVED, TestDatas::$tiles[6]['tile_location']);
        assertSame(1, TestDatas::$tiles[4]['player_id']);
        assertSame(1, TestDatas::$tiles[5]['player_id']);
        assertSame(1, TestDatas::$tiles[6]['player_id']);
        assertSame(4, TestDatas::$tiles[4]['type']);
        assertSame(5, TestDatas::$tiles[5]['type']);
        assertSame(6, TestDatas::$tiles[6]['type']);
    }
    
    public function test_actTakeCard_Pass_WithScenario(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $cardId = 101;
        $scenarioId = 301;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;
        TestDatas::$cards[$scenarioId] = ['result_associative_index' => $scenarioId,'card_id' => $scenarioId, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => null, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];

        $game->actTakeCard($cardId,999999,$scenarioId);
        
        assertSame(ST_DRAFT_NEXT_PLAYER, GamestateMachine::$test_current_state);
        assertSame(CARD_CLAN_LOCATION_ASSIGNED, TestDatas::$cards[$cardId]['card_location']);
        assertSame(1, TestDatas::$cards[$cardId]['player_id']);
        assertSame(CARD_SCENARIO_LOCATION_ASSIGNED, TestDatas::$cards[$scenarioId]['card_location']);
        assertSame(1, TestDatas::$cards[$scenarioId]['player_id']);
    }
    
    public function test_actTakeCard_setupScenarioCrab(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $cardId = 101;
        $scenarioId = 301;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;
        TestDatas::$cards[$scenarioId] = ['result_associative_index' => $scenarioId,'card_id' => $scenarioId, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => null, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::resetStartingBuildings();
        $expectedNotifs = [
            "newPlayerColor-1",
            "giveClanCardTo-1",
            "giveScenarioCard-1",
            "discardTiles",
        ];

        $game->actTakeCard($cardId,999999,$scenarioId);
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //TEST Scenario Setup
        //KEEP IMPERIAL MARKETS
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[44]['tile_location']);
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[45]['tile_location']);
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[46]['tile_location']);
        //Discard other Starting tiles
        assertSame(TILE_LOCATION_DISCARD, TestDatas::$tiles[47]['tile_location']);
        assertSame(TILE_LOCATION_DISCARD, TestDatas::$tiles[48]['tile_location']);
        assertSame(TILE_LOCATION_DISCARD, TestDatas::$tiles[49]['tile_location']);
        assertSame(TILE_LOCATION_DISCARD, TestDatas::$tiles[50]['tile_location']);
        assertSame(TILE_LOCATION_DISCARD, TestDatas::$tiles[51]['tile_location']);
        assertSame(TILE_LOCATION_DISCARD, TestDatas::$tiles[52]['tile_location']);
    }
    public function test_actTakeCard_setupScenarioNotCrab(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $cardId = 101;
        $scenarioId = 302;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[$cardId]['type'] = PATRON_PRIESTESS;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;
        TestDatas::$cards[$scenarioId] = ['result_associative_index' => $scenarioId,'card_id' => $scenarioId, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => null, 'type' => 2,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::resetStartingBuildings();
        $expectedNotifs = [
            "newPlayerColor-1",
            "giveClanCardTo-1",
            "giveScenarioCard-1",
        ];

        $game->actTakeCard($cardId,999999,$scenarioId);
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //TEST CRAB Scenario  Setup is NOT TRIGGERED
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[44]['tile_location']);
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[45]['tile_location']);
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[46]['tile_location']);
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[47]['tile_location']);
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[48]['tile_location']);
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[49]['tile_location']);
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[50]['tile_location']);
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[51]['tile_location']);
        assertSame(TILE_LOCATION_BUILDING_SHORE, TestDatas::$tiles[52]['tile_location']);
    }
    
    public function test_actTakeCard_KO_WrongScenario(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $cardId = 101;
        $scenarioId = 399;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Scenario $scenarioId is not selectable");
        $game->actTakeCard($cardId,999999,$scenarioId);
    }
    
    public function test_actTakeCard_KO_WrongScenarioClan(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        $cardId = 101;
        $scenarioId = 302;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => null, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[302] = ['result_associative_index' => 302,'card_id' => 302, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => null, 'type' => 2,   'subtype' => CARD_TYPE_SCENARIO,];

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Scenario $scenarioId is not selectable");
        $game->actTakeCard($cardId,999999,$scenarioId);
    }

    // ----------------------------------------------------------------------

    public function testArgs_DraftMulti(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DRAFT;

        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER_MULTIACTIVE;
        $args = $game->argDraftMulti();
        $expectedArgs = [
            '_private' => [ 
                1 => [
                    'cards' => [ 
                        101 => [
                            'id' => 101,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_MASTER_ENGINEER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Master Engineer',
                            'name' => 'Kaiu Shihobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [],
                        ],
                        102 => [
                            'id' => 102,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_TRADER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Wily Trader',
                            'name' => 'Yasuki Taka',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [],
                        ],

                    ],
                ],
                2 => [
                    'cards' => [ 
                        103 => [
                            'id' => 103,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 2,
                            'type' => PATRON_LADY,
                            'clan' => CLAN_SCORPION,
                            'abilityName' => 'Lady of Whispers',
                            'name' => 'Bayushi Kashiko',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [],
                        ],
                        104 => [
                            'id' => 104,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 2,
                            'type' => PATRON_GOVERNOR,
                            'clan' => CLAN_SCORPION,
                            'abilityName' => 'Governor of the City of lies',
                            'name' => 'Shosuro Hyobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [],
                        ],
                    ],
                ],
            ],
            'scenarios' => [],
        ];
        
        assertSame($expectedArgs, $args);
    }
    
    public function testArgs_DraftMulti_WithScenarios(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionScenarios(OPTION_SCENARIOS_ON);
        TestDatas::$cards[101]['type'] = PATRON_MASTER_ENGINEER;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[102]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[103]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[104]['card_location'] = CARD_CLAN_LOCATION_DRAFT;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 1, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[304] = ['result_associative_index' => 304,'card_id' => 304, 'card_location' => CARD_SCENARIO_LOCATION_DRAFT, 'card_state' => 0, 'player_id' => 2, 'type' => 4,   'subtype' => CARD_TYPE_SCENARIO,];
        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER_MULTIACTIVE;

        $args = $game->argDraftMulti();

        $expectedArgs = [
            '_private' => [ 
                1 => [
                    'cards' => [ 
                        101 => [
                            'id' => 101,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_MASTER_ENGINEER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Master Engineer',
                            'name' => 'Kaiu Shihobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [301, ],
                        ],
                        102 => [
                            'id' => 102,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_TRADER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Wily Trader',
                            'name' => 'Yasuki Taka',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [301, ],
                        ],

                    ],
                ],
                2 => [
                    'cards' => [ 
                        103 => [
                            'id' => 103,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 2,
                            'type' => PATRON_LADY,
                            'clan' => CLAN_SCORPION,
                            'abilityName' => 'Lady of Whispers',
                            'name' => 'Bayushi Kashiko',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [304, ],
                        ],
                        104 => [
                            'id' => 104,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 2,
                            'type' => PATRON_GOVERNOR,
                            'clan' => CLAN_SCORPION,
                            'abilityName' => 'Governor of the City of lies',
                            'name' => 'Shosuro Hyobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                            'scenario_ids' => [304, ],
                        ],
                    ],
                ],
            ],
             'scenarios' => [
                301 => [
                    'id' => 301,
                    'location' => CARD_SCENARIO_LOCATION_DRAFT,
                    'pId' => 1,
                    'type' => 1,
                    'clan' => CLAN_CRAB,
                    'name' => '',
                    'difficulty' => 2,
                    'subtype' => CARD_TYPE_SCENARIO,
                    'resources' => null,
                ],
                304 => [
                    'id' => 304,
                    'location' => CARD_SCENARIO_LOCATION_DRAFT,
                    'pId' => 2,
                    'type' => 4,
                    'clan' => CLAN_SCORPION,
                    'name' => '',
                    'difficulty' => 3,
                    'subtype' => CARD_TYPE_SCENARIO,
                    'resources' => null,
                ],
            ],
        ];
        
        assertSame($expectedArgs, $args);
    }

    // ----------------------------------------------------------------------

    public function testEnteringState_DraftMulti(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();

        GamestateMachine::$test_current_state = ST_DRAFT_PLAYER_MULTIACTIVE;
        $game->stDraftMulti();
        
        //STILL SAME STATE until players play
        assertSame(ST_DRAFT_PLAYER_MULTIACTIVE, GamestateMachine::$test_current_state);
    }
    // ----------------------------------------------------------------------
     
}