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

final class TradeTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        TestDatas::$test_activePlayerId = 1;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        Globals::setChoices(0);
        $expectedArgs = [
            'p' => [
                ['src' => [RESOURCE_TYPE_SILK =>2 ], 'dest' => [RESOURCE_TYPE_POTTERY =>1 ]],
                ['src' => [RESOURCE_TYPE_SILK =>2 ], 'dest' => [RESOURCE_TYPE_RICE =>1 ]],

                ['src' => [RESOURCE_TYPE_RICE =>2 ], 'dest' => [RESOURCE_TYPE_SILK =>1 ]],
                ['src' => [RESOURCE_TYPE_RICE =>2 ], 'dest' => [RESOURCE_TYPE_POTTERY =>1 ]],
            ],
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argTrade();
        
        assertSame($expectedArgs, $args);
    }
    // -------------------------------------------------
 
    public function test_ActionTradeSelect_Pass_Silk_Pottery(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setStateBeforeTrade(ST_PLAYER_TURN);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $typeSrc = RESOURCE_TYPE_SILK;
        $typeDest = RESOURCE_TYPE_POTTERY;

        $game->actTradeSelect($typeSrc,$typeDest);
        
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(1, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(2, $resources[RESOURCE_TYPE_RICE]);
    }
    
    public function test_ActionTradeSelect_Pass_Rice_Pottery(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setStateBeforeTrade(ST_PLAYER_TURN);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $typeSrc = RESOURCE_TYPE_RICE;
        $typeDest = RESOURCE_TYPE_POTTERY;

        $game->actTradeSelect($typeSrc,$typeDest);
        
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(3, $resources[RESOURCE_TYPE_SILK]);
        assertSame(1, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(0, $resources[RESOURCE_TYPE_MOON]);
        assertSame(0, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
    }
    
    public function test_ActionTradeSelect_Pass_Money_Favor(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setStateBeforeTrade(ST_PLAYER_TURN);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":5,"5":4,"6":5}';
        $typeSrc = RESOURCE_TYPE_MONEY;
        $typeDest = RESOURCE_TYPE_SUN;

        $game->actTradeSelect($typeSrc,$typeDest);
        
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(5, $resources[RESOURCE_TYPE_MOON]);
        assertSame(5, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(19, TestDatas::$players[1]['player_score']);//19+0
    }
    
    public function test_ActionTradeSelect_Pass_Money_Favor_MasterySun(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setStateBeforeTrade(ST_PLAYER_TURN);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$tiles[1]['type'] = 14;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":5,"5":4,"6":5}';
        $typeSrc = RESOURCE_TYPE_MONEY;
        $typeDest = RESOURCE_TYPE_SUN;

        $game->actTradeSelect($typeSrc,$typeDest);
        
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
        $resources = json_decode(TestDatas::$players[TestDatas::$test_activePlayerId]['resources'], true);
        assertSame(0, $resources[RESOURCE_TYPE_SILK]);
        assertSame(0, $resources[RESOURCE_TYPE_POTTERY]);
        assertSame(0, $resources[RESOURCE_TYPE_RICE]);
        assertSame(5, $resources[RESOURCE_TYPE_MOON]);
        assertSame(5, $resources[RESOURCE_TYPE_SUN]);
        assertSame(0, $resources[RESOURCE_TYPE_MONEY]);
        assertSame(24, TestDatas::$players[1]['player_score']);//19+5
    }

    public function test_ActionTradeSelect_Pass_PreviousStateBonus(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setStateBeforeTrade(ST_BONUS_CHOICE);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $typeSrc = RESOURCE_TYPE_SILK;
        $typeDest = RESOURCE_TYPE_POTTERY;

        $game->actTradeSelect($typeSrc,$typeDest);
        
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }

    public function test_ActionTradeSelect_Pass_PreviousStateConfirm(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setStateBeforeTrade(ST_CONFIRM_TURN);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $typeSrc = RESOURCE_TYPE_SILK;
        $typeDest = RESOURCE_TYPE_POTTERY;

        $game->actTradeSelect($typeSrc,$typeDest);
        
        assertSame(ST_CONFIRM_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionTradeSelect_KO_Money_FavorMax(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":5,"6":5}';
        $typeSrc = RESOURCE_TYPE_MONEY;
        $typeDest = RESOURCE_TYPE_SUN;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot do that trade");
        $game->actTradeSelect($typeSrc,$typeDest);
    }
    public function test_ActionTradeSelect_KO_Money_Pottery(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $typeSrc = RESOURCE_TYPE_MONEY;
        $typeDest = RESOURCE_TYPE_POTTERY;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot do that trade");
        $game->actTradeSelect($typeSrc,$typeDest);
    }
    public function test_ActionTradeSelect_KO_Money_Silk(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $typeSrc = RESOURCE_TYPE_MONEY;
        $typeDest = RESOURCE_TYPE_SILK;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot do that trade");
        $game->actTradeSelect($typeSrc,$typeDest);
    }
    public function test_ActionTradeSelect_KO_Money_Rice(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $typeSrc = RESOURCE_TYPE_MONEY;
        $typeDest = RESOURCE_TYPE_RICE;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot do that trade");
        $game->actTradeSelect($typeSrc,$typeDest);
    }
    
    public function test_ActionTradeSelect_KO_Favor_Money(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":5,"6":0}';
        $typeSrc = RESOURCE_TYPE_SUN;
        $typeDest = RESOURCE_TYPE_MONEY;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot do that trade");
        $game->actTradeSelect($typeSrc,$typeDest);
    }
    public function test_ActionTradeSelect_KO_Moon_Favor(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":5,"6":0}';
        $typeSrc = RESOURCE_TYPE_MOON;
        $typeDest = RESOURCE_TYPE_MONEY;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot do that trade");
        $game->actTradeSelect($typeSrc,$typeDest);
    }
    
    public function test_ActionTradeSelect_KO_Silk_Silk(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $typeSrc = RESOURCE_TYPE_SILK;
        $typeDest = RESOURCE_TYPE_SILK;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot do that trade");
        $game->actTradeSelect($typeSrc,$typeDest);
    }
    public function test_ActionTradeSelect_KO_Money_Money(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":5}';
        $typeSrc = RESOURCE_TYPE_MONEY;
        $typeDest = RESOURCE_TYPE_MONEY;

        $this->expectException(UnexpectedException::class);
        $this->expectExceptionMessage("You cannot do that trade");
        $game->actTradeSelect($typeSrc,$typeDest);
    }
    
    // -------------------------------------------------
}