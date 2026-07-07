<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Models\CITY_CARD_TYPE;
use ROG\Models\ScenarioType;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertNotSame;

final class ScoringTest extends TestCase
{


    // -------------------------------------------------
    
    public function test_EnteringState_PreEndOfGame(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PRE_END_OF_GAME;

        $game->stPreEndOfGame();
        
        assertSame(ST_END_GAME, GamestateMachine::$test_current_state);
    }
    // -------------------------------------------------
    
    public function test_computeScoring_WithNoGains(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(19, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(2, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_WithInfluence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$tokens[1]['meeple_state'] = 10;
        TestDatas::$tokens[11]['meeple_state'] = 6;
        TestDatas::$tokens[2]['meeple_state'] = 3;
        TestDatas::$tokens[12]['meeple_state'] = 4;
        TestDatas::$tokens[3]['meeple_state'] = 7;
        TestDatas::$tokens[13]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 6;
        TestDatas::$tokens[14]['meeple_state'] = 1;
        TestDatas::$tokens[5]['meeple_state'] = 7;
        TestDatas::$tokens[15]['meeple_state'] = 6;
        TestDatas::$tokens[6]['meeple_state'] = 7;
        TestDatas::$tokens[16]['meeple_state'] = 5;
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 8,
                    REGION_2 => 3,
                    REGION_3 => 4,
                    REGION_4 => 5,
                    REGION_5 => 3,
                    REGION_6 => 7,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 4,
                    REGION_2 => 6,
                    REGION_3 => 0,
                    REGION_4 => 2,
                    REGION_5 => 0,
                    REGION_6 => 3,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(19+30, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(2+15, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Deliveries_1_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[15] = TestDatas::$cards[12];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(21, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(7, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Deliveries_3_4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13] = TestDatas::$cards[11];
        TestDatas::$cards[14] = TestDatas::$cards[11];
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[15] = TestDatas::$cards[12];
        TestDatas::$cards[16] = TestDatas::$cards[12];
        TestDatas::$cards[17] = TestDatas::$cards[12];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 9, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 14, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(28, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(16, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Deliveries_6_5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[14] = TestDatas::$cards[11];
        TestDatas::$cards[15] = TestDatas::$cards[11];
        TestDatas::$cards[16] = TestDatas::$cards[11];
        TestDatas::$cards[17] = TestDatas::$cards[11];
        TestDatas::$cards[18] = TestDatas::$cards[11];
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[20] = TestDatas::$cards[12];
        TestDatas::$cards[21] = TestDatas::$cards[12];
        TestDatas::$cards[22] = TestDatas::$cards[12];
        TestDatas::$cards[23] = TestDatas::$cards[12];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 27, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 20, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(46, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(22, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }

    public function test_computeScoring_WithElders(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$tokens[1]['meeple_state'] = 10;
        TestDatas::$tokens[11]['meeple_state'] = 6;
        TestDatas::$tokens[2]['meeple_state'] = 3;
        TestDatas::$tokens[12]['meeple_state'] = 4;
        TestDatas::$tokens[3]['meeple_state'] = 7;
        TestDatas::$tokens[13]['meeple_state'] = 1;
        TestDatas::$tokens[4]['meeple_state'] = 6;
        TestDatas::$tokens[14]['meeple_state'] = 1;
        TestDatas::$tokens[5]['meeple_state'] = 7;
        TestDatas::$tokens[15]['meeple_state'] = 6;
        TestDatas::$tokens[6]['meeple_state'] = 7;
        TestDatas::$tokens[16]['meeple_state'] = 5;
        TestDatas::$tokens[101] = ['result_associative_index' => 101, 'meeple_id' => 101, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER.'1','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ];
        TestDatas::$tokens[102] = ['result_associative_index' => 102, 'meeple_id' => 102, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER.'2','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[103] = ['result_associative_index' => 103, 'meeple_id' => 103, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER.'3','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        TestDatas::$tokens[104] = ['result_associative_index' => 104, 'meeple_id' => 104, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER.'5','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 1,  ];
        TestDatas::$tokens[105] = ['result_associative_index' => 105, 'meeple_id' => 105, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER.'6','type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => 2,  ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 8,
                    REGION_2 => 3,
                    REGION_3 => 4,
                    REGION_4 => 5,
                    REGION_5 => 3,
                    REGION_6 => 7,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 8+3,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 4,
                    REGION_2 => 6,
                    REGION_3 => 0,
                    REGION_4 => 2,
                    REGION_5 => 0,
                    REGION_6 => 3,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 6+0+3,
            ],
        ];

        $game->stScoring();
        
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(19+30+11, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(2+15+9, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Artisans(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":0,"3":1,"4":5,"5":0,"6":5}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$players[2]['resources'] = '{"1":0,"2":6,"3":4,"4":5,"5":0,"6":17}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, 
                SCORING_CUSTOMERS=> 2.0,//!\ Float used in computation
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 3.0,//!\ Float used in computation
            ],
        ];

        $game->stScoring();
        
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(26, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(7, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Merchants(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":0,"3":1,"4":5,"5":0,"6":5}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_MERCHANT_2;
        TestDatas::$players[2]['resources'] = '{"1":0,"2":6,"3":4,"4":5,"5":0,"6":17}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_MERCHANT_3;
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, 
                SCORING_CUSTOMERS=> 2.0,//!\ Float used in computation
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 3.0,//!\ Float used in computation
            ],
        ];

        $game->stScoring();
        
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(26, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(7, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Noble1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_1;
        TestDatas::$tiles[41]['type'] = 8;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 2,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(23, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(2, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    public function test_computeScoring_Noble2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_2;
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 1,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(22, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(2, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Noble3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_3;
        TestDatas::$tiles[41]['type'] = 15;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 2,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(23, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(2, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Noble4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_4;
        TestDatas::$tiles[41]['type'] = 22;
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 1,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(22, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(2, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Noble5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$tiles[41]['type'] = 2;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['type'] = 9;
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        //add a 3rd building tile  + marker
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['type'] = 25;
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['meeple_id'] = 44;
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 3,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(24, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(2, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Noble6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_6;
        TestDatas::$tiles[41]['type'] = 2;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['type'] = 9;
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['tile_state'] = 12;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        //add a 3rd building tile  + marker
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['type'] = 25;
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['meeple_id'] = 44;
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 2,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(23, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(2, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }

    public function test_computeScoring_Smuggler1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":0,"3":1,"4":5,"5":0,"6":15}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SMUGGLER_1;
        TestDatas::$players[2]['resources'] = '{"1":0,"2":6,"3":4,"4":5,"5":0,"6":17}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_MERCHANT_3;
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, 
                SCORING_CUSTOMERS=> 4.0,//3 + 1
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 3.0,//!\ Float used in computation
            ],
        ];

        $game->stScoring();
        
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(28, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(7, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Smuggler5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":0,"3":1,"4":5,"5":0,"6":15}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SMUGGLER_5;
        TestDatas::$players[2]['resources'] = '{"1":0,"2":6,"3":4,"4":5,"5":0,"6":17}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_MERCHANT_3;
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, 
                SCORING_CUSTOMERS=> 5.0,//3 + 2
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 3.0,//!\ Float used in computation
            ],
        ];

        $game->stScoring();
        
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(29, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(7, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Smuggler6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":0,"3":1,"4":5,"5":0,"6":25}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_3;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SMUGGLER_6;
        TestDatas::$players[2]['resources'] = '{"1":0,"2":6,"3":4,"4":5,"5":0,"6":17}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_MERCHANT_3;
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, 
                SCORING_CUSTOMERS=> 7.0,//5 + 2
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 3.0,//!\ Float used in computation
            ],
        ];

        $game->stScoring();
        
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(31, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(7, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Shins(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":0,"3":1,"4":5,"5":3,"6":15}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = 43;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = 44;
        TestDatas::$players[2]['resources'] = '{"1":0,"2":6,"3":4,"4":5,"5":1,"6":17}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = 45;
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, 
                SCORING_CUSTOMERS=> 6.0,//2*3
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 1.0,//1*1
            ],
        ];

        $game->stScoring();
        
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(30, TestDatas::$players[1]['player_score']);
        assertSame(3, TestDatas::$players[1]['player_score_aux']);
        assertSame(5, TestDatas::$players[2]['player_score']);
        assertSame(1, TestDatas::$players[2]['player_score_aux']);
    }
    
    public function test_computeScoring_Traders(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":0,"3":1,"4":5,"5":0,"6":25}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_3;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_TRADER_3;
        TestDatas::$tiles[41]['tile_state'] = 11;//in region 3
        TestDatas::$players[2]['resources'] = '{"1":0,"2":6,"3":4,"4":5,"5":0,"6":17}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_TRADER_6;
        TestDatas::$tiles[42]['tile_state'] = 11;//in region 3
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, 
                SCORING_CUSTOMERS=> 11.0,//5 + 6 (2*2 for cards in region 3 + 2 for 1 building in region 3 )
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 2,// 2 for cards in region + 0 for buildings in region
            ],
        ];

        $game->stScoring();
        
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(35, TestDatas::$players[1]['player_score']);
        assertSame(0, TestDatas::$players[1]['player_score_aux']);
        assertSame(6, TestDatas::$players[2]['player_score']);
        assertSame(0, TestDatas::$players[2]['player_score_aux']);
    }
    // -------------------------------------------------
    
    public function test_computeScoring_AutomaLevel1_Artisans(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreArtisans--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 9, //3 deliveries
                SCORING_CUSTOMERS => 3.0,//3* 1 for 3 goods (3 goods)
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(12, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_AutomaLevel2_Artisans(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreArtisans--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 9, //3 deliveries
                SCORING_CUSTOMERS => 6.0,//3* 1 for 3 goods * 2 (6 goods)
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(15, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_AutomaLevel3_Artisans(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_3);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreArtisans--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 9, //3 deliveries
                SCORING_CUSTOMERS => 9.0,//3* 1 for 3 goods * 3 (9 goods)
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(18, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_AutomaLevel4_Artisans(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_4);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreArtisans--123", 
            "eliminateByScore-1",
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 9, //3 deliveries
                SCORING_CUSTOMERS => 12.0,//3* 1 for 3 goods * 4 (12 goods)
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(21, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_AutomaLevel5_Artisans(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_5);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreArtisans--123", 
            "eliminateByScore-1",
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 9, //3 deliveries
                SCORING_CUSTOMERS => 15.0,//3* 1 for 3 goods * 5 (15 goods)
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL,  TestDatas::$players[1]['player_score']);//19 reset with defeat
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);//2 reset with defeat
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(24, Globals::getAutomaScore());

    }
    
    public function test_computeScoring_AutomaLevel1_Elders(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_ELDER_6;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['type'] = CARD_ELDER_1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_ELDER_4;
        //------------------------------------------
        TestDatas::$tokens[31]['meeple_state'] = 18;
        TestDatas::$tokens[32]['meeple_state'] = 17;
        TestDatas::$tokens[33]['meeple_state'] = 0;
        TestDatas::$tokens[34]['meeple_state'] = 1;
        TestDatas::$tokens[35]['meeple_state'] = 6;
        TestDatas::$tokens[36]['meeple_state'] = 5;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "newClanMarker--123",
            "deliver--123", 
            "newClanMarker--123",
            "deliver--123", 
            "newClanMarker--123",
            "scoreInfluence--123",//region1 
            "scoreElder--123", 
            "scoreInfluence--123",
            "scoreInfluence--123", //region4 
            "scoreElder--123", 
            "scoreInfluence--123",
            "scoreInfluence--123", //region6
            "scoreElder--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "eliminateByScore-1",
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 8,
                    REGION_2 => 6,
                    REGION_3 => 0,
                    REGION_4 => 5,
                    REGION_5 => 3,
                    REGION_6 => 7,
                ], 
                SCORING_DELIVERED => 9, //3 deliveries
                SCORING_CUSTOMERS => 20,//8+5+7
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        //Test new clan markers
        assertSame(45, TestDatas::$lastInsertedId);
        assertSame(TestDatas::$tokens[43], ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER."6",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ] );
        assertSame(TestDatas::$tokens[44], ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER."1",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ] );
        assertSame(TestDatas::$tokens[45], ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => AUTOMA_PLAYER_ID, ] );
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(58, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_AutomaLevel1_Merchants(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_MERCHANT_5;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreMerchants--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, //2 deliveries
                SCORING_CUSTOMERS => 2.0,//2* 1 for 5 Koku * 1 (5 Koku)
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(7, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_AutomaLevel2_Merchants(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_MERCHANT_5;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreMerchants--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, //2 deliveries
                SCORING_CUSTOMERS => 4.0,//2* 1 for 5 Koku * 2 (10 Koku)
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(9, Globals::getAutomaScore());
    }
    public function test_computeScoring_AutomaLevel3_Merchants(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_3);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_MERCHANT_5;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreMerchants--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, //2 deliveries
                SCORING_CUSTOMERS => 6.0,//2* 1 for 5 Koku * 3 (15 Koku)
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(11, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_AutomaLevel4_Merchants(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_4);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_MERCHANT_5;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreMerchants--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, //2 deliveries
                SCORING_CUSTOMERS => 8.0,//2* 1 for 5 Koku * 4 (20 Koku)
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(13, Globals::getAutomaScore());
    }
    public function test_computeScoring_AutomaLevel5_Merchants(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_5);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_MERCHANT_5;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreMerchants--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, //2 deliveries
                SCORING_CUSTOMERS => 10.0,//2* 1 for 5 Koku * 5 (25 Koku)
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(15, Globals::getAutomaScore());
    }

    
    public function test_computeScoring_AutomaLevel1_Noble1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_5);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_1;
        TestDatas::$tiles[41]['type'] = 8;
        TestDatas::$tokens[41]['player_id'] = AUTOMA_PLAYER_ID;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreCustomer--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, //1 deliveries
                SCORING_CUSTOMERS => 2,//2* 1 market
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(4, Globals::getAutomaScore());
    }
    public function test_computeScoring_AutomaLevel1_Noble5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_5);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$tiles[41]['type'] = 2;
        TestDatas::$tokens[41]['player_id'] = AUTOMA_PLAYER_ID;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['type'] = 9;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        //add a 3rd building tile  + marker
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['type'] = 25;
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['meeple_id'] = 44;
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreCustomer--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, //1 deliveries
                SCORING_CUSTOMERS => 3,//3* 1 type of building
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(5, Globals::getAutomaScore());
    }
    public function test_computeScoring_AutomaLevel1_Noble6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_5);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_6;
        TestDatas::$tiles[41]['type'] = 2;
        TestDatas::$tokens[41]['player_id'] = AUTOMA_PLAYER_ID;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['type'] = 9;
        TestDatas::$tiles[43]['tile_state'] = 12;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        //add a 3rd building tile  + marker
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['type'] = 25;
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['meeple_id'] = 44;
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreCustomer--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, //1 deliveries
                SCORING_CUSTOMERS => 2,//2* 1 region of building
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(4, Globals::getAutomaScore());
    }

    public function test_computeScoring_AutomaLevel2_Smugglers(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_SMUGGLER_1;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['type'] = CARD_SMUGGLER_2;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_SMUGGLER_3;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreCustomer--123", 
            "scoreCustomer--123", 
            "scoreCustomer--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 9, //3 deliveries
                SCORING_CUSTOMERS => 5.0,//2+1+2
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(14, Globals::getAutomaScore());
    }

    public function test_computeScoring_AutomaLevel1_Shins(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_6;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_3;
        TestDatas::$cards[1]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[1]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[1]['type'] = CARD_SHINDOSHI_4;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreMultiCustomers--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 14, //4 deliveries
                SCORING_CUSTOMERS => 4.0,//4* 1 Favor
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(18, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_AutomaLevel2_Shins(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_6;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_3;
        TestDatas::$cards[1]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[1]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[1]['type'] = CARD_SHINDOSHI_4;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreMultiCustomers--123", 
            "eliminateByScore-1",
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 14, //4 deliveries
                SCORING_CUSTOMERS => 8.0,//4* 1 Favor * Level 2
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(22, Globals::getAutomaScore());
    }
    public function test_computeScoring_AutomaLevel3_Shins(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_3);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_6;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_3;
        TestDatas::$cards[1]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[1]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[1]['type'] = CARD_SHINDOSHI_4;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreMultiCustomers--123", 
            "eliminateByScore-1",
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 14, //4 deliveries
                SCORING_CUSTOMERS => 12.0,//4* 1 Favor * Level 3
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(26, Globals::getAutomaScore());
    }
    public function test_computeScoring_AutomaLevel4_Shins(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_4);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_6;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_3;
        TestDatas::$cards[1]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[1]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[1]['type'] = CARD_SHINDOSHI_4;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreMultiCustomers--123", 
            "eliminateByScore-1",
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 14, //4 deliveries
                SCORING_CUSTOMERS => 16.0,//4* 1 Favor * Level 4
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(30, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_AutomaLevel5_Shins(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_5);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_6;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[13]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_3;
        TestDatas::$cards[1]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[1]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[1]['type'] = CARD_SHINDOSHI_4;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreMultiCustomers--123", 
            "eliminateByScore-1",
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 14, //4 deliveries
                SCORING_CUSTOMERS => 20.0,//4* 1 Favor * Level 5
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(34, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_AutomaLevel2_Traders(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_3;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[12]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[12]['type'] = CARD_TRADER_3;
        TestDatas::$tiles[41]['tile_state'] = 11;//in region 3
        TestDatas::$tokens[41]['player_id'] = AUTOMA_PLAYER_ID;
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreMerchants--123", 
            "scoreCustomer--123", 
            "eliminateByScore-2",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, //2 deliveries
                SCORING_CUSTOMERS=> 8.0,//2 + 6 (2*2 for cards in region 3 + 2 for 1 building in region 3 )
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(13, Globals::getAutomaScore());
        assertSame(13, TestDatas::$stats['table'][14]);
    }
    
    
    public function test_computeScoring_AutomaLevel1_PlayersWIN(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_1;
        TestDatas::$players[2]['resources'] = '{"1":0,"2":6,"3":4,"4":5,"5":1,"6":17}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_3;
        TestDatas::$tiles[41]['type'] = 8;
        TestDatas::$tokens[41]['player_id'] = AUTOMA_PLAYER_ID;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreMultiCustomers-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreCustomer--123", 
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 1.0,//1*1
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, //1 deliveries
                SCORING_CUSTOMERS => 2,//2* 1 market
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(5, TestDatas::$players[1]['player_score']);//Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(5,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(4, Globals::getAutomaScore());
        assertSame(4, TestDatas::$stats['table'][14]);
    }
    public function test_computeScoring_AutomaLevel1_PlayersTie_Defeat(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(15);
        TestDatas::$players[1]['player_score'] = 15;
        TestDatas::$players[2]['player_score'] = 15;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":5,"5":5,"6":5}';
        TestDatas::$players[2]['resources'] = '{"1":0,"2":0,"3":0,"4":5,"5":2,"6":17}';
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123",
            "eliminateByScore-1",
            "eliminateByScore-2", 
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 15, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 15, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 15, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);//REDUCED
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(15, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_Scenario1_Completed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_1;
        TestDatas::$players[2]['resources'] = '{"1":0,"2":6,"3":4,"4":5,"5":1,"6":17}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_3;
        TestDatas::$tiles[41]['type'] = 8;
        TestDatas::$tokens[41]['player_id'] = 1;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreMultiCustomers-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioCompleted-1",
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => true,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 1.0,//1*1
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, //1 deliveries
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(5, TestDatas::$players[1]['player_score']);//Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(5,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(2, Globals::getAutomaScore());
        assertSame(2, TestDatas::$stats['table'][14]);
    }
    
    public function test_computeScoring_Scenario1_Failed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED_HIDDEN;
        TestDatas::$cards[11]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_1;
        TestDatas::$players[2]['resources'] = '{"1":0,"2":6,"3":4,"4":5,"5":1,"6":17}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_3;
        TestDatas::$tiles[41]['type'] = 8;
        TestDatas::$tokens[41]['player_id'] = AUTOMA_PLAYER_ID;
        //add a second building tile  + marker
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        $expectedNotifs = [
            "computeFinalScore",
            "deliver--123", 
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreMultiCustomers-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scoreCustomer--123", 
            "scenarioFailed-1",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => false,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 1.0,//1*1
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, //1 deliveries
                SCORING_CUSTOMERS => 2,//2* 1 market
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(4, Globals::getAutomaScore());
        assertSame(4, TestDatas::$stats['table'][14]);
    }
    
    public function test_computeScoring_ScenarioMantis1_Completed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::MANTIS_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioCompleted-1",
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => true,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(2, TestDatas::$players[1]['player_score']);//Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(2,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(0, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_ScenarioMantis1_Failed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::MANTIS_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$tokens[26]['player_id'] = ROGUE_PLAYER_ID;
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioFailed-1",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => false,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(0, Globals::getAutomaScore());
    }
    
    
    public function test_computeScoring_ScenarioCrane1_Completed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":0}'];
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioCompleted-1",
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => true,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(2, TestDatas::$players[1]['player_score']);//Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(2,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(0, Globals::getAutomaScore());
    }
    public function test_computeScoring_ScenarioCrane1_Failed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":1}'];
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioFailed-1",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => false,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(0, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_ScenarioScorpion1_Completed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::SCORPION_1->value,   'subtype' => CARD_TYPE_SCENARIO,  ];
        //Test with remaining targets in regions 4,5,6 :
        for($k = 4; $k <= 6; $k++ ) TestDatas::$tokens[43+$k] = ['result_associative_index' => 43 + $k, 'meeple_id' => 43 +$k, 'meeple_state' => 8+$k, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE."$k",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => SCORPION_ENEMY_ID,  ];
        TestDatas::$tokens[1]['meeple_state'] = 10;
        TestDatas::$tokens[2]['meeple_state'] = 10;
        TestDatas::$tokens[3]['meeple_state'] = 16;
        TestDatas::$tokens[4]['meeple_state'] = 10;
        TestDatas::$tokens[5]['meeple_state'] = 10;
        TestDatas::$tokens[6]['meeple_state'] = 10;
        TestDatas::$tokens[11]['meeple_state'] = 9;
        TestDatas::$tokens[12]['meeple_state'] = 11;
        TestDatas::$tokens[13]['meeple_state'] = 9;
        TestDatas::$tokens[14]['meeple_state'] = 9;
        TestDatas::$tokens[15]['meeple_state'] = 9;
        TestDatas::$tokens[16]['meeple_state'] = 9;
        TestDatas::$tokens[31]['meeple_state'] = 8;
        TestDatas::$tokens[32]['meeple_state'] = 8;
        TestDatas::$tokens[33]['meeple_state'] = 8;
        TestDatas::$tokens[34]['meeple_state'] = 8;
        TestDatas::$tokens[35]['meeple_state'] = 8;
        TestDatas::$tokens[36]['meeple_state'] = 8;
        $expectedNotifs = [
            "computeFinalScore",
            "removeClanMarker-1",
            "removeClanMarker-1",
            "removeClanMarker-1",
            "scoreInfluence-1",
            "scoreInfluence-2",
            "scoreInfluence-1",
            "scoreInfluence-2",
            "scoreInfluence-1",
            "scoreInfluence-2",
            "scoreInfluence--123",
            "scoreInfluence-2",
            "scoreInfluence-2",
            "scoreInfluence--123",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioCompleted-1",
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 8,
                    REGION_2 => 3,
                    REGION_3 => 4,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => true,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 4,
                    REGION_2 => 6,
                    REGION_3 => 0,
                    REGION_4 => 5,
                    REGION_5 => 3,
                    REGION_6 => 7,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 2,
                    REGION_5 => 0,
                    REGION_6 => 3,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(27,  TestDatas::$players[1]['player_score']);//19 + 15 Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(27,  TestDatas::$players[2]['player_score']);//2 + 25 Reduce to lowest score
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(5, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_ScenarioPhoenix1_Completed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::PHOENIX_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$tokens[41]['meeple_location'] = MEEPLE_LOCATION_TILE."1";
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioCompleted-1",
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => true,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(2, TestDatas::$players[1]['player_score']);//Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(2,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(0, Globals::getAutomaScore());
        assertSame(0, TestDatas::$stats['table'][14]);
    }
    public function test_computeScoring_ScenarioPhoenix1_Failed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::PHOENIX_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioFailed-1",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => false,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(0, Globals::getAutomaScore());
        assertSame(0, TestDatas::$stats['table'][14]);
    }
    
    
    public function test_computeScoring_ScenarioLion1_Completed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::LION_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //assassins
        TestDatas::$tokens[43]= ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        TestDatas::$tokens[44]= ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioCompleted-1",
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => true,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(2, TestDatas::$players[1]['player_score']);//Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(2,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(0, Globals::getAutomaScore());
        assertSame(0, TestDatas::$stats['table'][14]);
    }
    public function test_computeScoring_ScenarioLion1_Failed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::LION_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //assassins
        TestDatas::$tokens[43]= ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        TestDatas::$tokens[44]= ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."4",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        TestDatas::$tokens[45]= ['result_associative_index' => 45, 'meeple_id' => 45, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_NEAR_SHORE."5",'type' => MEEPLE_TYPE_CLAN_MARKER,  'player_id' => LION_ENEMY_ID, ];
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioFailed-1",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => false,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(0, Globals::getAutomaScore());
        assertSame(0, TestDatas::$stats['table'][14]);
    }
    public function test_computeScoring_ScenarioDragon1_Completed_0Noble(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::DRAGON_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioCompleted-1",
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => true,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(2, TestDatas::$players[1]['player_score']);//Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(2,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(0, Globals::getAutomaScore());
        assertSame(0, TestDatas::$stats['table'][14]);
    }
    
    public function test_computeScoring_ScenarioDragon1_Completed_1Noble(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$players[2]['player_score'] = 10;
        TestDatas::$cards[1]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[1]['player_id'] = 1;
        TestDatas::$cards[1]['type'] = CARD_NOBLE_1;
        TestDatas::$cards[2]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[2]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[2]['type'] = CARD_NOBLE_2;
        TestDatas::$cards[3]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[3]['player_id'] = 2;
        TestDatas::$cards[3]['type'] = CARD_NOBLE_3;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::DRAGON_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioCompleted-1",
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => true,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 10, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(12, TestDatas::$players[1]['player_score']);//Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(12,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(2, Globals::getAutomaScore());
        assertSame(2, TestDatas::$stats['table'][14]);
    }
    
    public function test_computeScoring_ScenarioDragon1_Failed_1Noble(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setAutomaScore(0);
        TestDatas::$players[2]['player_score'] = 10;
        TestDatas::$cards[1]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[1]['player_id'] = 1;
        TestDatas::$cards[1]['type'] = CARD_NOBLE_1;
        TestDatas::$cards[2]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[2]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[2]['type'] = CARD_NOBLE_2;
        TestDatas::$cards[3]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[3]['player_id'] = AUTOMA_PLAYER_ID;
        TestDatas::$cards[3]['type'] = CARD_NOBLE_3;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['player_id'] = 2;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_4;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['player_id'] = 2;
        TestDatas::$cards[12]['type'] = CARD_NOBLE_5;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::DRAGON_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        $expectedNotifs = [
            "computeFinalScore",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreCustomer-2",
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioFailed-1",
            "teamLoose",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 2, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => false,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 10, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5, 
                SCORING_CUSTOMERS=> 1,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 5,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(SCORE_FAIL, TestDatas::$players[1]['player_score']);
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(SCORE_FAIL,  TestDatas::$players[2]['player_score']);
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);
        assertSame(5, Globals::getAutomaScore());
        assertSame(5, TestDatas::$stats['table'][14]);
    }
    
    public function test_computeScoring_ScenarioUnicorn1_Completed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"1":4,"2":4,"3":4}', ];
        //Test with remaining resources in regions 4,5,6 :
        TestDatas::$tokens[43 + 0] = ['result_associative_index' => 43 + 0, 'meeple_id' => 43 +0, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE.(15 + 5),'type' => MEEPLE_TYPE_RESOURCE_SILK, 'player_id' => null,   ];
        TestDatas::$tokens[43 + 1] = ['result_associative_index' => 43 + 1, 'meeple_id' => 43 +1, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE.(15 + 10),'type' => MEEPLE_TYPE_RESOURCE_POTTERY, 'player_id' => null,   ];
        TestDatas::$tokens[43 + 2] = ['result_associative_index' => 43 + 2, 'meeple_id' => 43 +2, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE.(15 + 15),'type' => MEEPLE_TYPE_RESOURCE_RICE, 'player_id' => null,   ];
        TestDatas::$tokens[1]['meeple_state'] = 10;
        TestDatas::$tokens[2]['meeple_state'] = 10;
        TestDatas::$tokens[3]['meeple_state'] = 16;
        TestDatas::$tokens[4]['meeple_state'] = 10;
        TestDatas::$tokens[5]['meeple_state'] = 10;
        TestDatas::$tokens[6]['meeple_state'] = 10;
        TestDatas::$tokens[11]['meeple_state'] = 9;
        TestDatas::$tokens[12]['meeple_state'] = 11;
        TestDatas::$tokens[13]['meeple_state'] = 9;
        TestDatas::$tokens[14]['meeple_state'] = 9;
        TestDatas::$tokens[15]['meeple_state'] = 9;
        TestDatas::$tokens[16]['meeple_state'] = 9;
        TestDatas::$tokens[31]['meeple_state'] = 8;
        TestDatas::$tokens[32]['meeple_state'] = 8;
        TestDatas::$tokens[33]['meeple_state'] = 8;
        TestDatas::$tokens[34]['meeple_state'] = 8;
        TestDatas::$tokens[35]['meeple_state'] = 8;
        TestDatas::$tokens[36]['meeple_state'] = 8;
        $expectedNotifs = [
            "computeFinalScore",
            "removeClanMarker-1",
            "removeClanMarker-1",
            "removeClanMarker-1",
            "scoreInfluence-1",
            "scoreInfluence-2",
            "scoreInfluence-1",
            "scoreInfluence-2",
            "scoreInfluence-1",
            "scoreInfluence-2",
            "scoreInfluence--123",
            "scoreInfluence-2",
            "scoreInfluence-2",
            "scoreInfluence--123",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "scenarioCompleted-1",
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 8,
                    REGION_2 => 3,
                    REGION_3 => 4,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_COMPLETE_SCENARIO => true,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 4,
                    REGION_2 => 6,
                    REGION_3 => 0,
                    REGION_4 => 5,
                    REGION_5 => 3,
                    REGION_6 => 7,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 2,
                    REGION_5 => 0,
                    REGION_6 => 3,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(27,  TestDatas::$players[1]['player_score']);//19 + 15 Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(27,  TestDatas::$players[2]['player_score']);//2 + 25 Reduce to lowest score
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(5, Globals::getAutomaScore());
    }

    // -------------------------------------------------
    
    public function test_computeScoring_CityCard_Summons_0(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[221]['player_id'] = 1;
        TestDatas::$cards[221]['card_state'] = 1;
        TestDatas::$cards[221]['type'] = CITY_CARD_TYPE::SUMMONS->value;
        TestDatas::$players[2]['player_score'] = 29;
        $expectedNotifs = [
            "computeFinalScore",
            "revealCityCard-1",
            "scoreCityCard-1",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 0,//0*2
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 29, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
                SCORING_CITY_CARD => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(19,  TestDatas::$players[1]['player_score']);//19 + 0 Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(19,  TestDatas::$players[2]['player_score']);//+0 Reduce to lowest score
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(0, Globals::getAutomaScore());
    }
    public function test_computeScoring_CityCard_Summons_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::$tiles[41]['type'] = 14; //BUILDING_TYPE_MANOR
        TestDatas::$tiles[41]['tile_state'] = 1;
        TestDatas::$tokens[21]['meeple_state'] = 1;
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[221]['player_id'] = 1;
        TestDatas::$cards[221]['card_state'] = 1;
        TestDatas::$cards[221]['type'] = CITY_CARD_TYPE::SUMMONS->value;
        TestDatas::$players[2]['player_score'] = 29;
        $expectedNotifs = [
            "computeFinalScore",
            "revealCityCard-1",
            "scoreCityCard-1",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 2,//1*2
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 29, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
                SCORING_CITY_CARD => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(21,  TestDatas::$players[1]['player_score']);//19 + 2 Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(21,  TestDatas::$players[2]['player_score']);//+0 Reduce to lowest score
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(0, Globals::getAutomaScore());
    }
    public function test_computeScoring_CityCard_FullStorage_0(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::$players[1]['resources'] = '{"1":5,"2":5,"3":5,"4":6,"5":6,"6":25}';
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[221]['player_id'] = 1;
        TestDatas::$cards[221]['card_state'] = 1;
        TestDatas::$cards[221]['type'] = CITY_CARD_TYPE::FULL_STOR->value;
        TestDatas::$players[2]['player_score'] = 29;
        $expectedNotifs = [
            "computeFinalScore",
            "revealCityCard-1",
            "scoreCityCard-1",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 0,//0*5
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 29, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
                SCORING_CITY_CARD => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(19,  TestDatas::$players[1]['player_score']);//19 + 0 Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(19,  TestDatas::$players[2]['player_score']);//+0 Reduce to lowest score
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(0, Globals::getAutomaScore());
    }
    public function test_computeScoring_CityCard_FullStorage_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::$players[1]['resources'] = '{"1":3,"2":6,"3":5,"4":6,"5":6,"6":25}';
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[221]['player_id'] = 1;
        TestDatas::$cards[221]['card_state'] = 1;
        TestDatas::$cards[221]['type'] = CITY_CARD_TYPE::FULL_STOR->value;
        TestDatas::$players[2]['player_score'] = 29;
        $expectedNotifs = [
            "computeFinalScore",
            "revealCityCard-1",
            "scoreCityCard-1",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 5,//1*5
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 29, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
                SCORING_CITY_CARD => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(24,  TestDatas::$players[1]['player_score']);//19 + 5 Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(24,  TestDatas::$players[2]['player_score']);//+0 Reduce to lowest score
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(0, Globals::getAutomaScore());
    }
    
    public function test_computeScoring_CityCard_FullStorage_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::$players[1]['resources'] = '{"1":6,"2":6,"3":5,"4":6,"5":6,"6":25}';
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[221]['player_id'] = 1;
        TestDatas::$cards[221]['card_state'] = 1;
        TestDatas::$cards[221]['type'] = CITY_CARD_TYPE::FULL_STOR->value;
        TestDatas::$players[2]['player_score'] = 29;
        $expectedNotifs = [
            "computeFinalScore",
            "revealCityCard-1",
            "scoreCityCard-1",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 10,//2*5
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 29, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
                SCORING_CITY_CARD => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(29,  TestDatas::$players[1]['player_score']);//19 + 10 Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(29,  TestDatas::$players[2]['player_score']);//+0 Reduce to lowest score
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(0, Globals::getAutomaScore());
    }
    public function test_computeScoring_CityCard_FullStorage_3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::$players[1]['resources'] = '{"1":6,"2":6,"3":6,"4":6,"5":6,"6":25}';
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[221]['player_id'] = 1;
        TestDatas::$cards[221]['card_state'] = 1;
        TestDatas::$cards[221]['type'] = CITY_CARD_TYPE::FULL_STOR->value;
        TestDatas::$players[2]['player_score'] = 34;
        $expectedNotifs = [
            "computeFinalScore",
            "revealCityCard-1",
            "scoreCityCard-1",
            "scoreDeliveries-1", 
            "scoreDeliveries-2", 
            "scoreDeliveries--123", 
            "endResourcesForCustomers--123", 
            "teamWin",
        ];
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 15,//3*5
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 34, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
                SCORING_CITY_CARD => 0,
            ],
            AUTOMA_PLAYER_ID => [ 
                SCORING_INGAME => 0, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0,
                SCORING_CUSTOMERS => 0,
                SCORING_CITY_CARD => 0,
            ],
        ];

        $game->stScoring();
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(34,  TestDatas::$players[1]['player_score']);//19 + 15 Reduce to lowest score
        assertSame(0,  TestDatas::$players[1]['player_score_aux']);
        assertSame(34,  TestDatas::$players[2]['player_score']);//+0 Reduce to lowest score
        assertSame(0,  TestDatas::$players[2]['player_score_aux']);//REDUCED
        assertSame(0, Globals::getAutomaScore());
    }
    // -------------------------------------------------
    
    public function test_compute_TieBreaker(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_END_SCORING;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":0,"3":1,"4":5,"5":3,"6":5}';
        TestDatas::$players[2]['resources'] = '{"1":0,"2":6,"3":4,"4":5,"5":4,"6":17}';
        $expectedScoring = [
            1 => [ // PLAYER 1
                SCORING_INGAME => 19, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
            2 => [ // PLAYER 2
                SCORING_INGAME => 2, 
                SCORING_INFLUENCE => [
                    REGION_1 => 0,
                    REGION_2 => 0,
                    REGION_3 => 0,
                    REGION_4 => 0,
                    REGION_5 => 0,
                    REGION_6 => 0,
                ], 
                SCORING_DELIVERED => 0, 
                SCORING_CUSTOMERS=> 0,
            ],
        ];

        $game->stScoring();
        
        assertSame(ST_PRE_END_OF_GAME, GamestateMachine::$test_current_state);
        $endScoringDatas = Globals::getEndScoring();
        assertSame($expectedScoring, $endScoringDatas);
        assertSame(19, TestDatas::$players[1]['player_score']);
        assertSame(3, TestDatas::$players[1]['player_score_aux']);
        assertSame(2, TestDatas::$players[2]['player_score']);
        assertSame(4, TestDatas::$players[2]['player_score_aux']);
    }
    // -------------------------------------------------
}