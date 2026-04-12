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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        Globals::setStateBeforeTrade(ST_PLAYER_TURN);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $typeSrc = RESOURCE_TYPE_SILK;
        $typeDest = RESOURCE_TYPE_POTTERY;

        $game->actTradeSelect($typeSrc,$typeDest);
        
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionTradeSelect_Pass_Rice_Pottery(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        Globals::setStateBeforeTrade(ST_PLAYER_TURN);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":3,"2":0,"3":2,"4":0,"5":0,"6":0}';
        $typeSrc = RESOURCE_TYPE_RICE;
        $typeDest = RESOURCE_TYPE_POTTERY;

        $game->actTradeSelect($typeSrc,$typeDest);
        
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }
    
    public function test_ActionTradeSelect_Pass_Money_Favor(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        Globals::setStateBeforeTrade(ST_PLAYER_TURN);
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_TRADE;
        TestDatas::$players[TestDatas::$test_activePlayerId]['resources'] = '{"1":0,"2":0,"3":0,"4":5,"5":0,"6":5}';
        $typeSrc = RESOURCE_TYPE_MONEY;
        $typeDest = RESOURCE_TYPE_SUN;

        $game->actTradeSelect($typeSrc,$typeDest);
        
        assertSame(ST_PLAYER_TURN, GamestateMachine::$test_current_state);
    }

    public function test_ActionTradeSelect_Pass_PreviousStateBonus(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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