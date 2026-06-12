<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Models\MAIN_ACTION;
use ROG\Models\ScenarioType;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

final class SailTest extends TestCase
{

    // -------------------------------------------------
    // -------------------------------------------------
    
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        Globals::setChoices(0);
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        $expectedArgs = [
            'spaces' => [
                //['shipId' => positions]
                21 => [6],
                22 => [1],//14 +1 = 1
            ],
            'canSkipOwner' => null,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSail();
        
        assertSame($expectedArgs, $args);
        assertSame(null, Globals::getTurnMainActionDone());
    }

    public function test_Args_Noble5_ship1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        Globals::setChoices(0);
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 2;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$tokens[21]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;
        $expectedArgs = [
            'spaces' => [
                //['shipId' => positions]
                21 => [7, 8,6],
                22 => [
                    2, //14 +2 = 2
                ],
            ],
            'canSkipOwner' => null,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSail();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Noble5_ship2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        Globals::setChoices(0);
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$tokens[22]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;
        $expectedArgs = [
            'spaces' => [
                //['shipId' => positions]
                21 => [6],
                22 => [
                    1, //14 +1 = 1
                    2, //14 +1 +1 = 2
                    6, //14 +1 +6 = 6
                ],
            ],
            'canSkipOwner' => null,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSail();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_Shin1_upriver_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        Globals::setChoices(0);
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 2;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_1;
        TestDatas::$tokens[21]['meeple_state'] = 5 ;
        TestDatas::$tokens[21]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $expectedArgs = [
            'spaces' => [
                //['shipId' => positions]
                21 => [ 
                        7, //down
                        'upriver' => [
                            ['space' => 3, 'markerId' => 43], // up
                            ['space' => 2, 'markerId' => 43], // up Noble +1
                            ['space' => 4, 'markerId' => 43], // up Noble -1
                        ],
                        8, // down Noble +1
                        6, // down Noble -1
                       
                    ],
                22 => [
                    2, //14 +2 = 2
                    'upriver' => [
                        ['space' => 12, 'markerId' => 43],// 14 -2 = 12
                    ]
                ],
            ],
            'canSkipOwner' => null,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSail();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_Shin1_upriver_Pass_SomeUnreachable(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        Globals::setChoices(0);
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_1;
        TestDatas::$tokens[21]['meeple_state'] = 2 ;
        TestDatas::$tokens[21]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;
        TestDatas::$tokens[22]['meeple_state'] = 1 ;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $expectedArgs = [
            'spaces' => [
                //['shipId' => positions]
                21 => [ 
                        3, //down
                        'upriver' => [
                            ['space' => 1, 'markerId' => 43], // up
                            //0, // up Noble +1         unreachable
                            //-4, // up Noble -1 (=6)   unreachable
                        ],
                        4, // down Noble +1
                        8, // down Noble -1 (=6)
                       
                    ],
                22 => [
                    2, //1+ 1 = 2
                    //'upriver' => [
                    //    0,//1-1 =0 unreachable
                    //]
                ],
            ],
            'canSkipOwner' => null,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSail();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_Shin1_upriver_KO_clanMarkerSpent(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        Globals::setChoices(0);
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 2;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_1;
        TestDatas::$tokens[21]['state'] = 5 ;
        TestDatas::$tokens[21]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> 'discard','type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $expectedArgs = [
            'spaces' => [
                //['shipId' => positions]
                21 => [7, 8,6],
                22 => [
                    2, //14 +2 = 2
                ],
            ],
            'canSkipOwner' => null,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSail();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_Shin6_CanSkip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        Globals::setChoices(0);
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_1;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_6;
        TestDatas::$tokens[21]['meeple_state'] = 7;
        TestDatas::$tokens[21]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $expectedArgs = [
            'spaces' => [
                //['shipId' => positions]
                21 => [ 
                        8, //down
                        'upriver' => [
                            ['space' => 6, 'markerId' => 43], // up
                            ['space' => 5, 'markerId' => 43], // up Noble +1
                            ['space' => 1, 'markerId' => 43], // up Noble -1 (=6)
                        ],
                        9, // down Noble +1
                        13, // down Noble -1 (=+6)
                       
                    ],
                22 => [
                    1, //14 +1 = 1
                    'upriver' => [
                        ['space' => 13, 'markerId' => 43],// 14 -1 = 13
                    ]
                ],
            ],
            'canSkipOwner' => 44,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSail();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_Shin6_CannotSkip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        Globals::setChoices(0);
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_1;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_6;
        TestDatas::$tokens[21]['meeple_state'] = 7;
        TestDatas::$tokens[21]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        //if marker is not on card we cannot use it
        //TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $expectedArgs = [
            'spaces' => [
                //['shipId' => positions]
                21 => [ 
                        8, //down
                        'upriver' => [
                            ['space' => 6, 'markerId' => 43], // up
                            ['space' => 5, 'markerId' => 43], // up Noble +1
                            ['space' => 1, 'markerId' => 43], // up Noble -1 (=6)
                        ],
                        9, // down Noble +1
                        13, // down Noble -1 (=+6)
                       
                    ],
                22 => [
                    1, //14 +1 = 1
                    'upriver' => [
                        ['space' => 13, 'markerId' => 43],// 14 -1 = 13
                    ]
                ],
            ],
            'canSkipOwner' => null,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argSail();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
    public function test_ActionSail_Pass_Ship1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        $shipId = 21;
        $riverSpace = 6;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(4, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*4
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsSail']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
        assertSame(MAIN_ACTION::SAIL->value, Globals::getTurnMainActionDone());
    }
    
    public function test_ActionSail_Pass_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(TILE_LOCATION_DISCARD, TestDatas::$tiles[34]['tile_location']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(7, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*3 + 1 as owner reward + 3 as visitor reward
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsSail']);
        //Test discard last building in row :
        assertSame(TILE_LOCATION_DISCARD, TestDatas::$tiles[34]['tile_location']);
    }
    
    public function test_ActionSail_Pass_Merchant1_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_1;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        //check influences : +1 in owned region
        assertSame(1, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
    }
    
    public function test_ActionSail_Pass_Merchant2_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_2;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(19+1, TestDatas::$players[TestDatas::$test_activePlayerId]['player_score']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ActionSail_Pass_Merchant3_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_3;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD, BONUS_TYPE_CHOICE]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ActionSail_Pass_Merchant4_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_4;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD, BONUS_TYPE_SELL_GOODS]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ActionSail_Pass_Merchant5_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_5;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD, ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Merchant6_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MERCHANT_6;
        $shipId = 22;
        $riverSpace = 1;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        $expectedBonuses = json_encode([BONUS_TYPE_MONEY_OR_GOOD, ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(3 + 7, $resources[RESOURCE_TYPE_MONEY]);//3 + sail base( EMPTY_SPACE_REWARD*3 + 1 as owner reward + 3 as visitor reward )
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Monk1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MONK_1;
        TestDatas::$tiles[41]['tile_state'] = 11;
        TestDatas::$tiles[42]['tile_state'] = 12;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['meeple_state'] = 2;
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        $shipId = 21;
        $riverSpace = 6;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(10, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*2 + 1*2 as owner reward + 3 as Own visitor reward + 3 as Opponent visitor reward
        assertSame(19+0,TestDatas::$players[1]['player_score']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsSail']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    public function test_ActionSail_Pass_Monk2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_MONK_2;
        TestDatas::$tiles[41]['tile_state'] = 11;
        TestDatas::$tiles[42]['tile_state'] = 12;
        TestDatas::$tiles[42]['type'] = 9;
        TestDatas::$tokens[43] = TestDatas::$tokens[42];
        TestDatas::$tokens[43]['meeple_state'] = 2;
        TestDatas::$tokens[43]['meeple_id'] = 43;
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['player_id'] = 1;
        $shipId = 21;
        $riverSpace = 6;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(6, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*2 + 1 as owner reward + 3 as Own visitor reward 
        assertSame(1, $resources[RESOURCE_TYPE_SUN]);//+1 as owner reward on oppponent tile
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);//1 as visitor reward on oppponent tile
        assertSame(20,TestDatas::$players[1]['player_score']);//+1 as owner reward on Opponent tile
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsSail']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    public function test_ActionSail_Pass_Noble1_WithStandardShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_1;
        //market with good:
        TestDatas::$tiles[40] = ['result_associative_index' => 40,'tile_id' => 40, 'tile_location' => TILE_LOCATION_BUILDING_SHORE, 'tile_state' => 3,  'type' => 8, 'subtype' => TILE_TYPE_BUILDING, ];
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 1;
        $riverSpace = 2;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        $expectedBonuses = json_encode([ ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble1_WithRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_1;
        //market with good:
        TestDatas::$tiles[40] = ['result_associative_index' => 40,'tile_id' => 40, 'tile_location' => TILE_LOCATION_BUILDING_SHORE, 'tile_state' => 3,  'type' => 8, 'subtype' => TILE_TYPE_BUILDING, ];
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 1;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;
        $riverSpace = 2;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        $expectedBonuses = json_encode([BONUS_TYPE_CHOICE, ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble2_WithRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_2;
        $shipId = 21;
        $riverSpace = 6;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        $expectedBonuses = json_encode([ ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(8, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*4 *2
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble3_WithRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_3;
        $shipId = 21;
        $riverSpace = 5;
        TestDatas::$tokens[$shipId]['meeple_state'] = 4;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(1, TestDatas::$tokens[2]['meeple_state']);
        assertSame(1, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
        $expectedBonuses = json_encode([ ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble4_WithRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_4;
        $shipId = 21;
        $riverSpace = 2;
        TestDatas::$tokens[$shipId]['meeple_state'] = 1;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame(19+1,TestDatas::$players[1]['player_score']);//+NB_POINTS_NOBLE_4
        $expectedBonuses = json_encode([ ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble6_WithRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_6;
        $shipId = 21;
        $riverSpace = 2;
        TestDatas::$tokens[$shipId]['meeple_state'] = 1;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame(19+1,TestDatas::$players[1]['player_score']);//+NB_POINTS_NOBLE_6
        $expectedBonuses = json_encode([ ]);
        assertSame($expectedBonuses, TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble5_WithShin1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_1;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $shipId = 21;
        $riverSpace = 1;//Move UPRIVER 
        TestDatas::$tokens[$shipId]['meeple_state'] = 7;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        assertFalse( array_key_exists(43,TestDatas::$tokens));//removed clan marker
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble5_WithShin2_dontSkipRewards_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_1;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_6;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        TestDatas::$tiles[42]['tile_state'] = 3;//set opponent building next to this river space
        $shipId = 21;
        $riverSpace = 1;//Move UPRIVER thanks to Shin 1
        $skipORewards = false;
        TestDatas::$tokens[$shipId]['meeple_state'] = 7;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $game->actSailSelect($shipId,$riverSpace,999999,$skipORewards);
        
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        assertFalse( array_key_exists(43,TestDatas::$tokens));//removed clan marker
        assertSame( ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ], TestDatas::$tokens[44]);//NOT removed clan marker
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP1[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SUN ]);
        assertSame(9, $resourcesP1[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*2 + 1 as owner reward + 3*2 as visitor reward
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        $resourcesP2 = json_decode(TestDatas::$players[2]['resources'], true);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP2[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_MONEY]);
        assertSame(3, TestDatas::$players[2]['player_score']);//+1
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble5_WithShin2_skipRewards_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_1;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_6;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        TestDatas::$tiles[42]['tile_state'] = 3;//set opponent building next to this river space
        $shipId = 21;
        $riverSpace = 1;//Move UPRIVER thanks to Shin 1
        $skipORewards = true;
        TestDatas::$tokens[$shipId]['meeple_state'] = 7;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $game->actSailSelect($shipId,$riverSpace,999999,$skipORewards);
        
        assertSame($riverSpace, TestDatas::$tokens[$shipId]['meeple_state']);
        assertFalse( array_key_exists(43,TestDatas::$tokens));//removed clan marker
        assertFalse( array_key_exists(44,TestDatas::$tokens));//removed clan marker
        $resourcesP1 = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP1[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP1[RESOURCE_TYPE_SUN ]);
        assertSame(9, $resourcesP1[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*2 + 1 as owner reward + 3*2 as visitor reward
        assertSame(19, TestDatas::$players[1]['player_score']);//+0
        $resourcesP2 = json_decode(TestDatas::$players[2]['resources'], true);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SILK ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_RICE ]);
        assertSame(3, $resourcesP2[RESOURCE_TYPE_MOON]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_SUN ]);
        assertSame(0, $resourcesP2[RESOURCE_TYPE_MONEY]);
        assertSame(2, TestDatas::$players[2]['player_score']);//+0
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_Noble5_WithShin2_skipRewards_KO(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_NOBLE_5;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_1;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_6;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        //TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $shipId = 21;
        $riverSpace = 1;//Move UPRIVER thanks to Shin 1
        $skipORewards = true;
        TestDatas::$tokens[$shipId]['meeple_state'] = 7;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL ;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot skip owner rewards now");
        $game->actSailSelect($shipId,$riverSpace,999999,$skipORewards);
    }
    
    public function test_ActionSail_Pass_Trader6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 6;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_TRADER_6;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 6;
        $riverSpace = 12;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(4, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*4
        assertSame(19+1,TestDatas::$players[1]['player_score']);//+1
        assertSame(json_encode([BONUS_TYPE_DRAW]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }

    public function test_ActionSail_Pass_IronCrane(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_IRON_CRANE;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 1;
        $riverSpace = 2;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame(19+2,TestDatas::$players[1]['player_score']);//+NB_POINTS_IRON_CRANE
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_MagnateOfSandRoad_0ShipInSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_MAGNATE_SAND_ROAD;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 10;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL;
        TestDatas::$tokens[22]['meeple_state'] = 10;
        TestDatas::$tokens[25] = TestDatas::$tokens[22];
        TestDatas::$tokens[25]['meeple_id'] = 25;
        TestDatas::$tokens[25]['result_associative_index'] = 25;
        $riverSpace = 11;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame(19,TestDatas::$players[1]['player_score']);//+0
    }
    public function test_ActionSail_Pass_MagnateOfSandRoad_1ShipInSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_MAGNATE_SAND_ROAD;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 9;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL;
        TestDatas::$tokens[22]['meeple_state'] = 10;
        TestDatas::$tokens[25] = TestDatas::$tokens[22];
        TestDatas::$tokens[25]['meeple_id'] = 25;
        TestDatas::$tokens[25]['result_associative_index'] = 25;
        TestDatas::$tokens[25]['meeple_state'] = 11;
        $riverSpace = 10;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame(21,TestDatas::$players[1]['player_score']);//+1*2
    }
    public function test_ActionSail_Pass_MagnateOfSandRoad_2ShipsInSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_MAGNATE_SAND_ROAD;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 9;
        TestDatas::$tokens[$shipId]['type'] = MEEPLE_TYPE_SHIP_ROYAL;
        TestDatas::$tokens[22]['meeple_state'] = 10;
        TestDatas::$tokens[25] = TestDatas::$tokens[22];
        TestDatas::$tokens[25]['meeple_id'] = 25;
        TestDatas::$tokens[25]['result_associative_index'] = 25;
        $riverSpace = 10;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame(23,TestDatas::$players[1]['player_score']);//+2*2
    }
    
    public function test_ActionSail_Pass_MistressOfWinds_2ShipsInSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_MISTRESS_OF_WINDS;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 9;
        TestDatas::$tokens[23]['meeple_state'] = 10;
        TestDatas::$tokens[24]['meeple_state'] = 10;
        $riverSpace = 10;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame(json_encode(['datas' => [BONUS_TYPE_PAY_SHIPS=>[ 1 => ['ship'=>21,'position'=>10,'bonusQuantity'=>1,]]]]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ActionSail_Pass_MistressOfWinds_0OpponentsInSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_MISTRESS_OF_WINDS;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 9;
        TestDatas::$tokens[22]['meeple_state'] = 10;
        $riverSpace = 10;

        $game->actSailSelect($shipId,$riverSpace,999999);
        
        assertSame(json_encode([]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_ImperialEnvoy_Region2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_IMPERIAL_ENVOY;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 2;
        $riverSpace = 3;
        //Set an imperial market in space 6 :
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['tile_state'] = 6;

        $game->actSailSelect($shipId,$riverSpace,999999);

        //check influences : +2 in region 2
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(2, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
    }
    
    public function test_ActionSail_Pass_ImperialEnvoy_Region4(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_IMPERIAL_ENVOY;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 8;
        $riverSpace = 9;
        //Set an imperial market in space 6 :
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['tile_state'] = 17;

        $game->actSailSelect($shipId,$riverSpace,999999);

        //check influences : +2 in region 4
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(2, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
    }
    
    public function test_ActionSail_Pass_ImperialEnvoy_Region6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[101]['type'] = PATRON_IMPERIAL_ENVOY;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 13;
        $riverSpace = 14;
        //Set an imperial market in space 6 :
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['tile_state'] = 29;

        $game->actSailSelect($shipId,$riverSpace,999999);

        //check influences : +2 in region 6
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(2, TestDatas::$tokens[6]['meeple_state']);
    }
    
    public function test_ActionSail_Pass_WithoutImperialEnvoy_Region6(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 13;
        $riverSpace = 14;
        //Set an imperial market in space 6 :
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['tile_state'] = 29;

        $game->actSailSelect($shipId,$riverSpace,999999);

        //check influences
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(0, TestDatas::$tokens[6]['meeple_state']);
    }
    
    public function test_ActionSail_Pass_VisitAutomaBuildings(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 13;
        $riverSpace = 14;
        TestDatas::$tiles[42]['tile_state'] = 29;
        TestDatas::$tokens[42]['player_id'] = AUTOMA_PLAYER_ID;
        $expectedNotifs = [
            "sail-1",
            "checkVRewards",
            "giveResource-1", //space 26
            "giveResource-1", //space 28
            "giveResource-1", //space 29 [RESOURCE_TYPE_MONEY=>3],
            "giveResource-1", //space 30
            "checkORewards",
            "addPoints--123",//space 29 //type 6 [BONUS_TYPE_POINTS=>1],
        ];

        $game->actSailSelect($shipId,$riverSpace,999999);

        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(1, Globals::getAutomaScore());
        assertSame(1, TestDatas::$stats['table'][14]);
    }
    
    public function test_ActionSail_Pass_VisitAutomaBuildings_ScenarioCrab1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => 1,   'subtype' => CARD_TYPE_SCENARIO,];
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 13;
        $riverSpace = 14;
        TestDatas::$tiles[42]['tile_state'] = 29;
        TestDatas::$tokens[42]['player_id'] = AUTOMA_PLAYER_ID;
        $expectedNotifs = [
            "sail-1",
            "checkVRewards",
            "giveResource-1", //space 26
            "giveResource-1", //space 28
            "giveResource-1", //space 30
            "checkORewards",
            "addPoints--123",//space 29 //type 6 [BONUS_TYPE_POINTS=>1],
        ];

        $game->actSailSelect($shipId,$riverSpace,999999);

        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(1, Globals::getAutomaScore());
        assertSame(1, TestDatas::$stats['table'][14]);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(3, $resources[RESOURCE_TYPE_MONEY]);//EMPTY_SPACE_REWARD*3
    }
    
    public function test_ActionSail_Pass_ScenarioMantis1_moveRogue(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::MANTIS_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 13;
        $riverSpace = 14;
        TestDatas::$tokens[26]['player_id'] = ROGUE_PLAYER_ID;
        TestDatas::$tokens[26]['meeple_state'] = $riverSpace;
        $expectedNotifs = [
            "sail-1",
            "checkVRewards",
            "giveResource-1", //space 26
            "giveResource-1", //space 28
            "giveResource-1", //space 29 [RESOURCE_TYPE_SILK=>1,BONUS_TYPE_DRAW =>1]
            "addBonus-1",
            "giveResource-1", //space 30
            "checkORewards",
            "moveRogueShip-1",
        ];

        $game->actSailSelect($shipId,$riverSpace,999999);

        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //MOVED rogue ship
        assertSame($riverSpace-1, TestDatas::$tokens[26]['meeple_state']);
    }
    
    public function test_ActionSail_Pass_ScenarioMantis1_removeRogue(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_2);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::MANTIS_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        $shipId = 21;
        TestDatas::$tokens[$shipId]['meeple_state'] = 2;
        $riverSpace = 3;
        TestDatas::$tokens[26]['player_id'] = ROGUE_PLAYER_ID;
        TestDatas::$tokens[26]['meeple_state'] = $riverSpace;
        TestDatas::$tokens[22]['meeple_state'] = 2;
        TestDatas::$tiles[41]['tile_state'] = 1;
        $expectedNotifs = [
            "sail-1",
            "checkVRewards",
            "giveResource-1", //empty space 4
            "giveResource-1", //space 6 [RESOURCE_TYPE_POTTERY=>1,RESOURCE_TYPE_SUN=>1]
            "giveResource-1",
            "giveResource-1", //empty space 7
            "giveResource-1", //empty space 9
            "checkORewards",
            "moveRogueShip-1",
            "removeShip-1",
        ];

        $game->actSailSelect($shipId,$riverSpace,999999);

        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //REMOVED rogue ship
        assertFalse(array_key_exists(26,TestDatas::$tokens));
    }
    
    public function test_ActionSail_Pass_ScenarioCrane1_DontCompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":"30"}'];
        $shipId = 22;
        $riverSpace = 7;
        TestDatas::$tokens[$shipId]['meeple_state'] = 6;
        unset(TestDatas::$tiles[41]);
        $expectedNotifs = [
            "sail-1",
            "checkVRewards",
            "giveResource-1", //empty space 
            "giveResource-1", //empty space 
            "giveResource-1", //empty space 
            "giveResource-1", //empty space 
            "checkORewards",
        ];

        $game->actSailSelect($shipId,$riverSpace,999999);

        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $expectedBonuses = [];
        assertSame($expectedBonuses, json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_Pass_ScenarioCrane1_CompleteJourney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::CRANE_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'resources' => '{"6":"30"}'];
        $shipId = 22;
        $riverSpace = 1;
        TestDatas::$tokens[$shipId]['meeple_state'] = 14;
        unset(TestDatas::$tiles[41]);
        $expectedNotifs = [
            "sail-1",
            "reachRiverEnd-1",
            "addBonus-1",
            "discardBuildingRow",
            "addBonus-1",
            "checkVRewards",
            "giveResource-1", //empty space 1
            "giveResource-1", //empty space 2
            "giveResource-1", //empty space 3
            "giveResource-1", //empty space 4
            "checkORewards",
        ];

        $game->actSailSelect($shipId,$riverSpace,999999);

        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $expectedBonuses = [
            BONUS_TYPE_MONEY_OR_GOOD,
            'datas' => [
                BONUS_TYPE_MANAGE_DEBT => [
                    1 => ['card_id'=>301, 'bonusQuantity'=>1,],
                ],
            ],
        ];
        assertSame($expectedBonuses, json_decode(TestDatas::$players[1]['bonuses'], true));
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSail_KO_WrongShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        $shipId = 1;
        $riverSpace = 6;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Sail ship $shipId");
        $game->actSailSelect($shipId,$riverSpace,999999);
    }
    public function test_ActionSail_KO_WrongRiverSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_SAIL;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 1;
        $shipId = 21;
        $riverSpace = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Sail to $riverSpace");
        $game->actSailSelect($shipId,$riverSpace,999999);
    }
    // -------------------------------------------------
}