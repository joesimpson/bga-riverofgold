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
                        [
                            'id' => 101,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_MASTER_ENGINEER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Master Engineer',
                            'name' => 'Kaiu Shihobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                        [
                            'id' => 102,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_TRADER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Wily Trader',
                            'name' => 'Yasuki Taka',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                    [
                        'id' => 103,
                        'location' => CARD_CLAN_LOCATION_DRAFT,
                        'pId' => 2,
                        'type' => PATRON_LADY,
                        'clan' => CLAN_SCORPION,
                        'abilityName' => 'Lady of Whispers',
                        'name' => 'Bayushi Kashiko',
                        'desc' => '',
                        'subtype' => CARD_TYPE_CLAN_PATRON,
                    ],
                    [
                        'id' => 104,
                        'location' => CARD_CLAN_LOCATION_DRAFT,
                        'pId' => 2,
                        'type' => PATRON_GOVERNOR,
                        'clan' => CLAN_SCORPION,
                        'abilityName' => 'Governor of the City of lies',
                        'name' => 'Shosuro Hyobu',
                        'desc' => '',
                        'subtype' => CARD_TYPE_CLAN_PATRON,
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
                        [
                            'id' => 101,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_MASTER_ENGINEER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Master Engineer',
                            'name' => 'Kaiu Shihobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                        [
                            'id' => 102,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_TRADER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Wily Trader',
                            'name' => 'Yasuki Taka',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                    [
                        'id' => 103,
                        'location' => CARD_CLAN_LOCATION_DRAFT,
                        'pId' => 2,
                        'type' => PATRON_LADY,
                        'clan' => CLAN_SCORPION,
                        'abilityName' => 'Lady of Whispers',
                        'name' => 'Bayushi Kashiko',
                        'desc' => '',
                        'subtype' => CARD_TYPE_CLAN_PATRON,
                    ],
                    [
                        'id' => 104,
                        'location' => CARD_CLAN_LOCATION_DRAFT,
                        'pId' => 2,
                        'type' => PATRON_GOVERNOR,
                        'clan' => CLAN_SCORPION,
                        'abilityName' => 'Governor of the City of lies',
                        'name' => 'Shosuro Hyobu',
                        'desc' => '',
                        'subtype' => CARD_TYPE_CLAN_PATRON,
                    ],
            ],
            'scenarios' => [
                301 => [
                    'id' => 301,
                    'location' => CARD_SCENARIO_LOCATION_DRAFT,
                    'pId' => null,
                    'type' => 1,
                    'clan' => 1,
                    'name' => '',
                    'subtype' => CARD_TYPE_SCENARIO,
                ],
                302 => [
                    'id' => 302,
                    'location' => CARD_SCENARIO_LOCATION_DRAFT,
                    'pId' => null,
                    'type' => 2,
                    'clan' => 2,
                    'name' => '',
                    'subtype' => CARD_TYPE_SCENARIO,
                ],
                303 => [
                    'id' => 303,
                    'location' => CARD_SCENARIO_LOCATION_DRAFT,
                    'pId' => null,
                    'type' => 3,
                    'clan' => 3,
                    'name' => '',
                    'subtype' => CARD_TYPE_SCENARIO,
                ],
                304 => [
                    'id' => 304,
                    'location' => CARD_SCENARIO_LOCATION_DRAFT,
                    'pId' => null,
                    'type' => 4,
                    'clan' => 4,
                    'name' => '',
                    'subtype' => CARD_TYPE_SCENARIO,
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

        $this->expectException(feException::class);
        $this->expectExceptionMessage("Class Pieces: getMany, some pieces have not been found ! Table cards [$cardId]");
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
        $this->expectExceptionMessage("Scenario $scenarioId must match patron clan");
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
                        [
                            'id' => 101,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_MASTER_ENGINEER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Master Engineer',
                            'name' => 'Kaiu Shihobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                        [
                            'id' => 102,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 1,
                            'type' => PATRON_TRADER,
                            'clan' => CLAN_CRAB,
                            'abilityName' => 'Wily Trader',
                            'name' => 'Yasuki Taka',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],

                    ],
                ],
                2 => [
                    'cards' => [ 
                        [
                            'id' => 103,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 2,
                            'type' => PATRON_LADY,
                            'clan' => CLAN_SCORPION,
                            'abilityName' => 'Lady of Whispers',
                            'name' => 'Bayushi Kashiko',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                        [
                            'id' => 104,
                            'location' => CARD_CLAN_LOCATION_DRAFT,
                            'pId' => 2,
                            'type' => PATRON_GOVERNOR,
                            'clan' => CLAN_SCORPION,
                            'abilityName' => 'Governor of the City of lies',
                            'name' => 'Shosuro Hyobu',
                            'desc' => '',
                            'subtype' => CARD_TYPE_CLAN_PATRON,
                        ],
                    ],
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