<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use ROG\Managers\Players;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class PlayerTurnTest extends TestCase
{

    // -------------------------------------------------
    public function test_Args_OnlySail(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_SpendFavor(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }
    
    public function test_Args_Trade(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }

    public function test_Args_Build(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }

    public function test_Args_Deliver(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
            'previousSteps' => [],
            'previousChoices' => 0,
        ];

        $args = $game->argPlayerTurn();
        
        assertSame($expectedArgs, $args);
    }

    // -------------------------------------------------
 
    public function test_ActionSpendFavor(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actSpendFavor();
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_DIVINE_FAVOR, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
 
    public function test_ActionTrade(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actTrade();
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_TRADE, GamestateMachine::$test_current_state);
    }
    // -------------------------------------------------
 
    public function test_ActionBuild(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actBuild();
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_BUILD, GamestateMachine::$test_current_state);
    }
    // -------------------------------------------------
 
    public function test_ActionSail(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actSail();
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_SAIL, GamestateMachine::$test_current_state);
    }
    // -------------------------------------------------
 
    public function test_ActionDeliver(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN;

        $game->actDeliver();
        
        //Test go to next state
        assertSame(ST_PLAYER_TURN_DELIVER, GamestateMachine::$test_current_state);
    }
    
    // -------------------------------------------------
 
    public function test_goToBonusStepIfNeeded_False(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
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
        logForTests(__CLASS__.".".__FUNCTION__, 'TEST_RUN');
        $game = new GameMock();
        GamestateMachine::$test_current_state = ST_PLAYER_TURN_BUILD;
        TestDatas::$players[1]['bonuses'] = json_encode([BONUS_TYPE_CHOICE]);
        $player = Players::get(1);

        $game->goToBonusStepIfNeeded($player,true);
        
        //Test go to bonus state
        assertSame(ST_BONUS_CHOICE, GamestateMachine::$test_current_state);
    }
}