<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\ClientAnswer;
use ROG\Managers\Players;
use ROG\Managers\Tiles;
use ROG\Models\BEFORE_ACTION;
use ROG\Models\ScenarioType;
use ROG\Models\TURN_ACTION;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;

final class PlayerTurnTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args_OnlySail(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        Globals::setChoices(0);
        $expectedArgs = [
            'a' => [
                'actSail',
            ],
            'die_face' => 1,
            'p_cards' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_SpendFavor(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":5,"6":0}';
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        Globals::setChoices(0);
        $expectedArgs = [
            'a' => [
                'actSpendFavor',
                'actSail',
            ],
            'die_face' => 1,
            'p_cards' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_Trade(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":2,"2":0,"3":0,"4":0,"5":0,"6":0}';
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        Globals::setChoices(0);
        $expectedArgs = [
            'a' => [
                'actTrade',
                'actSail',
            ],
            'die_face' => 1,
            'p_cards' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }

    public function test_Args_Build(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        Globals::setChoices(0);
        $expectedArgs = [
            'a' => [
                'actBuild',
                'actSail',
            ],
            'die_face' => 1,
            'p_cards' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }

    public function test_Args_Deliver(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":2,"3":0,"4":0,"5":0,"6":0}';
        //TestDatas::$cards[11]; may be delivered
        TestDatas::$cards[101]['type'] = PATRON_DARLING;
        Globals::setChoices(0);

        $expectedArgs = [
            'a' => [
                'actTrade',
                'actSail',
                'actDeliver',
            ],
            'die_face' => 1,
            'p_cards' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_Advance(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        Globals::setOptionCity(OPTION_CITY_OF_LIES_ON);
        TestDatas::resetCityTokens();

        $expectedArgs = [
            'a' => [
                'actSail',
                'actAdvance',
            ],
            'die_face' => 1,
            'p_cards' => [],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_PlayableCards_Shin5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_5;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        Globals::setChoices(0);
        $expectedArgs = [
            'a' => [
                'actBuild',
                'actSail',
                'actPlayCard',
            ],
            'die_face' => 1,
            'p_cards' => [ 
                13 => [ 'marker' => 43, 
                        'actions' => [
                            BEFORE_ACTION::MOVE_BUILDING->value => [
                                'tiles' => [41,],
                                'spaces' => [
                                    1,2,3, 7,8,9,10,
                                    11,12,13,14,15,16,18,19,20,
                                    21,22,23,24,25,26,27,28, 30
                                ],
                            ],
                        ] 
                    ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_PlayableCards_Shin4and5(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_5;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        Globals::setChoices(0);
        $expectedArgs = [
            'a' => [
                'actBuild',
                'actSail',
                'actPlayCard',
            ],
            'die_face' => 1,
            'p_cards' => [ 
                11 => [ 'marker' => 43, 
                        'actions' => [
                            BEFORE_ACTION::SWAP_BOATS->value => [
                                'source' => [21,22,],
                                'dest' => [21,22,23,24,25,26],
                            ],
                        ],
                    ],
                13 => [ 'marker' => 44, 
                        'actions' => [
                            BEFORE_ACTION::MOVE_BUILDING->value => [
                                'tiles' => [41,],
                                'spaces' => [
                                    1,2,3, 7,8,9,10,
                                    11,12,13,14,15,16,18,19,20,
                                    21,22,23,24,25,26,27,28,30
                                ],
                            ],
                        ],
                    ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_PlayableCards_Shin4and5_NoEmptyShoreSpace(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[13]['type'] = CARD_SHINDOSHI_5;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."13",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        for($k = 0; $k<30;$k++){
            //Fill all shore spaces
            $index = 41+$k;
            TestDatas::$tiles[$index] = TestDatas::$tiles[41];
            TestDatas::$tiles[$index]['tile_id'] = $index;
            TestDatas::$tiles[$index]['result_associative_index'] = $index;
            TestDatas::$tiles[$index]['tile_state'] = $k +1;
        }
        Globals::setChoices(0);
        $expectedArgs = [
            'a' => [
                'actSail',
                'actPlayCard',
            ],
            'die_face' => 1,
            'p_cards' => [ 
                11 => [ 'marker' => 43, 
                        'actions' => [
                            BEFORE_ACTION::SWAP_BOATS->value => [
                                'source' => [21,22,],
                                'dest' => [21,22,23,24,25,26],
                            ],
                        ],
                    ],
                13 => [ 'marker' => 44, 
                        'actions' => [
                        ],
                    ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_PlayableCards_ScenarioPhoenix1_Playable(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":1,"6":20}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::PHOENIX_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'card_played' => false];
        TestDatas::$tiles[2]['tile_location'] = TILE_LOCATION_MASTERY_DECK;
        TestDatas::$tiles[3]['tile_location'] = TILE_LOCATION_MASTERY_DECK;
        $expectedArgs = [
            'a' => [
                'actSpendFavor',
                'actBuild',
                'actSail',
                'actPlayCard',
            ],
            'die_face' => 1,
            'p_cards' => [ 
                301 => [   
                        'marker' => null,
                        'actions' => [
                            TURN_ACTION::DIVINE_CYCLING->value => [

                            ],
                        ] 
                    ],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_PlayableCards_ScenarioPhoenix1_NotPlayable(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":0,"6":20}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::PHOENIX_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'card_played' => false];
        $expectedArgs = [
            'a' => [
                'actBuild',
                'actSail',
            ],
            'die_face' => 1,
            'p_cards' => [ 
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_PlayableCards_ScenarioPhoenix1_AlreadyPlayed(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":0,"5":1,"6":20}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::PHOENIX_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'card_played' => true];
        $expectedArgs = [
            'a' => [
                'actSpendFavor',
                'actBuild',
                'actSail',
            ],
            'die_face' => 1,
            'p_cards' => [ 
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionSpendFavor(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actSpendFavor(999999);
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_DIVINE_FAVOR, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
 
    public function test_ActionTrade(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actTrade(999999);
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_TRADE, GamestateMachine::$test_current_state);
    }
    // -------------------------------------------------
 
    public function test_ActionBuild(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actBuild(999999);
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_BUILD, GamestateMachine::$test_current_state);
    }
    // -------------------------------------------------
 
    public function test_ActionSail(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actSail(999999);
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_SAIL, GamestateMachine::$test_current_state);
    }
    // -------------------------------------------------
 
    public function test_ActionDeliver(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actDeliver(999999);
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_DELIVER, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
 
    public function test_ActionAdvance(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actAdvance(999999);
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_ADVANCE, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
    // -------------------------------------------------
    public function test_ActionPlayCard_Shin4_SwapBoats_Pass_OwnBoats(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 43;
        $action = BEFORE_ACTION::SWAP_BOATS->value;
        $source = 21;
        $dest = 22;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $game->actPlayCard($answer,999999);
        
        //Test moved ships positions: 
        assertSame(14, TestDatas::$tokens[21]['meeple_state']);
        assertSame(5,  TestDatas::$tokens[22]['meeple_state']);
        //Test not moved ships positions: 
        assertSame(3,  TestDatas::$tokens[23]['meeple_state']);
        assertSame(14,  TestDatas::$tokens[24]['meeple_state']);
        //Test stay in state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
        assertSame(1,  TestDatas::$cards[$cardId]['card_played']);
    }
    public function test_ActionPlayCard_SwapBoats_Pass_OpponentBoat(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 43;
        $action = BEFORE_ACTION::SWAP_BOATS->value;
        $source = 21;
        $dest = 23;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $game->actPlayCard($answer,999999);
        
        //Test moved ships positions: 
        assertSame(3,  TestDatas::$tokens[21]['meeple_state']);
        assertSame(5,  TestDatas::$tokens[23]['meeple_state']);
        //test unchanged player id :
        assertSame(1,  TestDatas::$tokens[21]['player_id']);
        assertSame(2,  TestDatas::$tokens[23]['player_id']);
        //Test not moved ships positions: 
        assertSame(14,  TestDatas::$tokens[22]['meeple_state']);
        assertSame(14,  TestDatas::$tokens[24]['meeple_state']);
        //Test stay in state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionPlayCard_SwapBoats_Pass_RogueShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 43;
        $action = BEFORE_ACTION::SWAP_BOATS->value;
        $source = 21;
        $dest = 23;
        Globals::setRogueClan(CLAN_PHOENIX);
        TestDatas::$tokens[$dest]['player_id'] = ROGUE_PLAYER_ID;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $game->actPlayCard($answer,999999);
        
        //Test moved ships positions: 
        assertSame(3,  TestDatas::$tokens[21]['meeple_state']);
        assertSame(5,  TestDatas::$tokens[23]['meeple_state']);
        //test unchanged player id :
        assertSame(1,  TestDatas::$tokens[21]['player_id']);
        assertSame(ROGUE_PLAYER_ID,  TestDatas::$tokens[23]['player_id']);
        //Test not moved ships positions: 
        assertSame(14,  TestDatas::$tokens[22]['meeple_state']);
        assertSame(14,  TestDatas::$tokens[24]['meeple_state']);
        //Test stay in state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionPlayCard_ScenarioPhoenix1_DivineCycling(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$players[1]['resources'] = '{"1":0,"2":0,"3":0,"4":3,"5":1,"6":20}';
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::PHOENIX_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'card_played' => false];
        TestDatas::$tiles[2]['tile_location'] = TILE_LOCATION_MASTERY_DECK;
        TestDatas::$tiles[3]['tile_location'] = TILE_LOCATION_MASTERY_DECK;
        $cardId = 301;
        $action = TURN_ACTION::DIVINE_CYCLING->value;
        $answer = new ClientAnswer($cardId,null,$action, null,null );

        $game->actPlayCard($answer,999999);

        $expectedNotifs = [
            "scenarioAbility-1",
            "spendResource-1",
            "masteryBottom-1",
            "masteryDeck",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //Test stay in state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[1]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(3, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(20,$resources[RESOURCE_TYPE_MONEY]);
        assertSame(1, Tiles::countInLocation(TILE_LOCATION_MASTERY_CARD));
        assertSame(8, Tiles::countInLocation(TILE_LOCATION_MASTERY_DECK));
        assertSame(TILE_LOCATION_MASTERY_DECK, TestDatas::$tiles[1]['tile_location']);
        assertSame(TILE_LOCATION_MASTERY_CARD, TestDatas::$tiles[9]['tile_location']);
    }
    public function test_ActionPlayCard_Shin4_SwapBoats_KO_WrongCard(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 999;
        $markerId = 43;
        $action = BEFORE_ACTION::SWAP_BOATS->value;
        $source = 21;
        $dest = 22;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot play card $cardId");
        $game->actPlayCard($answer,999999);
    }
    
    public function test_ActionPlayCard_Shin4_SwapBoats_KO_WrongMarker(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 999;
        $action = BEFORE_ACTION::SWAP_BOATS->value;
        $source = 21;
        $dest = 22;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot play card $cardId with marker $markerId");
        $game->actPlayCard($answer,999999);
    }
    
    public function test_ActionPlayCard_KO_WrongAction(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 43;
        $action = "WWWWWW";
        $source = 21;
        $dest = 22;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot play card $cardId with Action");
        $game->actPlayCard($answer,999999);
    }
    public function test_ActionPlayCard_KO_WrongSource(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 43;
        $action = BEFORE_ACTION::SWAP_BOATS->value;
        $source = 23;
        $dest = 22;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot swap ships from $source");
        $game->actPlayCard($answer,999999);
    }
    public function test_ActionPlayCard_KO_WrongDest(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 43;
        $action = BEFORE_ACTION::SWAP_BOATS->value;
        $source = 21;
        $dest = 99;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot swap ships from $source to $dest");
        $game->actPlayCard($answer,999999);
    }
    public function test_ActionPlayCard_KO_SourceDest(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_4;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 43;
        $action = BEFORE_ACTION::SWAP_BOATS->value;
        $source = 21;
        $dest = $source;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot swap ships in the same space");
        $game->actPlayCard($answer,999999);
    }
    
    public function test_ActionPlayCard_Shin5_MoveBuilding_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_5;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 43;
        $action = BEFORE_ACTION::MOVE_BUILDING->value;
        $source = 41;
        $dest = 20;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $game->actPlayCard($answer,999999);
        
        $expectedNotifs = [
            "playCustomerAbility-1",//shin5
            "removeClanMarker-1",//shin5
            "moveBuilding-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //Test moved tile: 
        assertSame($dest, TestDatas::$tiles[41]['tile_state']);
        //Test stay in state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
        assertSame(1,  TestDatas::$cards[$cardId]['card_played']);
    }
    
    public function test_ActionPlayCard_Shin5_MoveBuilding_Pass_MasteryOfWaves(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_5;
        // -------------------- WAVES    --------------------------------
        TestDatas::$tiles[1]['type'] = 13;
        TestDatas::$tokens[43] = TestDatas::$tokens[41];
        TestDatas::$tokens[43]['result_associative_index'] = 43;
        TestDatas::$tokens[43]['meeple_location'] = MEEPLE_LOCATION_TILE.'43';
        TestDatas::$tiles[43] = TestDatas::$tiles[41];
        TestDatas::$tiles[43]['tile_id'] = 43;
        TestDatas::$tiles[43]['type'] = 19;
        TestDatas::$tiles[43]['result_associative_index'] = 43;
        TestDatas::$tiles[43]['tile_state'] = 10;
        TestDatas::$tokens[44] = TestDatas::$tokens[41];
        TestDatas::$tokens[44]['result_associative_index'] = 44;
        TestDatas::$tokens[44]['meeple_location'] = MEEPLE_LOCATION_TILE.'44';
        TestDatas::$tiles[44] = TestDatas::$tiles[41];
        TestDatas::$tiles[44]['tile_id'] = 44;
        TestDatas::$tiles[44]['type'] = 25;
        TestDatas::$tiles[44]['result_associative_index'] = 44;
        TestDatas::$tiles[44]['tile_state'] = 11;
        // River spaces before moving : 1,2,3,4,5,6,
        // -----------------------------------------------------------------
        $cardId = 11;
        $markerId = 45;
        TestDatas::$tokens[$markerId] = ['result_associative_index' => $markerId, 'meeple_id' => $markerId, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $action = BEFORE_ACTION::MOVE_BUILDING->value;
        $source = 44;
        $dest = 25;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $game->actPlayCard($answer,999999);
        
        $expectedNotifs = [
            "playCustomerAbility-1",//shin5
            "removeClanMarker-1",//shin5
            "moveBuilding-1",
            "newClanMarker-1",//Mastery
            "claimMC-1",//Mastery
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //Test moved tile: 
        assertSame($dest, TestDatas::$tiles[44]['tile_state']);
        // River spaces before moving : 1,2,3,4,5, 11,12,13
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
        //Test stay in state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
        assertSame(1,  TestDatas::$cards[$cardId]['card_played']);
    }
    
    public function test_ActionPlayCard_Shin5_MoveBuilding_Pass_ScenarioUnicorn_onResource(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$players[1]['resources'] = '{"1":1,"2":2,"3":1,"4":5,"5":5,"6":25}';
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_5;
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::UNICORN_1->value,   'subtype' => CARD_TYPE_SCENARIO,];
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 43;
        $action = BEFORE_ACTION::MOVE_BUILDING->value;
        $source = 41;
        $dest = 20;
        TestDatas::$tokens[44] = ['result_associative_index' => 44, 'meeple_id' => 44, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_SHORE."$dest",'type' => MEEPLE_TYPE_RESOURCE_RICE,  'player_id' => null,  ];
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $game->actPlayCard($answer,999999);
        
        $expectedNotifs = [
            "playCustomerAbility-1",//shin5
            "removeClanMarker-1",//shin5
            "moveBuilding-1",
            "removeClanMarker-1",//removeResourceMarker
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertFalse(array_key_exists(44,TestDatas::$tokens));
        //Test moved tile: 
        assertSame($dest, TestDatas::$tiles[41]['tile_state']);
        //unchanged resources :
        assertSame('{"1":1,"2":2,"3":1,"4":5,"5":5,"6":25}', TestDatas::$players[1]['resources']);
        assertSame(1,  TestDatas::$cards[$cardId]['card_played']);
    }
    public function test_ActionPlayCard_Shin5_MoveBuilding_KO_Source(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_5;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 43;
        $action = BEFORE_ACTION::MOVE_BUILDING->value;
        $source = 42;
        $dest = 20;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot move tile $source");
        $game->actPlayCard($answer,999999);
    }
    public function test_ActionPlayCard_Shin5_MoveBuilding_KO_Dest(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$cards[11]['card_location'] = CARD_LOCATION_DELIVERED;
        TestDatas::$cards[11]['type'] = CARD_SHINDOSHI_5;
        TestDatas::$tokens[43] = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 1, 'meeple_location'=> MEEPLE_LOCATION_CARD."11",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        $cardId = 11;
        $markerId = 43;
        $action = BEFORE_ACTION::MOVE_BUILDING->value;
        $source = 41;
        $dest = 99;
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot move tile $source to $dest");
        $game->actPlayCard($answer,999999);
    }
    // -------------------------------------------------
 
    public function test_goToBonusStepIfNeeded_False(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;
        TestDatas::$players[1]['bonuses'] = '[]';
        $player = Players::get(1);

        $game->goToBonusStepIfNeeded($player);
        
        //Test stay in state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }
    public function test_goToBonusStepIfNeeded_True(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['bonuses'] = json_encode([BONUS_TYPE_CHOICE]);
        $player = Players::get(1);

        $game->goToBonusStepIfNeeded($player);
        
        //Test go to bonus state
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_goToBonusStepIfNeeded_TrueChangePlayer(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['bonuses'] = json_encode([BONUS_TYPE_CHOICE]);
        $player = Players::get(1);

        $game->goToBonusStepIfNeeded($player,true);
        
        //Test go to bonus state
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
}