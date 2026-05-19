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
            'trade' => true,
            'canSkip' => true,
            'cannotSetDie' => false,
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argBonusChoice();
        
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

        $game->actBonus($bonusType);
        
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

        $game->actBonus($bonusType);
        
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

        $game->actBonus($bonusType);
        
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

        $game->actBonus($bonusType);
        
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

        $game->actBonus($bonusType);
        
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

        $game->actBonus($bonusType);
        
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

        $game->actBonus($bonusType);
        
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
        foreach(TestDatas::$cards as &$card) $card['card_location'] = CARD_LOCATION_DISCARD;

        $game->actBonus($bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
        foreach(TestDatas::$cards as &$card) assertSame(CARD_LOCATION_DISCARD,$card['card_location']);
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

        $game->actBonus($bonusType);
        
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
        foreach(TestDatas::$cards as &$card) $card['card_location'] = CARD_LOCATION_DISCARD;

        $game->actBonus($bonusType);
        
        assertSame(json_encode([]), TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
    public function test_ActionBonus_Pass_SetDie(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_BONUS_CHOICE;
        $bonusType = BONUS_TYPE_SET_DIE;
        $bonuses = [$bonusType];
        TestDatas::$players[TestDatas::$test_activePlayerId]['bonuses'] = json_encode($bonuses);

        $game->actBonus($bonusType);
        
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

        $game->actBonus($bonusType);
        
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

        $game->actBonus($bonusType,$bonusKey);
        
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

        $game->actBonus($bonusType);
        
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

        $game->actBonus($bonusType,$bonusKey);
        
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

        $game->actBonus($bonusType);
        
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

        $game->actBonus($bonusType,$bonusKey);
        
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

        $game->actBonus($bonusType,$bonusKey);
        
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

        $game->actBonus($bonusType,$bonusKey);
        
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

        $game->actBonus($bonusType,$bonusKey);
        
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

        $game->actBonus($bonusType,$bonusKey);
        
        assertSame(json_encode($expectedBonuses), TestDatas::$players[1]['bonuses']);
        assertSame($bonusType, Globals::getCurrentBonus());
        assertSame(['koku'=>3,'bonusQuantity'=>1,], Globals::getCurrentBonusDatas());
        //Next state differs for each bonus :
        assertSame(ST_BONUS_MULTI_TRADES, GamestateMachine::$test_current_state);
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
        $game->actBonus($bonusType);
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
        $game->actBonus($bonusType);
    }
    // -------------------------------------------------
}