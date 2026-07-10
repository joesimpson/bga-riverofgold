<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Exceptions\UnexpectedException;
use ROG\Helpers\ClientAnswer;
use ROG\Managers\Cards;
use ROG\Models\AFTER_ACTION;
use ROG\Models\BEFORE_ACTION;
use ROG\Models\CITY_CARD_TYPE;
use ROG\Models\MAIN_ACTION;
use ROG\Models\ScenarioType;
use ROG\Models\TURN_ACTION;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class BonusChoiceTest extends TestCase
{


    // -------------------------------------------------
    public function test_EnteringState_Stay(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode([BONUS_TYPE_REFILL_HAND]);

        $game->stBonusChoice();
        
        //Stay in state when possible actions
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_EnteringState_Skip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode([]);

        $game->stBonusChoice();
        
        //skip state when 0 possible actions
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }

    public function test_EnteringState_GoBackToPlayerTurn(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode([]);
        Globals::setStateBeforeBonus(ST_PLAYER_TURN);

        $game->stBonusChoice();
        
        //skip state when 0 possible actions
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }

    // I don't know if it is a real use case, but just to be sure...
    public function test_EnteringState_DontGoBackToPlayerTurn(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode([]);
        Globals::setStateBeforeBonus(ST_PLAYER_TURN);
        Globals::setTurnMainActionDone(MAIN_ACTION::BUILD->value);

        $game->stBonusChoice();
        
        //skip state when 0 possible actions
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
    public function test_Args_Skippable(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonuses = [];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);
        $expectedArgs = [
            'p' => $bonuses,
            '_private' => [
                1 => [
                    'p' => [],
                ],
            ],
            'trade' => false,
            'canSkip' => true,
            'cannotSetDie' => true,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusChoice();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_UnSkippableRefill(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonuses = [BONUS_TYPE_REFILL_HAND];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);
        $expectedArgs = [
            'p' => $bonuses,
            '_private' => [
                1 => [
                    'p' => [],
                ],
            ],
            'trade' => false,
            'canSkip' => false,
            'cannotSetDie' => true,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusChoice();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_UnSkippableUpgrade(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonuses = [BONUS_TYPE_UPGRADE_SHIP];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);
        $expectedArgs = [
            'p' => $bonuses,
            '_private' => [
                1 => [
                    'p' => [],
                ],
            ],
            'trade' => false,
            'canSkip' => false,
            'cannotSetDie' => true,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusChoice();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_UnSkippableLion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonuses = [BONUS_TYPE_PLACE_LION];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);
        $expectedArgs = [
            'p' => $bonuses,
            '_private' => [
                1 => [
                    'p' => [],
                ],
            ],
            'trade' => false,
            'canSkip' => false,
            'cannotSetDie' => true,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusChoice();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_SkippableLion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonuses = [BONUS_TYPE_PLACE_LION];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);
        for($k = 0; $k<30;$k++){
            //Fill all shore spaces
            $index = 41+$k;
            TestDatas::$tiles[$index] = TestDatas::$tiles[41];
            TestDatas::$tiles[$index]['tile_id'] = $index;
            TestDatas::$tiles[$index]['result_associative_index'] = $index;
            TestDatas::$tiles[$index]['tile_state'] = $k +1;
        }
        $expectedArgs = [
            'p' => $bonuses,
            '_private' => [
                1 => [
                    'p' => [],
                ],
            ],
            'trade' => false,
            'canSkip' => true,
            'cannotSetDie' => true,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusChoice();
        
        assertSame($expectedArgs, $args);
    }
    public function test_Args_WithTrades(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $bonuses = [];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);
        $expectedArgs = [
            'p' => $bonuses,
            '_private' => [
                1 => [
                    'p' => [],
                ],
            ],
            'trade' => true,
            'canSkip' => true,
            'cannotSetDie' => true,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusChoice();
        
        assertSame($expectedArgs, $args);
    }   
    public function test_Args_WithFavor(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setChoices(0);
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":4,"5":3,"6":0}';
        $bonuses = [];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);
        $expectedArgs = [
            'p' => $bonuses,
            '_private' => [
                1 => [
                    'p' => [],
                ],
            ],
            'trade' => true,
            'canSkip' => true,
            'cannotSetDie' => false,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusChoice();
        
        assertSame($expectedArgs, $args);
    }   
    
    public function test_Args_ScenarioPhoenix1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        TestDatas::$players[1]['resources'] = '{"1":3,"2":0,"3":2,"4":4,"5":3,"6":0}';
        $bonuses = [];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        TestDatas::$cards[301] = ['result_associative_index' => 301,'card_id' => 301, 'card_location' => CARD_SCENARIO_LOCATION_ASSIGNED, 'card_state' => 0, 'player_id' => 1, 'type' => ScenarioType::PHOENIX_1->value,   'subtype' => CARD_TYPE_SCENARIO, 'card_played' => false];
        $expectedArgs = [
            'p' => $bonuses,
            '_private' => [
                1 => [
                    'p' => [],
                ],
            ],
            'trade' => true,
            'canSkip' => true,
            'cannotSetDie' => false,
            'a' => [ 'actPlayCard' ],
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

        $args = $game->argBonusChoice();
        
        assertSame($expectedArgs, $args);
    }   
    public function test_Args_WithRevealCard_1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonuses = [
            BONUS_TYPE_REFILL_HAND,
            'datas' => [
                BONUS_TYPE_REVEAL_CARD => [
                    1 => [
                        'cardId' => 221,
                        'actions' => [
                            AFTER_ACTION::GAIN_INFLUENCE->value => [
                                'n' => 2,
                                'regions' => [3],
                            ],
                        ],
                        'private' => true,
                    ],
                ],
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);

        $args = $game->argBonusChoice();
        
        $expectedArgs = [
            'p' => [BONUS_TYPE_REFILL_HAND],
            '_private' => [
                1 => [
                    'p' => [
                        'datas' => [
                            BONUS_TYPE_REVEAL_CARD => [
                                1 => [
                                    'cardId' => 221,
                                    'actions' => [
                                        AFTER_ACTION::GAIN_INFLUENCE->value => [
                                            'n' => 2,
                                            'regions' => [3],
                                        ],
                                    ],
                                    'private' => true,
                                ],
                            ],
                        ],
                    ],
                    'p_cards' => [
                        221 => [ 
                            'marker' => 1,
                            'source' => BONUS_TYPE_REVEAL_CARD,
                            'actions' => [
                                AFTER_ACTION::GAIN_INFLUENCE->value => [
                                    'n' => 2,
                                    'regions' => [3],
                                ],
                            ],
                        ],
                    ]
                ],
            ],
            'trade' => false,
            'canSkip' => false,
            'cannotSetDie' => true,
            'a' =>  [
                'actPlayCard',
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];
        assertSame($expectedArgs, $args);
    }   
    public function test_Args_WithRevealCard_2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        TestDatas::$cards[221]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[221]['player_id'] = 1;
        TestDatas::$cards[221]['type'] = CITY_CARD_TYPE::TRAVEL_TRO->value;
        $bonuses = [
            BONUS_TYPE_REFILL_HAND,
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);

        $args = $game->argBonusChoice();
        
        $expectedArgs = [
            'p' => [BONUS_TYPE_REFILL_HAND],
            '_private' => [
                1 => [
                    'p' => [],
                    'p_cards' => [
                        221 => [ 
                            'actions' => [
                                BEFORE_ACTION::GAIN_INFLUENCE->value => [
                                    'n' => 1,
                                    'regions' => [ 1,2,3,4,5,6 ],
                                ],
                            ],
                            'marker' => null,
                        ],
                    ]
                ],
            ],
            'trade' => false,
            'canSkip' => false,
            'cannotSetDie' => true,
            'a' =>  [
                'actPlayCard',
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];
        assertSame($expectedArgs, $args);
    }   
    // -------------------------------------------------
 
    public function test_ActionSkipBonuses_Pass(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;

        $game->actSkipBonuses(999999);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame(ST_CONFIRM_CHOICES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionSkipBonuses_KO_Unskipabble(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode([BONUS_TYPE_REFILL_HAND]);

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You should not skip these bonuses !");
        $game->actSkipBonuses(999999);
    }
    
    // -------------------------------------------------
    
    public function test_ActionBonus_Pass_Resource(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_CHOICE;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_CHOICE_RESOURCE, GamestateMachine::$test_current_state);
    }
    public function test_ActionBonus_Pass_UpgradeShip(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_UPGRADE_SHIP;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_UPGRADE_SHIP, GamestateMachine::$test_current_state);
    }
    public function test_ActionBonus_Pass_SecondMarkerOnBuilding(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_SECOND_MARKER_ON_BUILDING;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_SECOND_MARKER_ON_BUILDING, GamestateMachine::$test_current_state);
    }
    public function test_ActionBonus_Pass_SecondMarkerOnOpponent(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_SECOND_MARKER_ON_OPPONENT;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_SECOND_MARKER_ON_BUILDING, GamestateMachine::$test_current_state);
    }
    public function test_ActionBonus_Pass_MoneyOrGood(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_MONEY_OR_GOOD;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_MONEY_OR_GOOD, GamestateMachine::$test_current_state);
    }
    public function test_ActionBonus_Pass_SellGoods(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_SELL_GOODS;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_SELL_GOODS, GamestateMachine::$test_current_state);
    }
    public function test_ActionBonus_Pass_Draw(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_DRAW;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_DISCARD_CARD, GamestateMachine::$test_current_state);
        //Check draw 1 cards in hand :
        assertSame(CARD_LOCATION_HAND, TestDatas::$cards[1]['card_location']);
        assertSame(CARD_LOCATION_DECK, TestDatas::$cards[2]['card_location']);
        assertSame(CARD_LOCATION_DECK, TestDatas::$cards[3]['card_location']);
    }
    public function test_ActionBonus_Pass_EmptyDeckDraw(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_DRAW;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);
        foreach(TestDatas::$cards as &$card) if($card['subtype'] == CARD_TYPE_CUSTOMER) $card['card_location'] = CARD_LOCATION_DELIVERED;

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        foreach(TestDatas::$cards as &$card) if($card['subtype'] == CARD_TYPE_CUSTOMER) assertSame(CARD_LOCATION_DELIVERED,$card['card_location']);
    }
    public function test_ActionBonus_Pass_Refill(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_REFILL_HAND;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);
        TestDatas::$cards[13]['card_location'] = CARD_LOCATION_DELIVERED;

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_DISCARD_CARD, GamestateMachine::$test_current_state);
        //Check draw 2 cards in hand :
        assertSame(CARD_LOCATION_HAND, TestDatas::$cards[1]['card_location']);
        assertSame(CARD_LOCATION_HAND, TestDatas::$cards[2]['card_location']);
        assertSame(CARD_LOCATION_DECK, TestDatas::$cards[3]['card_location']);
    }
    public function test_ActionBonus_Pass_EmptyDeckRefill(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_REFILL_HAND;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);
        foreach(TestDatas::$cards as &$card) if($card['subtype'] == CARD_TYPE_CUSTOMER) $card['card_location'] = CARD_LOCATION_DELIVERED;

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_IncreaseHand(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_INC_HAND_LIMIT;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        //Check draw 1 card in hand :
        assertSame(1, TestDatas::$cards[1]['player_id']);
        assertSame(CARD_LOCATION_HAND, TestDatas::$cards[1]['card_location']);
        assertSame(3, Cards::countPlayerCards(1,CARD_LOCATION_HAND));
    }
    
    public function test_ActionBonus_Pass_deliverTopDeck(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_DELIVER_TOP_DECK;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        $expectedNotifs = [
            "deliver-1",
            "newClanMarker-1", //ELDER
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        //Check deliver 1 card
        assertSame(1, TestDatas::$cards[1]['player_id']);
        assertSame(CARD_LOCATION_DELIVERED, TestDatas::$cards[1]['card_location']);
        assertSame(1, Cards::countPlayerCards(1,CARD_LOCATION_DELIVERED));
        assertSame(2, Cards::countPlayerCards(1,CARD_LOCATION_HAND));
        //check customer ability : 
        $expectedToken = ['result_associative_index' => 43, 'meeple_id' => 43, 'meeple_state' => 0, 'meeple_location'=> MEEPLE_LOCATION_ELDER."1",'type' => MEEPLE_TYPE_CLAN_MARKER, 'player_id' => 1,  ];
        assertSame($expectedToken, TestDatas::$tokens[43]);
    }

    public function test_ActionBonus_Pass_SetDie(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_SET_DIE;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_SET_DIE, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_PlaceLion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_PLACE_LION;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_PLACE_LION, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_BuildingReward(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_BUILDING_REWARD;
        $bonusKey = 1;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    1 => ['tile'=>41,'position'=>5,],
                ],
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(['tile'=>41,'position'=>5,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_BUILDING_REWARD, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_AnyOwnerReward(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_ANY_OWNER_REWARD;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_BUILDING_REWARD, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_PayShips(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_PAY_SHIPS;
        $bonusKey = 1;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    1 => ['ship'=>21,'position'=>10,],
                ],
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(['ship'=>21,'position'=>10,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_PAY_SHIPS, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_AdvanceOrPoints(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_ADVANCE_OR_POINTS;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus(999999,$bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_ADVANCE_CITY, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_InfluenceRewardSelectRegion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $region = 1;
        $bonusType = BONUS_TYPE_INF_SELECT_REGION;
        $bonusKey = 3;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    1 => ['region'=>$region,'bonusQuantity'=>1,],
                    2 => ['region'=>$region,'bonusQuantity'=>1,],
                    3 => ['region'=>$region,'bonusQuantity'=>1,],
                    4 => ['region'=>$region,'bonusQuantity'=>1,],
                    5 => ['region'=>$region,'bonusQuantity'=>3,],
                ],
                
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [
            'datas' => [
                $bonusType => [
                    1 => ['region'=>$region,'bonusQuantity'=>1,],
                    2 => ['region'=>$region,'bonusQuantity'=>1,],
                    4 => ['region'=>$region,'bonusQuantity'=>1,],
                    5 => ['region'=>$region,'bonusQuantity'=>3,],
                ],
                
            ],
        ];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(['region'=>$region,'bonusQuantity'=>1,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_SELECT_REGION, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_MultiTrade2Points(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_MULTITRADE_2;
        $bonusKey = 1;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    1 => ['region'=>6,'bonusQuantity'=>4,'koku'=>3,'points'=>2,],
                    2 => ['region'=>6,'bonusQuantity'=>2,'koku'=>3,'points'=>2,],
                ],
                BONUS_TYPE_MULTITRADE_3 => [
                    1 => ['region'=>5,'bonusQuantity'=>1,'koku'=>3,'points'=>3,],
                ],
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [
            'datas' => [
                $bonusType => [
                    2 => ['region'=>6,'bonusQuantity'=>2,'koku'=>3,'points'=>2,],
                ],
                BONUS_TYPE_MULTITRADE_3 => [
                    1 => ['region'=>5,'bonusQuantity'=>1,'koku'=>3,'points'=>3,],
                ],
            ],
        ];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(['region'=>6,'bonusQuantity'=>4,'koku'=>3,'points'=>2,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_MULTI_TRADES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_MultiTrade3Points(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_MULTITRADE_3;
        $bonusKey = 1;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    1 => ['region'=>6,'bonusQuantity'=>1,'koku'=>3,'points'=>3,],
                ],
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [
        ];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(['region'=>6,'bonusQuantity'=>1,'koku'=>3,'points'=>3,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_MULTI_TRADES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_UniTrade2Points(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_TRADE_POINTS;
        $bonusKey = 1;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    1 => ['points'=>2,'bonusQuantity'=>1,],
                ],
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [
        ];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(['points'=>2,'bonusQuantity'=>1,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_MULTI_TRADES, GamestateMachine::$test_current_state);
    }
    public function test_ActionBonus_Pass_UniTrade3Koku(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_TRADE_KOKU;
        $bonusKey = 1;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    1 => ['koku'=>3,'bonusQuantity'=>1,],
                ],
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [
        ];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(['koku'=>3,'bonusQuantity'=>1,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_MULTI_TRADES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_ManageDebt(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_MANAGE_DEBT;
        $bonusKey = 1;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    $bonusKey => ['card_id'=>301, 'bonusQuantity'=>1,],
                ],
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [
        ];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(['card_id'=>301, 'bonusQuantity'=>1,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_MANAGE_CARD_RESOURCES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_RemoveGoods(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_REMOVE_GOODS;
        $bonusKey = 1;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    $bonusKey => ['card_id'=>301,'shoreSpacesIds'=>[21,22,23,25], 'meeplesIds'=>[43,44,45], 'bonusQuantity'=>1, 'resources' => [1,2,3] ],
                ],
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [
        ];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(['card_id'=>301,'shoreSpacesIds'=>[21,22,23,25], 'meeplesIds'=>[43,44,45], 'bonusQuantity'=>1, 'resources' => [1,2,3] ], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_MANAGE_CARD_RESOURCES, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_MultiRewardSelectRegion(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_REWARDS_SELECT_REGION;
        $bonusKey = 1;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    1 => [ 'bonusQuantity'=>2,],
                ],
                
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [
        ];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame([ 'bonusQuantity'=>2,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_SELECT_REGION, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_BuildNearShips(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_BUILD_NEAR_SHIPS;
        $bonusKey = 1;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    1 => [ 'bonusQuantity'=>1,],
                ],
                
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [
        ];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame([ 'bonusQuantity'=>1,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_FREE_BUILD, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionBonus_Pass_FreeSail(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_FREE_SAIL;
        $bonusKey = 1;
        $bonuses = [
            'datas' => [
                $bonusType => [
                    1 => [ 'bonusQuantity'=>1,],
                ],
                
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [
        ];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame([ 'bonusQuantity'=>1,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_FREE_SAIL, GamestateMachine::$test_current_state);
    }

    public function test_ActionBonus_Pass_CityDraw(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_CITY_CARD_DRAW;
        $bonusKey = 1;
        $bonuses = [
            BONUS_TYPE_CHOICE, BONUS_TYPE_CHOICE,
            'datas' => [
                $bonusType => [
                    1 => [ 'bonusQuantity'=>1,],
                ],
                
            ],
        ];
        TestDatas::$players[1]['bonuses'] = json_encode($bonuses);
        $expectedBonuses = [
            BONUS_TYPE_CHOICE, BONUS_TYPE_CHOICE,
        ];

        $game->actBonus(999999,$bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame([ 'bonusQuantity'=>1,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_CITY_DRAW, GamestateMachine::$test_current_state);
    }

    public function test_ActionBonus_KO_WrongBonus(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_REFILL_HAND;
        $bonuses = [];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You don't have this bonus $bonusType");
        $game->actBonus(999999,$bonusType);
    }
    public function test_ActionBonus_KO_UnexpectedBonus(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = 88;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("Not supported bonus type $bonusType");
        $game->actBonus(999999,$bonusType);
    }
    // -------------------------------------------------
    
    // -------------------------------------------------
    // actPlayCard defined in PlayerTurnTrait
    // -------------------------------------------------
    
    public function test_ActionPlayCard_GainInfluenceBeforeAction_Pass_City_TravelTroupe_GainBonus(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        Globals::setTurnMainActionDone(MAIN_ACTION::SAIL->value);
        $cardId = 221;
        TestDatas::$cards[$cardId]['card_location'] = CARD_CITY_LOCATION_HAND;
        TestDatas::$cards[$cardId]['player_id'] = 1;
        TestDatas::$cards[$cardId]['type'] = CITY_CARD_TYPE::TRAVEL_TRO->value;
        TestDatas::$players[1]['bonuses'] = json_encode([]);
        $markerId = null;
        $action = BEFORE_ACTION::GAIN_INFLUENCE->value;
        $source = null;
        $dest = 6;//region
        $answer = new ClientAnswer($cardId,$markerId,$action, $source,$dest );
        TestDatas::$tokens[$dest]['meeple_state'] = 17;

        $game->actPlayCard($answer,999999);
        
        $expectedNotifs = [
            "revealCityCard-1",
            "gainInfluence-1",
            "addBonus-1",
            "addPoints-1",
            "newClanMarker-1",
            "claimMC-1",
        ];
        assertSame($expectedNotifs, TestDatas::$notifs['all']);
        //Test moved card: 
        assertSame(CARD_CITY_LOCATION_REVEALED, TestDatas::$cards[$cardId]['card_location']);
        assertSame(1,  TestDatas::$cards[$cardId]['card_played']);
        //Test gained influence :
        assertSame(0, TestDatas::$tokens[1]['meeple_state']);
        assertSame(0, TestDatas::$tokens[2]['meeple_state']);
        assertSame(0, TestDatas::$tokens[3]['meeple_state']);
        assertSame(0, TestDatas::$tokens[4]['meeple_state']);
        assertSame(0, TestDatas::$tokens[5]['meeple_state']);
        assertSame(18, TestDatas::$tokens[6]['meeple_state']);
        // bonuses :
        assertSame(json_encode([BONUS_TYPE_CHOICE]), TestDatas::$players[1]['bonuses']);
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        assertSame(ST_BONUS_CHOICE, Globals::getStateBeforeBonus());
    }
    // -------------------------------------------------
}