<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;
use ROG\Core\Globals;
use Tests\Utils\TestDatas;

use function PHPUnit\Framework\assertSame;

final class NextTurnTest extends TestCase
{

    public function testEnteringState_turn1(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setTurn(0);
        TestDatas::$players[1]['last_turn_played'] = false;
        TestDatas::$players[2]['last_turn_played'] = false;

        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        $game->stNextTurn();
        
        assertSame(ST_BEFORE_TURN, GamestateMachine::$test_current_state);
    }
     
    public function testEnteringState_turn2(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setTurn(1);
        TestDatas::$players[1]['last_turn_played'] = false;
        TestDatas::$players[2]['last_turn_played'] = false;

        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        $game->stNextTurn();
        
        assertSame(ST_BEFORE_TURN, GamestateMachine::$test_current_state);
    }
    
    public function testEnteringState_turnLast(): void
    {
        logTestRun(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();
        Globals::setTurn(123456789);//just for visibility, doesn't affect last turn
        TestDatas::$players[1]['last_turn_played'] = true;
        TestDatas::$players[2]['last_turn_played'] = true;

        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        $game->stNextTurn();
        
        assertSame(ST_END_SCORING, GamestateMachine::$test_current_state);
    }
     
}