<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
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
        TestDatas::$tiles[43]['tile_state'] = 13;
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