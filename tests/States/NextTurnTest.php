<?php

declare(strict_types=1);

namespace Tests\States;

use Bga\GameFramework\GamestateMachine;
use GameMock;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

final class NextTurnTest extends TestCase
{

    public function testEnteringState(): void
    {
        logForTests(__CLASS__.".".__FUNCTION__);
        $game = new GameMock();

        GamestateMachine::$test_current_state = ST_NEXT_TURN;
        $game->stNextTurn();
        
        //useless assert but we need 1
        assertSame(ST_BEFORE_TURN, GamestateMachine::$test_current_state);
    }
     
}