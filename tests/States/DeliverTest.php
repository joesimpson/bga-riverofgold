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

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;

final class DeliverTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":5}';
        Globals::setChoices(0);
        $expectedArgs = [
            '_private' => [
                TestDatas::$test_activePlayerId => [
                    'c' => [ 13, ], // cards ids
                    'canReplaceGoods' => [],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argDeliver();
        
        assertSame($expectedArgs, $args);
        assertSame(null, Globals::getTurnMainActionDone());
    }
    public function test_Args_Shin2_CanReplaceGoods(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[1]['die_face'] = 3;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":5}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        Globals::setChoices(0);
        $expectedArgs = [
            '_private' => [
                1 => [
                    'c' => [ 13, ], // cards ids
                    'canReplaceGoods' => [
                        'marker' => 43,
                        'cards' => [11,13],
                        'cardsCosts' => [11 => [RESOURCE_TYPE_POTTERY => 2], 13=> [RESOURCE_TYPE_SILK => 2],],
                    ],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argDeliver();
        
        assertSame($expectedArgs, $args);
        assertSame(null, Globals::getTurnMainActionDone());
    }
    public function test_Args_Shin2_CanOnlyReplaceGoods(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[1]['die_face'] = 4;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":2,"3":0,"4":0,"5":0,"6":5}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        Globals::setChoices(0);
        $expectedArgs = [
            '_private' => [
                1 => [
                    'c' => [ ], // cards ids
                    'canReplaceGoods' => [
                        'marker' => 43,
                        'cards' => [11,13],
                        'cardsCosts' => [11 => [RESOURCE_TYPE_POTTERY => 2], 13=> [RESOURCE_TYPE_SILK => 2],],
                    ],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argDeliver();
        
        assertSame($expectedArgs, $args);
        assertSame(null, Globals::getTurnMainActionDone());
    }
    public function test_Args_ScenarioScorpion_CannotDeliverWithTargets(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":6,"2":6,"3":6,"4":5,"5":5,"6":25}';
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$cards[14] = TestDatas::$cards[11];
        TestDatas::$cards[14]['card_id'] = 14;
        TestDatas::$cards[14]['result_associative_index'] = 14;
        TestDatas::$cards[14]['type'] = CARD_ARTISAN_4;
        TestDatas::$cards[15] = TestDatas::$cards[11];
        TestDatas::$cards[15]['card_id'] = 15;
        TestDatas::$cards[15]['result_associative_index'] = 15;
        TestDatas::$cards[15]['type'] = CARD_ARTISAN_5;
        TestDatas::$cards[16] = TestDatas::$cards[11];
        TestDatas::$cards[16]['card_id'] = 16;
        TestDatas::$cards[16]['result_associative_index'] = 16;
        TestDatas::$cards[16]['type'] = CARD_ARTISAN_6;
        //Test with Shin 2 to list all regions cards at once
        TestDatas::$cards[17] = TestDatas::$cards[11];
        TestDatas::$cards[17]['card_id'] = 17;
        TestDatas::$cards[17]['result_associative_index'] = 17;
        TestDatas::$cards[17]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[17]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."17",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::SCORPION_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //Add Scorpion targets on influence region 3,4,5,6 :
        for($k = 3; $k <= 6; $k++ ) TestDatas::$tokens[43+$k] = ['result_associative_index' => 43 + $k, 'meeple_id' => 43 +$k, 'meeple_state' => 5, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE."$k",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => SCORPION_ENEMY_ID,  ];
        $expectedArgs = [
            '_private' => [
                TestDatas::$test_activePlayerId => [
                    'c' => [11 ], // cards ids
                    'canReplaceGoods' => [
                        'marker' => 43,
                        'cards' => [11,12],
                        'cardsCosts' => [
                            11 => [RESOURCE_TYPE_POTTERY => 2], 
                            12 => [RESOURCE_TYPE_POTTERY => 2], 
                            //13 => [RESOURCE_TYPE_SILK => 2],
                            //14 => [RESOURCE_TYPE_SILK => 2],
                            //15 => [RESOURCE_TYPE_RICE => 2],
                            //16 => [RESOURCE_TYPE_RICE => 2],
                        ],
                    ],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argDeliver();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_ScenarioScorpionAndDragon_CannotDeliverWithTargets(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$players[1]['die_face'] = 2;
        TestDatas::$players[1]['resources'] = '{"1":6,"2":6,"3":6,"4":5,"5":5,"6":25}';
        TestDatas::resetCardsDeck();
        for($k = 1; $k <= 6; $k++ ){
            TestDatas::$cards[$k]['card_location'] = CARD_LOCATION_HAND;
            TestDatas::$cards[$k]['player_id'] = 1;
        }
        TestDatas::$cards[24 + 1]['card_location'] = CARD_LOCATION_MAP_REGION."1";
        TestDatas::$cards[24 + 2]['card_location'] = CARD_LOCATION_MAP_REGION."2";
        TestDatas::$cards[24 + 3]['card_location'] = CARD_LOCATION_MAP_REGION."3";
        TestDatas::$cards[24 + 4]['card_location'] = CARD_LOCATION_MAP_REGION."4";
        TestDatas::$cards[24 + 5]['card_location'] = CARD_LOCATION_MAP_REGION."5";
        TestDatas::$cards[24 + 6]['card_location'] = CARD_LOCATION_MAP_REGION."6";
        //Test with Shin 2 to list all regions cards at once
        TestDatas::$cards[17] = TestDatas::$cards[11];
        TestDatas::$cards[17]['card_id'] = 17;
        TestDatas::$cards[17]['result_associative_index'] = 17;
        TestDatas::$cards[17]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[17]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."17",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::SCORPION_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //set influence
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[$k]['meeple_state'] = 15;
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[30 + $k]['meeple_state'] = 14;
        //Add Scorpion targets on influence region 3,4 :
        for($k = 3; $k <= 4; $k++ ) TestDatas::$tokens[43+$k] = ['result_associative_index' => 43 + $k, 'meeple_id' => 43 +$k, 'meeple_state' => 5, 'meeple_location'=> MEEPLE_LOCATION_INFLUENCE."$k",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => SCORPION_ENEMY_ID,  ];
        $expectedArgs = [
            '_private' => [
                TestDatas::$test_activePlayerId => [
                    'c' => [2,26 ], // cards ids
                    'canReplaceGoods' => [
                        'marker' => 43,
                        'cards' => [1,2,5,6, 25,26,29,30],
                        'cardsCosts' => [
                            1 => [RESOURCE_TYPE_POTTERY => 2], 
                            2 => [RESOURCE_TYPE_POTTERY => 2], 
                            //3 => [RESOURCE_TYPE_SILK => 2],
                            //4 => [RESOURCE_TYPE_SILK => 2],
                            5 => [RESOURCE_TYPE_RICE => 2],
                            6 => [RESOURCE_TYPE_RICE => 2],
                            //nobles :
                            24 + 1 => [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>2],
                            24 + 2 => [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_POTTERY=>2],
                            //24 + 3 => [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>1,RESOURCE_TYPE_POTTERY=>1],
                            //24 + 4 => [RESOURCE_TYPE_SILK=>2,RESOURCE_TYPE_RICE=>2],
                            24 + 5 => [RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>2],
                            24 + 6 => [RESOURCE_TYPE_SILK=>1,RESOURCE_TYPE_RICE=>2,RESOURCE_TYPE_POTTERY=>1],
                        ],
                    ],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argDeliver();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_ScenarioDragon_Noble1_HighInfluence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":6,"2":6,"3":6,"4":5,"5":5,"6":25}';
        TestDatas::resetCardsDeck();
        TestDatas::$cards[1]['card_location'] = CARD_LOCATION_HAND;
        TestDatas::$cards[1]['player_id'] = 1;
        TestDatas::$cards[24 + 1]['card_location'] = CARD_LOCATION_MAP_REGION."1";
        TestDatas::$cards[24 + 2]['card_location'] = CARD_LOCATION_MAP_REGION."2";
        TestDatas::$cards[24 + 3]['card_location'] = CARD_LOCATION_MAP_REGION."3";
        TestDatas::$cards[24 + 4]['card_location'] = CARD_LOCATION_MAP_REGION."4";
        TestDatas::$cards[24 + 5]['card_location'] = CARD_LOCATION_MAP_REGION."5";
        TestDatas::$cards[24 + 6]['card_location'] = CARD_LOCATION_MAP_REGION."6";
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::DRAGON_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //set influence
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[$k]['meeple_state'] = 15;
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[30 + $k]['meeple_state'] = 14;
        $expectedArgs = [
            '_private' => [
                TestDatas::$test_activePlayerId => [
                    'c' => [1, 25 ], // cards ids
                    'canReplaceGoods' => [
                    ],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argDeliver();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_ScenarioDragon_Noble1_LowInfluence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":6,"2":6,"3":6,"4":5,"5":5,"6":25}';
        TestDatas::resetCardsDeck();
        TestDatas::$cards[1]['card_location'] = CARD_LOCATION_HAND;
        TestDatas::$cards[1]['player_id'] = 1;
        TestDatas::$cards[24 + 1]['card_location'] = CARD_LOCATION_MAP_REGION."1";
        TestDatas::$cards[24 + 2]['card_location'] = CARD_LOCATION_MAP_REGION."2";
        TestDatas::$cards[24 + 3]['card_location'] = CARD_LOCATION_MAP_REGION."3";
        TestDatas::$cards[24 + 4]['card_location'] = CARD_LOCATION_MAP_REGION."4";
        TestDatas::$cards[24 + 5]['card_location'] = CARD_LOCATION_MAP_REGION."5";
        TestDatas::$cards[24 + 6]['card_location'] = CARD_LOCATION_MAP_REGION."6";
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::DRAGON_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //set influence
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[$k]['meeple_state'] = 13;
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[30 + $k]['meeple_state'] = 14;
        $expectedArgs = [
            '_private' => [
                TestDatas::$test_activePlayerId => [
                    'c' => [1 ], // cards ids
                    'canReplaceGoods' => [
                    ],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argDeliver();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_ScenarioDragon_Noble6_HighInfluence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$players[1]['die_face'] = 6;
        TestDatas::$players[1]['resources'] = '{"1":6,"2":6,"3":6,"4":5,"5":5,"6":25}';
        TestDatas::resetCardsDeck();
        TestDatas::$cards[6]['card_location'] = CARD_LOCATION_HAND;
        TestDatas::$cards[6]['player_id'] = 1;
        TestDatas::$cards[24 + 1]['card_location'] = CARD_LOCATION_MAP_REGION."1";
        TestDatas::$cards[24 + 2]['card_location'] = CARD_LOCATION_MAP_REGION."2";
        TestDatas::$cards[24 + 3]['card_location'] = CARD_LOCATION_MAP_REGION."3";
        TestDatas::$cards[24 + 4]['card_location'] = CARD_LOCATION_MAP_REGION."4";
        TestDatas::$cards[24 + 5]['card_location'] = CARD_LOCATION_MAP_REGION."5";
        TestDatas::$cards[24 + 6]['card_location'] = CARD_LOCATION_MAP_REGION."6";
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::SCORPION_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //set influence
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[$k]['meeple_state'] = 15;
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[30 + $k]['meeple_state'] = 14;
        $expectedArgs = [
            '_private' => [
                TestDatas::$test_activePlayerId => [
                    'c' => [6, 30 ], // cards ids
                    'canReplaceGoods' => [
                    ],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argDeliver();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_ScenarioDragon_Noble6_LowInfluence(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$players[1]['die_face'] = 6;
        TestDatas::$players[1]['resources'] = '{"1":6,"2":6,"3":6,"4":5,"5":5,"6":25}';
        TestDatas::resetCardsDeck();
        TestDatas::$cards[6]['card_location'] = CARD_LOCATION_HAND;
        TestDatas::$cards[6]['player_id'] = 1;
        TestDatas::$cards[24 + 1]['card_location'] = CARD_LOCATION_MAP_REGION."1";
        TestDatas::$cards[24 + 2]['card_location'] = CARD_LOCATION_MAP_REGION."2";
        TestDatas::$cards[24 + 3]['card_location'] = CARD_LOCATION_MAP_REGION."3";
        TestDatas::$cards[24 + 4]['card_location'] = CARD_LOCATION_MAP_REGION."4";
        TestDatas::$cards[24 + 5]['card_location'] = CARD_LOCATION_MAP_REGION."5";
        TestDatas::$cards[24 + 6]['card_location'] = CARD_LOCATION_MAP_REGION."6";
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::SCORPION_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //set influence
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[$k]['meeple_state'] = 13;
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[30 + $k]['meeple_state'] = 14;
        $expectedArgs = [
            '_private' => [
                TestDatas::$test_activePlayerId => [
                    'c' => [6 ], // cards ids
                    'canReplaceGoods' => [
                    ],
                ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argDeliver();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
    // -------------------------------------------------
    // -------------------------------------------------
 
    public function test_ActionDeliver_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        TestDatas::$tokens[3]['meeple_state'] = 2;

        $game->actDeliverSelect($cardId,999999);
        
        //check card datas
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);
        assertSame(TestDatas::$test_activePlayerId, $cardDatas['player_id']);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);// -2 Silk 
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(2, $resources[RESOURCE_TYPE_RICE]);
        assertSame(0, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
         //Test gained influence in region 3
        assertSame(4, TestDatas::$tokens[3]['meeple_state']);
        assertSame(json_encode([BONUS_TYPE_REFILL_HAND]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(1, TestDatas::$stats[TestDatas::$test_activePlayerId]['nbActionsDeliver']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(MAIN_ACTION::DELIVER->value, Globals::getTurnMainActionDone());
    }
    
    public function test_ActionDeliver_KO_WrongLocation(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Deliver card $cardId");
        $game->actDeliverSelect($cardId,999999);
    }
    public function test_ActionDeliver_KO_NotEnoughResources(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":1,"2":0,"3":2,"4":0,"5":0,"6":5}';

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Deliver card $cardId");
        $game->actDeliverSelect($cardId,999999);
    }
    
    public function test_ActionDeliver_KO_WrongRegion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 11;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":2,"3":2,"4":0,"5":0,"6":0}';

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Deliver card $cardId");
        $game->actDeliverSelect($cardId,999999);
    }

    public function test_ActionDeliver_Pass_AnyRegionWithSonOfStorm(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        TestDatas::$cards[101]['type'] = PATRON_SON_OF_STORM;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $cardId = 11;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":2,"3":2,"4":0,"5":0,"6":0}';

        //Test 1 OK with Son of storm 
        $game->actDeliverSelect($cardId,999999);
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);

        //Test 2 KO without
        $game = new GameMock();
        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Deliver card $cardId");
        $game->actDeliverSelect($cardId,999999);
    }
    
    public function test_ActionDeliver_Pass_PRIESTESS(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        TestDatas::$cards[101]['type'] = PATRON_PRIESTESS;
        TestDatas::$cards[101]['card_location'] = CARD_CLAN_LOCATION_ASSIGNED;
        $cardId = 13;
        TestDatas::$players[TestDatas::$test_activePlayerId]['die_face'] = 3;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        
        $game->actDeliverSelect($cardId,999999);

        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);
        //Test score +NB_POINTS_PRIESTESS
        assertSame(22, TestDatas::$players[TestDatas::$test_activePlayerId]['player_score']);

    }
    
    public function test_ActionDeliver_Magistrate_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$cards[$cardId]['type'] = 31;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":3,"4":0,"5":0,"6":9}';
        TestDatas::$tokens[1]['meeple_state'] = 12;

        $game->actDeliverSelect($cardId,999999);
        
        //check card datas
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);//-2
        assertSame(0, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(4, $resources[RESOURCE_TYPE_MONEY]);//-5
    }
    public function test_ActionDeliver_Magistrate_KO_NotEnoughMoney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$cards[$cardId]['type'] = 31;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":3,"4":0,"5":0,"6":4}';

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Deliver card $cardId");
        $game->actDeliverSelect($cardId,999999);
    }
    public function test_ActionDeliver_Pass_Trader1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$cards[$cardId]['type'] = CARD_TRADER_1;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":4,"3":5,"4":0,"5":0,"6":9}';

        $game->actDeliverSelect($cardId,999999);
        
        //check card datas
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);//-2
        assertSame(3, $resources[RESOURCE_TYPE_POTTERY]);//-1
        assertSame(4, $resources[RESOURCE_TYPE_RICE]);//-1
        assertSame(0, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(9, $resources[RESOURCE_TYPE_MONEY]);
        //INFLUENCe
        assertSame(1, TestDatas::$tokens[1]['meeple_state']);
        //ONGOING Ability must be activated right now :
        assertSame(20, TestDatas::$players[1]['player_score']);//+1
        assertSame(json_encode([BONUS_TYPE_DRAW, BONUS_TYPE_REFILL_HAND]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionDeliver_Pass_ScenarioMantis1_moveRogueShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$cards[$cardId]['type'] = CARD_ARTISAN_3;
        TestDatas::$players[1]['die_face'] = 3;
        TestDatas::$players[1]['resources'] = '{"1":5,"2":5,"3":5,"4":0,"5":0,"6":9}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::MANTIS_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$tokens[21]['meeple_state'] = 6;
        TestDatas::$tokens[26]['player_id'] = ROGUE_PLAYER_ID;
        TestDatas::$tokens[26]['meeple_state'] = 6;//suppose someone moved rogue ship to same place as our ship
        $expectedNotifs = [
            "deliver-1",
            "spendResource-1",
            "gainInfluence-1",
            "giveResource-1", // influence track
            "newClanMarker-1", //artisan
            "moveRogueShip-1",
        ];

        $game->actDeliverSelect($cardId,999999);
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //MOVED rogue ship
        assertSame(5, TestDatas::$tokens[26]['meeple_state']);
    }
    
    public function test_ActionDeliver_Pass_ScenarioMantis1_removeRogueShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$cards[$cardId]['type'] = CARD_ARTISAN_1;
        TestDatas::$players[1]['die_face'] = 1;
        TestDatas::$players[1]['resources'] = '{"1":5,"2":5,"3":5,"4":0,"5":0,"6":9}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::MANTIS_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$tokens[26]['player_id'] = ROGUE_PLAYER_ID;
        TestDatas::$tokens[26]['meeple_state'] = 1;
        $expectedNotifs = [
            "deliver-1",
            "spendResource-1",
            "gainInfluence-1",
            "giveResource-1", // influence track
            "newClanMarker-1", //artisan
            "moveRogueShip-1",
            "removeShip-1",
        ];

        $game->actDeliverSelect($cardId,999999);
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //REMOVED rogue ship
        assertFalse(array_key_exists(26,TestDatas::$tokens));
    }
    
    public function test_ActionDeliver_Pass_ScenarioDragon1_Noble2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$players[1]['die_face'] = 2;
        TestDatas::$players[1]['resources'] = '{"1":6,"2":6,"3":6,"4":5,"5":5,"6":25}';
        TestDatas::resetCardsDeck();
        TestDatas::$cards[24 + 1]['card_location'] = CARD_LOCATION_MAP_REGION."1";
        TestDatas::$cards[24 + 2]['card_location'] = CARD_LOCATION_MAP_REGION."2";
        TestDatas::$cards[24 + 3]['card_location'] = CARD_LOCATION_MAP_REGION."3";
        TestDatas::$cards[24 + 4]['card_location'] = CARD_LOCATION_MAP_REGION."4";
        TestDatas::$cards[24 + 5]['card_location'] = CARD_LOCATION_MAP_REGION."5";
        TestDatas::$cards[24 + 6]['card_location'] = CARD_LOCATION_MAP_REGION."6";
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::DRAGON_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //set influence
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[$k]['meeple_state'] = 2;
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[30 + $k]['meeple_state'] = 0;
        $cardId = 26;
        $expectedNotifs = [
            "deliver-1",
            "spendResource-1",
            "spendResource-1",
            "gainInfluence-1",
            "addBonus-1",
        ];

        $game->actDeliverSelect($cardId,999999);
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);
        assertSame(TestDatas::$test_activePlayerId, $cardDatas['player_id']);
        assertSame(2 +2, TestDatas::$tokens[2]['meeple_state']);
        assertSame([BONUS_TYPE_UPGRADE_SHIP],json_decode( TestDatas::$players[1]['bonuses']));
    }
    
    public function test_ActionDeliver_Pass_ScenarioDragon1_Noble4_AlreadyRoyalShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$players[1]['die_face'] = 4;
        TestDatas::$players[1]['resources'] = '{"1":6,"2":6,"3":6,"4":5,"5":5,"6":25}';
        TestDatas::resetCardsDeck();
        TestDatas::$cards[24 + 1]['card_location'] = CARD_LOCATION_MAP_REGION."1";
        TestDatas::$cards[24 + 2]['card_location'] = CARD_LOCATION_MAP_REGION."2";
        TestDatas::$cards[24 + 3]['card_location'] = CARD_LOCATION_MAP_REGION."3";
        TestDatas::$cards[24 + 4]['card_location'] = CARD_LOCATION_MAP_REGION."4";
        TestDatas::$cards[24 + 5]['card_location'] = CARD_LOCATION_MAP_REGION."5";
        TestDatas::$cards[24 + 6]['card_location'] = CARD_LOCATION_MAP_REGION."6";
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::DRAGON_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //set influence
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[$k]['meeple_state'] = 2;
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[30 + $k]['meeple_state'] = 0;
        TestDatas::$tokens[21]['type'] = MEEPLE_TYPE_SHIP_ROYAL;
        $cardId = 28;
        $expectedNotifs = [
            "deliver-1",
            "spendResource-1",
            "spendResource-1",
            "gainInfluence-1",
        ];

        $game->actDeliverSelect($cardId,999999);
        
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);
        assertSame(TestDatas::$test_activePlayerId, $cardDatas['player_id']);
        assertSame(2 +2, TestDatas::$tokens[4]['meeple_state']);
        assertSame([],json_decode( TestDatas::$players[1]['bonuses']));
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
    // -------------------------------------------------
    // -------------------------------------------------
    public function test_actDeliverReplace_Pass_WithShin2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$players[1]['die_face'] = 4;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":3,"3":1,"4":3,"5":0,"6":5}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 2;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $silk = 0;
        $pottery = 2;
        $rice = 0;

        $game->actDeliverReplace($cardId,$silk,$rice,$pottery,999999);
        
        //check card datas
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]); 
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);//-2 replaced
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(5, $resources[RESOURCE_TYPE_MONEY]);
         //Test gained influence in region 3
        assertSame(4, TestDatas::$tokens[3]['meeple_state']);
        assertSame(json_encode([BONUS_TYPE_REFILL_HAND]), TestDatas::$players[1]['bonuses']);
        assertSame(1, TestDatas::$stats[1]['nbActionsDeliver']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(MAIN_ACTION::DELIVER->value, Globals::getTurnMainActionDone());
    }
    public function test_actDeliverReplace_Pass_WithShin2_Magistrate(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$cards[$cardId]['type'] = 31;
        TestDatas::$players[1]['die_face'] = 4;
        TestDatas::$players[1]['resources'] = '{"1":1,"2":1,"3":1,"4":3,"5":0,"6":9}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$tokens[1]['meeple_state'] = 12;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $silk = 0;
        $pottery = 1;
        $rice = 1;

        $game->actDeliverReplace($cardId,$silk,$rice,$pottery,999999);

        //check card datas
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);//-1
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);//-1
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(4, $resources[RESOURCE_TYPE_MONEY]);//-5
    }
    
    public function test_actDeliverReplace_Pass_WithShin2_ScenarioDragon1_Noble3(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        Globals::setOptionSeishin(OPTION_SEISHIN_LEVEL_1);
        TestDatas::$players[1]['die_face'] = 2;
        TestDatas::$players[1]['resources'] = '{"1":1,"2":6,"3":2,"4":3,"5":0,"6":25}';
        TestDatas::resetCardsDeck();
        TestDatas::$cards[24 + 1]['card_location'] = CARD_LOCATION_MAP_REGION."1";
        TestDatas::$cards[24 + 2]['card_location'] = CARD_LOCATION_MAP_REGION."2";
        TestDatas::$cards[24 + 3]['card_location'] = CARD_LOCATION_MAP_REGION."3";
        TestDatas::$cards[24 + 4]['card_location'] = CARD_LOCATION_MAP_REGION."4";
        TestDatas::$cards[24 + 5]['card_location'] = CARD_LOCATION_MAP_REGION."5";
        TestDatas::$cards[24 + 6]['card_location'] = CARD_LOCATION_MAP_REGION."6";
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::DRAGON_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        //set influence
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[$k]['meeple_state'] = 2;
        for($k = 1; $k <= 6; $k++ ) TestDatas::$tokens[30 + $k]['meeple_state'] = 0;
        $cardId = 26;
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $silk = 1;
        $pottery = 2;
        $rice = 1;

        $game->actDeliverReplace($cardId,$silk,$rice,$pottery,999999);

        //check card datas
        $cardDatas = TestDatas::$cards[$cardId];
        assertSame(CARD_LOCATION_DELIVERED, $cardDatas['card_location']);
        assertSame(TestDatas::$test_activePlayerId, $cardDatas['player_id']);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);//-1
        assertSame(4, $resources[RESOURCE_TYPE_POTTERY]);//-2
        assertSame(1, $resources[RESOURCE_TYPE_RICE]);//-1
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(25, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(2 +2, TestDatas::$tokens[2]['meeple_state']);
        assertSame([BONUS_TYPE_UPGRADE_SHIP],json_decode( TestDatas::$players[1]['bonuses']));
    }

    public function test_actDeliverReplace_KO_WithShin2_Magistrate_NoMoney(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$cards[$cardId]['type'] = 31;
        TestDatas::$players[1]['die_face'] = 4;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":1,"3":1,"4":3,"5":0,"6":4}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$tokens[1]['meeple_state'] = 12;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $silk = 0;
        $pottery = 1;
        $rice = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Deliver card $cardId and replace goods");
        $game->actDeliverReplace($cardId,$silk,$rice,$pottery,999999);
    }
    
    public function test_actDeliverReplace_KO_WithoutAvailableShin2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$cards[$cardId]['type'] = 31;
        TestDatas::$players[1]['die_face'] = 4;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":1,"3":1,"4":3,"5":0,"6":4}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$tokens[1]['meeple_state'] = 12;
        $silk = 0;
        $pottery = 1;
        $rice = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot Deliver cards and replace goods");
        $game->actDeliverReplace($cardId,$silk,$rice,$pottery,999999);
    }
    
    public function test_actDeliverReplace_KO_WrongAmount(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$players[1]['die_face'] = 4;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":3,"3":1,"4":3,"5":0,"6":5}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 2;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $silk = 1;
        $pottery = 5;
        $rice = 3;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Wrong amount to replace goods : 2 != 9");
        $game->actDeliverReplace($cardId,$silk,$rice,$pottery,999999);
    }
    
    public function test_actDeliverReplace_KO_BigAmount(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_DELIVER;
        $cardId = 13;
        TestDatas::$players[1]['die_face'] = 4;
        TestDatas::$players[1]['resources'] = '{"1":1,"2":0,"3":1,"4":3,"5":0,"6":5}';
        TestDatas::$cards[12]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[12]['type'] = CARD_SHINDOSHI_2;
        TestDatas::$cards[12]['player_id'] = 1;
        TestDatas::$tokens[3]['meeple_state'] = 2;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."12",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $silk = 0;
        $pottery = 1;
        $rice = 1;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot spend 1 of resource type 2");
        $game->actDeliverReplace($cardId,$silk,$rice,$pottery,999999);
    }
    // -------------------------------------------------
}