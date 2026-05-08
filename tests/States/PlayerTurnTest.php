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
use ROG\Models\BEFORE_ACTION;
use Tests\Utils\TestDatas;

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
                                    1,2,3, 6,7,8,9,10,
                                    11,12,13,14,15,16,17,18,19,20,
                                    21,22,23,24,25,26,27,28,29,30
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
                                'dest' => [21,22,23,24],
                            ],
                        ],
                    ],
                13 => [ 'marker' => 44, 
                        'actions' => [
                            BEFORE_ACTION::MOVE_BUILDING->value => [
                                'tiles' => [41,],
                                'spaces' => [
                                    1,2,3, 6,7,8,9,10,
                                    11,12,13,14,15,16,17,18,19,20,
                                    21,22,23,24,25,26,27,28,29,30
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
                                'dest' => [21,22,23,24],
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

    // -------------------------------------------------
 
    public function test_ActionSpendFavor(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actSpendFavor();
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_DIVINE_FAVOR, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
 
    public function test_ActionTrade(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actTrade();
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_TRADE, GamestateMachine::$test_current_state);
    }
    // -------------------------------------------------
 
    public function test_ActionBuild(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actBuild();
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_BUILD, GamestateMachine::$test_current_state);
    }
    // -------------------------------------------------
 
    public function test_ActionSail(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actSail();
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_SAIL, GamestateMachine::$test_current_state);
    }
    // -------------------------------------------------
 
    public function test_ActionDeliver(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actDeliver();
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_DELIVER, GamestateMachine::$test_current_state);
    }
    
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

        $game->actPlayCard($answer);
        
        //Test moved ships positions: 
        assertSame(14, TestDatas::$tokens[21]['meeple_state']);
        assertSame(5,  TestDatas::$tokens[22]['meeple_state']);
        //Test not moved ships positions: 
        assertSame(3,  TestDatas::$tokens[23]['meeple_state']);
        assertSame(24,  TestDatas::$tokens[24]['meeple_state']);
        //Test stay in state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
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

        $game->actPlayCard($answer);
        
        //Test moved ships positions: 
        assertSame(3,  TestDatas::$tokens[21]['meeple_state']);
        assertSame(5,  TestDatas::$tokens[23]['meeple_state']);
        //test unchanged player id :
        assertSame(1,  TestDatas::$tokens[21]['player_id']);
        assertSame(2,  TestDatas::$tokens[23]['player_id']);
        //Test not moved ships positions: 
        assertSame(14,  TestDatas::$tokens[22]['meeple_state']);
        assertSame(24,  TestDatas::$tokens[24]['meeple_state']);
        //Test stay in state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
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
        $game->actPlayCard($answer);
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
        $game->actPlayCard($answer);
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
        $game->actPlayCard($answer);
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
        $game->actPlayCard($answer);
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
        $game->actPlayCard($answer);
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
        $game->actPlayCard($answer);
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

        $game->actPlayCard($answer);
        
        //Test moved tile: 
        assertSame($dest, TestDatas::$tiles[41]['tile_state']);
        //Test stay in state
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
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
        $game->actPlayCard($answer);
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
        $game->actPlayCard($answer);
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